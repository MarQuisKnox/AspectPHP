<?php

/**
 * AspectPHP_Reflection_Aspect
 *
 * @category PHP
 * @package AspectPHP_Reflection
 * @author Matthias Molitor <matthias@matthimatiker.de>
 * @copyright 2012 Matthias Molitor
 * @license http://www.opensource.org/licenses/BSD-3-Clause BSD License
 * @link https://github.com/Matthimatiker/AspectPHP
 * @since 09.04.2012
 */

/**
 * Reflection class that is used to gather information about an aspect.
 *
 * @category PHP
 * @package AspectPHP_Reflection
 * @author Matthias Molitor <matthias@matthimatiker.de>
 * @copyright 2012 Matthias Molitor
 * @license http://www.opensource.org/licenses/BSD-3-Clause BSD License
 * @link https://github.com/Matthimatiker/AspectPHP
 * @since 09.04.2012
 */
class AspectPHP_Reflection_Aspect extends ReflectionClass
{
    
    /**
     * Creates a reflection object that is used to inspect
     * the provided aspect.
     *
     * The constructor accepts an aspect object or the name
     * of an aspect class.
     *
     * @param AspectPHP_Aspect|string $classOrAspect
     * @throws AspectPHP_Reflection_Exception If an invalid argument is provided.
     */
    public function __construct($classOrAspect)
    {
        parent::__construct($classOrAspect);
        if (!$this->implementsInterface('AspectPHP_Aspect')) {
            $message = 'Provided class/object is not an aspect.';
            throw new AspectPHP_Reflection_Exception($message);
        }
    }
    
    /**
     * Returns all pointcut methods.
     *
     * A method is considered as pointcut if it starts with the
     * prefix "pointcut" or if it is referenced by an advice.
     *
     * @return array(ReflectionMethod)
     */
    public function getPointcuts()
    {
        
    }
    
    /**
     * Returns the pointcut method with the provided name.
     *
     * @param string $name
     * @return ReflectionMethod
     * @throws AspectPHP_Reflection_Exception If the requested method is not considered as pointcut.
     */
    public function getPointcut($name)
    {
        
    }
    
    /**
     * Checks if the aspect contains a pointcut with the provided name.
     *
     * @param string $name
     * @return boolean True if the pointcut exists, false otherwise.
     */
    public function hasPointcut($name)
    {
        
    }
    
    /**
     * Returns all advice methods.
     *
     * A method is considered as advice method if it references
     * a pointcut via annotations.
     *
     * @return array(ReflectionMethod)
     */
    public function getAdvices()
    {
        
    }
    
    /**
     * Returns the advice method with the provided name.
     *
     * @param string $name
     * @return ReflectionMethod
     * @throws AspectPHP_Reflection_Exception If the requested method is not considered as advice.
     */
    public function getAdvice($name)
    {
    
    }
    
    /**
     * Checks if the aspect contains an advice with the provided name.
     *
     * @param string $name
     * @return boolean True if the advice exists, false otherwise.
     */
    public function hasAdvice($name)
    {
    
    }
    
}