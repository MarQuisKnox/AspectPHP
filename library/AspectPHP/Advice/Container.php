<?php

/**
 * AspectPHP_Advice_Container
 *
 * @category PHP
 * @package AspectPHP_Advice
 * @author Matthias Molitor <matthias@matthimatiker.de>
 * @copyright 2012 Matthias Molitor
 * @license http://www.opensource.org/licenses/BSD-3-Clause BSD License
 * @link https://github.com/Matthimatiker/AspectPHP
 * @since 29.03.2012
 */

/**
 * Container class that holds an arbitrary number of advices grouped by type.
 *
 * The container provides a composite object for each advice type. An arbitrary
 * number of advices may be added for each type:
 * <code>
 * $container = new AspectPHP_Advice_Container();
 * $container->before()->add($myAdvice);
 * </code>
 *
 * @category PHP
 * @package AspectPHP_Advice
 * @author Matthias Molitor <matthias@matthimatiker.de>
 * @copyright 2012 Matthias Molitor
 * @license http://www.opensource.org/licenses/BSD-3-Clause BSD License
 * @link https://github.com/Matthimatiker/AspectPHP
 * @since 29.03.2012
 */
class AspectPHP_Advice_Container
{
    
    /**
     * Returns a composite that holds the before advices.
     *
     * @return AspectPHP_Advice_Composite
     */
    public function before()
    {
        
    }
    
    /**
     * Returns a composite that holds the afterReturning advices.
     *
     * @return AspectPHP_Advice_Composite
     */
    public function afterReturning()
    {
        
    }
    
    /**
     * Returns a composite that holds the afterThrowing advices.
     *
     * @return AspectPHP_Advice_Composite
     */
    public function afterThrowing()
    {
        
    }
    
    /**
     * Returns a composite that holds the after advices.
     *
     * @return AspectPHP_Advice_Composite
     */
    public function after()
    {
        
    }
    
}