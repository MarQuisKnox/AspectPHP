<?php

/**
 * AspectPHP_JoinPointTest
 *
 * @author Matthias Molitor <matthias@matthimatiker.de>
 * @package AspectPHP
 * @subpackage Tests
 * @copyright Matthias Molitor 2012
 * @version $Rev$
 * @since 08.01.2012
 */

/**
 * Initializes the test environment.
 */
require_once(dirname(__FILE__) . '/bootstrap.php');

/**
 * Tests the JoinPoint implementation.
 *
 * @author Matthias Molitor <matthias@matthimatiker.de>
 * @package AspectPHP
 * @subpackage Tests
 * @copyright Matthias Molitor 2012
 * @version $Rev$
 * @since 08.01.2012
 */
class AspectPHP_JoinPointTest extends PHPUnit_Framework_TestCase {
    
    // TODO
    // return value is null if not set
    // return value correct
    // set return value fluent interface
    // exception null if not set
    // exception correct
    // set exception accepts null
    // set exception fluent interface
    // get arguments array
    // get arguments fluent interface
    // get arguments correct
    // get arguments correct if default value
    // get argument by index correct
    // get argument by index correct if default value
    // get argument by name correct
    // get argument by name correct if default value
    // get class correct
    // get method correct
    // context correct if object
    // context correct if string
    // default target correct
    // set target fluent interface
    // set target throws exception if invalid callback
    // provided target correct
    
    /**
     * This method and its parameters are used to create a join point for testing.
     *
     * @param string $name
     * @param boolean $register
     * @return AspectPHP_JoinPoint
     */
    protected function createJoinPoint($name, $register = true) {
        $arguments = func_get_args();
        $joinPoint = new AspectPHP_JoinPoint(__METHOD__, $this);
        $joinPoint->setArguments($arguments);
        return $joinPoint;
    }
    
}

?>