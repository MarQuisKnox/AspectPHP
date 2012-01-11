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
    
    /**
     * System under test.
     *
     * @var AspectPHP_JoinPoint
     */
    protected $joinPoint = null;
    
    /**
     * See {@link PHPUnit_Framework_TestCase::setUp()} for details.
     */
    protected function setUp() {
        parent::setUp();
        $this->joinPoint = $this->createJoinPoint('Bert', false);
    }
    
    /**
     * See {@link PHPUnit_Framework_TestCase::tearDown()} for details.
     */
    protected function tearDown() {
        $this->joinPoint = null;
        parent::tearDown();
    }
    
    /**
     * Checks if getClass() returns the name of the class that contains the method.
     */
    public function testGetClassReturnsCorrectValue() {
        $this->assertEquals(__CLASS__, $this->joinPoint->getClass());
    }
    
    /**
     * Checks if getMethod() returns the name of the method.
     */
    public function testGetMethodReturnsCorrectValue() {
        $this->assertEquals('createJoinPoint', $this->joinPoint->getMethod());
    }
    
    /**
     * Ensures that getReturnValue() returns null if no value was provided.
     */
    public function testGetReturnValueReturnsNullIfNoValueWasProvided() {
        $this->assertNull($this->joinPoint->getReturnValue());
    }
    
    /**
     * Checks if getReturnsValue() returns the correct value.
     */
    public function testGetReturnValueReturnsCorrectValue() {
        $this->joinPoint->setReturnValue('Test');
        $this->assertEquals('Test', $this->joinPoint->getReturnValue());
    }
    
    /**
     * Checks if setReturnsValue() provides a fluent interface.
     */
    public function testSetReturnValueProvidesFluentInterface() {
        $this->assertSame($this->joinPoint, $this->joinPoint->setReturnValue('Demo'));
    }
    
    /**
     * Ensures that getException() returns null if no exception was provided.
     */
    public function testGetExceptionReturnsNullIfNoExceptionWasProvided() {
        $this->assertNull($this->joinPoint->getException());
    }
    
    /**
     * Checks if getException() returns the correct exception object.
     */
    public function testGetExceptionReturnsCorrectObject() {
        $exception = new RuntimeException('Exception test.');
        $this->joinPoint->setException($exception);
        $this->assertSame($exception, $this->joinPoint->getException());
    }
    
    /**
     * Ensures that setException() accepts null.
     */
    public function testSetExceptionAcceptsNull() {
        $this->joinPoint->setException(new RuntimeException('Test.'));
        $this->joinPoint->setException(null);
        $this->assertNull($this->joinPoint->getException());
    }
    
    /**
     * Ensures that setException() throws an exception if an invalid argument
     * is passed.
     */
    public function testSetExceptionThrowsExceptionIfInvalidArgumentIsProvided() {
        $this->setExpectedException('InvalidArgumentException');
        $this->joinPoint->setException(new stdClass());
    }
    
    /**
     * Checks if setException() provides a fluent interface.
     */
    public function testSetExceptionProvidesFluentInterface() {
        $this->assertSame($this->joinPoint, $this->joinPoint->setException(new RuntimeException('Test')));
    }
    
    /**
     * Checks if getArguments() returns an array.
     */
    public function testGetArgumentsReturnsArray() {
        $arguments = $this->joinPoint->getArguments();
        $this->assertInternalType('array', $arguments);
    }
    
    /**
     * Ensures that getArguments() returns correct values.
     */
    public function testGetArgumentsReturnsCorrectValues() {
        $arguments = $this->joinPoint->getArguments();
        $this->assertEquals(array('Bert', false), $arguments);
    }
    
    /**
     * Ensures that getArguments() returns the correct values if a default
     * parameter was used when the method was called.
     */
    public function testGetArgumentsReturnsCorrectValuesIfDefaultParameterIsUsed() {
        $joinPoint = $this->createJoinPoint('Ernie');
        $arguments = $joinPoint->getArguments();
        $this->assertEquals(array('Ernie', true), $arguments);
    }
    
    /**
     * Checks if getArgument() returns the correct value for a given
     * parameter index.
     */
    public function testGetArgumentReturnsCorrectValueByIndex() {
        $this->assertEquals('Bert', $this->joinPoint->getArgument(0));
    }
    
    /**
     * Ensures that getArgument() returns the correct value for a given parameter
     * index if a default parameter was used.
     */
    public function testGetArgumentReturnsCorrectValueByIndexIfDefaultParameterIsUsed() {
         $joinPoint = $this->createJoinPoint('Ernie');
         $this->assertEquals(true, $joinPoint->getArgument(1));
    }
    
    /**
     * Checks if getArgument() returns the correct value for a given
     * parameter name.
     */
    public function testGetArgumentReturnsCorrectValueByName() {
        $this->assertEquals('Bert', $this->joinPoint->getArgument('name'));
    }
    
    /**
     * Ensures that getArgument() returns the correct value for a given parameter
     * name if a default parameter was used.
     */
    public function testGetArgumentReturnsCorrectValueByNameIfDefaultParameterIsUsed() {
        $joinPoint = $this->createJoinPoint('Ernie');
        $this->assertEquals(true, $joinPoint->getArgument('register'));
    }
    
	/**
     * Ensures that getArgument() throws an exception if an invalid parameter index is
     * provided.
     */
    public function testGetArgumentThrowsExceptionIfParameterWithProvidedIndexDoesNotExist() {
        $this->setExpectedException('InvalidArgumentException');
        $this->joinPoint->getArgument(2);
    }
    
    /**
     * Ensures that getArgument() throws an exception if an invalid parameter name is
     * provided.
     */
    public function testGetArgumentThrowsExceptionIfParameterWithProvidedNameDoesNotExist() {
        $this->setExpectedException('InvalidArgumentException');
        $this->joinPoint->getArgument('missing');
    }
     
    /**
     * Checks if setArguments() provides a fluent interface.
     */
    public function testSetArgumentsProvidesFluentInterface() {
        
    }
    
    /**
     * Checks if getContext() returns the correct object.
     */
    public function testGetContextReturnsCorrectObject() {
        
    }
    
    /**
     * Ensures that getContext() returns the correct value if the class name was provided.
     */
    public function testGetContextReturnsCorrectValueIfClassNameWasProvided() {
        
    }
    
    /**
     * Ensures that getTarget() returns a callable per default.
     */
    public function testGetTargetReturnsCallablePerDefault() {
        
    }
    
    /**
     * Checks if getTarget() returns the provided callable.
     */
    public function testGetTargetReturnsCorrectCallable() {
        
    }
    
    /**
     * Checks if setTarget() provides a fluent interface.
     */
    public function testSetTargetProvidesFluentInterface() {
        
    }
    
    /**
     * Ensures that setTarget() throws an exception if no callable was provided.
     */
    public function testSetTargetThrowsExceptionIfNoCallableIsProvided() {
        
    }
    
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