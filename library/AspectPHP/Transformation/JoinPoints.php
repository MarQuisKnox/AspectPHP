<?php

/**
 * AspectPHP_Transformation_JoinPoints
 *
 * @category PHP
 * @package AspectPHP_Transformation
 * @author Matthias Molitor <matthias@matthimatiker.de>
 * @copyright 2012 Matthias Molitor
 * @license http://www.opensource.org/licenses/BSD-3-Clause BSD License
 * @link https://github.com/Matthimatiker/AspectPHP
 * @since 05.01.2012
 */

/**
 * Transformation class that adds injection points to the given source code.
 *
 * @category PHP
 * @package AspectPHP_Transformation
 * @author Matthias Molitor <matthias@matthimatiker.de>
 * @copyright 2012 Matthias Molitor
 * @license http://www.opensource.org/licenses/BSD-3-Clause BSD License
 * @link https://github.com/Matthimatiker/AspectPHP
 * @since 05.01.2012
 */
class AspectPHP_Transformation_JoinPoints implements AspectPHP_Transformation
{
    
    /**
     * Helper object to load source code.
     *
     * @var AspectPHP_Code_Extractor
     */
    private static $codeExtractor = null;
    
    /**
     * The analyzer that is currently used to inspect the tokens.
     *
     * @var AspectPHP_Code_TokenEditor
     */
    protected $editor = null;
    
    /**
     * See {@link AspectPHP_Transformation::trasnform()} for details.
     *
     * @param string $source
     * @return string
     */
    public function transform($source)
    {
        $this->editor = new AspectPHP_Code_TokenEditor($source);
        
        $classToken = $this->editor->findNext(T_CLASS, 0);
        if ($classToken === -1) {
            // No class found.
            return $source;
        }
        $body     = $this->findBody($classToken);
        $classEnd = $this->editor->findMatchingBrace($body);
        
        $index = $classToken;
        while (($index = $this->editor->findNext(T_FUNCTION, $index)) !== -1) {
            // We found a "function" keyword at position $index.
            $bodyStart = $this->findBody($index);
            if ($bodyStart === -1) {
                // No body, might be an abstract method.
                continue;
            }
            $visibility     = $this->findMethodVisibility($index);
            $name           = $this->findMethodName($index);
            $originalName   = $this->editor[$name][1];
            $newName        = '_aspectPHP' . $originalName;
            $context        = ($this->isStatic($index)) ? '__CLASS__' : '$this';
            $signatureStart = $this->findSignatureStart($index);
            $signature      = $this->between($signatureStart, $bodyStart - 1);
            
            $injectionPoint = $this->buildInjectionPoint($signature, $newName, $context);
            $this->editor->insertBefore($classEnd, array($injectionPoint));
            
            // Rename the original method...
            $this->editor->rename($name, $newName);
            // ... and reduce its visibility.
            if ($visibility === -1) {
                // Visibility was not defined explicity.
                $visibilityToken = array(
                    T_PRIVATE,
                    'private',
                    0
                );
                $this->editor->insertBefore($index, array($visibility));
            } else {
                $visibilityToken    = $this->editor[$visibility];
                $visibilityToken[0] = T_PRIVATE;
                $visibilityToken[1] = 'private';
                $this->editor->replace($visibility, $visibilityToken);
            }
            
            // Replace __METHOD__ constants.
            $methodConstants = $this->findAll(T_METHOD_C, $index);
            $newContent = array(
                T_STRING,
                "__CLASS__ . '::{$originalName}'"
            );
            $this->editor->replace($methodConstants, $newContent);

            // Replace __FUNCTION__ constants.
            $functionConstants = $this->findAll(T_FUNC_C, $index);
            $newContent = array(
                T_STRING,
                "'{$originalName}'"
            );
            $this->editor->replace($functionConstants, $newContent);
        }
        
        $this->editor->insertBefore($classEnd, array($this->getCodeTemplate('_aspectPHPInternalHandleCall')));
        
        $this->editor->commit();
        
        return (string)$this->editor;
    }
    
    /**
     * Searches for the token that begins the signature of the given function.
     *
     * @param integer $functionIndex The function index.
     * @return integer
     */
    protected function findSignatureStart($functionIndex)
    {
        $signatureTypes = array(
            T_DOC_COMMENT,
            T_PUBLIC,
            T_PROTECTED,
            T_PRIVATE,
            T_STATIC,
            T_FINAL,
            T_FUNCTION
        );
        
        $stopTokens = array(';', '{', '}');
        $tokens     = $this->editor->findAllBetween($signatureTypes, $functionIndex, 0, $stopTokens);
        return min($tokens);
    }
    
    /**
     * Finds all tokens of the given type in the body of the provided function.
     *
     * @param integer|string $type
     * @param integer $functionIndex
     * @return array(integer) The indexes of the matches.
     */
    protected function findAll($type, $functionIndex)
    {
        $matches = array();
        $start   = $this->findBody($functionIndex);
        $end     = $this->editor->findMatchingBrace($start);
        return $this->editor->findAllBetween($type, $start, $end);
    }
    
    /**
     * Checks if the function at position $functionIndex is static.
     *
     * @param integer $functionIndex
     * @return boolean True if the function is static, false otherwise.
     */
    protected function isStatic($functionIndex)
    {
        return $this->editor->findPrevious(T_STATIC, $functionIndex, array(T_DOC_COMMENT, ';', '{', '}')) !== -1;
    }
    
    /**
     * Builds an injection point.
     *
     * @param string $signature The method signature, including the doc comment.
     * @param string $callee Name of the method that will be called.
     * @param string $context The method context. For example $this or __CLASS__.
     * @return string The code of the generated injection point method.
     */
    protected function buildInjectionPoint($signature, $callee, $context)
    {
        $template = '    %1$s'                                                                                . PHP_EOL
                  . '    {'                                                                                   . PHP_EOL
                  . '        $args = func_get_args();'                                                        . PHP_EOL
                  . '        return self::_aspectPHPInternalHandleCall(__FUNCTION__, \'%3$s\', %2$s, $args);' . PHP_EOL
                  . '    }'                                                                                   . PHP_EOL;
        return sprintf($template, $signature, $context, $callee);
    }
    
    /**
     * Returns the source code of the given method including its doc block
     * from the template class AspectPHP_Transformation_Template_JoinPointHandler.
     *
     * Example:
     * <code>
     * $code = $this->getCodeTemplate('methodName');
     * </code>
     *
     * @param string $name The method name.
     * @return string
     */
    protected function getCodeTemplate($name)
    {
        return $this->getCodeExtractor()->getSource('AspectPHP_Transformation_Template_JoinPointHandler::' . $name);
    }
    
    /**
     * Returns the extractor that is used to load the source code of methods.
     *
     * @return AspectPHP_Code_Extractor
     */
    protected function getCodeExtractor()
    {
        if (self::$codeExtractor === null) {
            self::$codeExtractor = new AspectPHP_Code_Extractor();
        }
        return self::$codeExtractor;
    }
    
    /**
     * Merges the tokens between $start and $end (inclusive) and
     * return the code as string.
     *
     * @param integer $start
     * @param integer $end
     * @return string
     */
    protected function between($start, $end)
    {
        $code = '';
        for ($i = $start; $i <= $end; $i++) {
            if (is_string($this->editor[$i])) {
                $code .= $this->editor[$i];
            } else {
                $code .= $this->editor[$i][1];
            }
        }
        return $code;
    }
    
    /**
     * Returns the index of the token that contains the name of the
     * method that is declared by the given function token.
     *
     * @param integer $functionIndex Index of a T_FUNCTION token.
     * @return integer
     */
    protected function findMethodName($functionIndex)
    {
        return $this->editor->findNext(T_STRING, $functionIndex);
    }
    
    /**
     * Returns the index of the token that contains the visibility of the
     * method that is declared by the given function token.
     *
     * @param integer $functionIndex Index of a T_FUNCTION token.
     * @return integer
     */
    protected function findMethodVisibility($functionIndex)
    {
        $visibilities = array(
            T_PUBLIC,
            T_PROTECTED,
            T_PRIVATE
        );
        return $this->editor->findPrevious($visibilities, $functionIndex, array('{', '}', ';'));
    }
    
    /**
     * Returns the index of the doc block that belongs to the method that is
     * declared by the given function token.
     *
     * @param integer $functionIndex Index of a T_FUNCTION token.
     * @return integer
     */
    protected function findDocBlock($functionIndex)
    {
        return $this->editor->findPrevious(T_DOC_COMMENT, $functionIndex, array('{', '}', ';'));
    }
    
    /**
     * Returns the index of the next "{" token that starts a body.
     *
     * @param integer $index
     * @return integer
     */
    protected function findBody($index)
    {
        return $this->editor->findNext('{', $index, array(';'));
    }
    
}