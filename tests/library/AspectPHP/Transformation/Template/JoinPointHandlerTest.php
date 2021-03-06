<?php

/**
 * AspectPHP_Transformation_Template_JoinPointHandlerTest
 *
 * @category PHP
 * @package AspectPHP_Transformation
 * @subpackage Tests
 * @author Matthias Molitor <matthias@matthimatiker.de>
 * @copyright 2012 Matthias Molitor
 * @license http://www.opensource.org/licenses/BSD-3-Clause BSD License
 * @link https://github.com/Matthimatiker/AspectPHP
 * @since 04.04.2012
 */

/**
 * Initializes the test environment.
 */
require_once(dirname(__FILE__) . '/bootstrap.php');

/**
 * Tests the JoinPointHandler template.
 *
 * @category PHP
 * @package AspectPHP_Transformation
 * @subpackage Tests
 * @author Matthias Molitor <matthias@matthimatiker.de>
 * @copyright 2012 Matthias Molitor
 * @license http://www.opensource.org/licenses/BSD-3-Clause BSD License
 * @link https://github.com/Matthimatiker/AspectPHP
 * @since 04.04.2012
 */
class AspectPHP_Transformation_Template_JoinPointHandlerTest extends PHPUnit_Framework_TestCase
{
    
    /**
     * The name of the simulated method that is provided by callback mocks.
     *
     * @var string
     */
    const CALLBACK_METHOD = 'invoke';
    
    /**
     * The manager that was stored before the test starts.
     *
     * @var AspectPHP_Manager|null
     */
    protected $previousManager = null;
    
    /**
     * Contains simulated advisors.
     *
     * @var AspectPHP_Advisor_Container
     */
    protected $advisors = null;
    
    /**
     * See {@link PHPUnit_Framework_TestCase::setUp()} for details.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->storeManager();
        $this->simulateManager(null);
        $this->advisors = $this->createContainer();
    }
    
    /**
     * See {@link PHPUnit_Framework_TestCase::tearDown()} for details.
     */
    protected function tearDown()
    {
        $this->advisors = null;
        $this->restoreManager();
        parent::tearDown();
    }
    
    /**
     * Ensures that the handler calls the compiled method if no aspect manager is available.
     */
    public function testHandlerExecutesCompiledMethodIfManagerIsNotAvailable()
    {
        $this->simulateManager(null);
        
        $mock = $this->createCallbackMock();
        $mock->expects($this->once())
             ->method(self::CALLBACK_METHOD);
        $this->handle($mock);
    }
    
    /**
     * Checks if the handler uses the aspect manager to request advices.
     */
    public function testHandlerRequestsAdvicesFromManager()
    {
        $manager = $this->createManagerMock();
        $manager->expects($this->once())
                ->method('getAdvicesFor');
        $this->simulateManager($manager);
        
        $this->handle($this->createCallbackMock());
    }
    
    /**
     * Checks if the handler requests advices for the correct method.
     */
    public function testHandlerRequestsAdvicesForCorrectMethod()
    {
        $manager = $this->createManagerMock();
        $manager->expects($this->any())
                ->method('getAdvicesFor')
                ->with('AspectPHP_Transformation_Template_JoinPointHandler::original');
        $this->simulateManager($manager);
        
        $this->handle($this->createCallbackMock());
    }
    
    /**
     * Ensures that the handler executes the compiled method if the aspect manager
     * does not provide any advice.
     */
    public function testHandlerExecutesCompiledMethodIfNoAdvicesAreAvailable()
    {
        $this->simulateManager($this->createManagerMock());
        
        $mock = $this->createCallbackMock();
        $mock->expects($this->once())
             ->method(self::CALLBACK_METHOD);
        $this->handle($mock);
    }
    
    /**
     * Ensures that the handler executes the compiled method even if advices
     * are available.
     */
    public function testHandlerExecutesCompiledMethodIfAdvicesAreAvailable()
    {
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $this->advisors->before()->add($this->toAdvisor($adviceCallback));
        
        $mock = $this->createCallbackMock();
        $mock->expects($this->once())
             ->method(self::CALLBACK_METHOD);
        $this->handle($mock);
        
    }
    
    /**
     * Checks if the handler executes before advices.
     */
    public function testHandlerExecutesBeforeAdvices()
    {
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $adviceCallback->expects($this->once())
                       ->method(self::CALLBACK_METHOD);
        $this->advisors->before()->add($this->toAdvisor($adviceCallback));
        
        $this->handle($this->createCallbackMock());
    }
    
    /**
     * Ensures that the handler does not execute the compiled method if a before advice
     * provides a return value.
     */
    public function testHandlerDoesNotExecuteCompiledMethodIfBeforeAdviceProvidesReturnValue()
    {
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $adviceCallback->expects($this->any())
                       ->method(self::CALLBACK_METHOD)
                       ->will($this->returnCallback(array($this, 'joinPointReturnValue')));
        $this->advisors->before()->add($this->toAdvisor($adviceCallback));
        
        $mock = $this->createCallbackMock();
        $mock->expects($this->never())
             ->method(self::CALLBACK_METHOD);
        $this->handle($mock);
    }
    
    /**
     * Ensures that the handler returns the result that is provided by a before advice.
     */
    public function testHandlerReturnsReturnValueThatIsProvidedByBeforeAdvice()
    {
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $adviceCallback->expects($this->any())
                       ->method(self::CALLBACK_METHOD)
                       ->will($this->returnCallback(array($this, 'joinPointReturnValue')));
        $this->advisors->before()->add($this->toAdvisor($adviceCallback));
        
        $result = $this->handle($this->createCallbackMock());
        $this->assertEquals(42, $result);
    }
    
    /**
     * Checks if the handler passes the provided arguments to the compiled method
     * if there are no registered advices for the method.
     */
    public function testHandlerPassesProvidedArgumentsToCompiledMethodIfNoAdvicesAreAvailable()
    {
        $this->simulateManager($this->createManagerMock());
        
        $mock = $this->createCallbackMock();
        $mock->expects($this->any())
             ->method(self::CALLBACK_METHOD)
             ->with(3, 5, 7);
        
        $this->handle($mock, array(3, 5, 7));
    }
    
    /**
     * Checks if the handler passes the provided arguments to the compiled method
     * if advices are registered for the method.
     */
    public function testHandlerPassesProvidedArgumentsToCompiledMethodIfAdvicesAreAvailable()
    {
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $this->advisors->before()->add($this->toAdvisor($adviceCallback));
        
        $mock = $this->createCallbackMock();
        $mock->expects($this->any())
             ->method(self::CALLBACK_METHOD)
             ->with(3, 5, 7);
        
        $this->handle($mock, array(3, 5, 7));
    }
    
    /**
     * Ensures that the handler passes modified arguments to the compiled method
     * if a before advice changed them.
     */
    public function testHandlerPassesArgumentsThatWereModifiedByBeforeAdviceToCompiledMethod()
    {
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $adviceCallback->expects($this->any())
                       ->method(self::CALLBACK_METHOD)
                       ->will($this->returnCallback(array($this, 'joinPointModifyArgs')));
        $this->advisors->before()->add($this->toAdvisor($adviceCallback));
        
        $mock = $this->createCallbackMock();
        $mock->expects($this->any())
             ->method(self::CALLBACK_METHOD)
             ->with(1, 2, 3);
        
        $this->handle($mock, array(3, 5, 7));
    }
    
    /**
     * Checks if the handler returns the result from the compiled method if no advices
     * are registered for the method.
     */
    public function testHandlerReturnsReturnValueFromCompiledMethodIfNoAdvicesAreAvailable()
    {
        $this->simulateManager($this->createManagerMock());
        
        $mock = $this->createCallbackMock(99);
        
        $result = $this->handle($mock);
        $this->assertEquals(99, $result);
    }
    
    /**
     * Checks if the handler returns the result from the compiled method if advices
     * are registered for the method.
     */
    public function testHandlerReturnsReturnValueFromCompiledMethodIfAdvicesAreAvailable()
    {
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $this->advisors->before()->add($this->toAdvisor($adviceCallback));
        
        $mock = $this->createCallbackMock(99);
        
        $result = $this->handle($mock);
        $this->assertEquals(99, $result);
    }
    
    /**
     * Ensures that the handler returns the result of the compiled method
     * if no aspect manager is available.
     */
    public function testHandlerReturnsReturnValueOfCompiledMethodIfManagerIsNotAvailable()
    {
        $this->simulateManager(null);
        
        $mock = $this->createCallbackMock(99);
        
        $result = $this->handle($mock);
        $this->assertEquals(99, $result);
    }
    
    /**
     * Checks if the handler executes afterReturning advices.
     */
    public function testHandlerExecutesAfterReturningAdvices()
    {
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $adviceCallback->expects($this->once())
                       ->method(self::CALLBACK_METHOD);
        $this->advisors->afterReturning()->add($this->toAdvisor($adviceCallback));
        
        $this->handle($this->createCallbackMock());
    }
    
    /**
     * Ensures that the handler returns the result that is provided by an afterReturning advice.
     */
    public function testHandlerReturnsReturnValueModifiedByAfterReturningAdvice()
    {
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $adviceCallback->expects($this->once())
                       ->method(self::CALLBACK_METHOD)
                       ->will($this->returnCallback(array($this, 'joinPointReturnValue')));
        $this->advisors->afterReturning()->add($this->toAdvisor($adviceCallback));
        
        $result = $this->handle($this->createCallbackMock());
        $this->assertEquals(42, $result);
    }
    
    /**
     * Ensures that the afterThrowing advice is not called if no exception occurred.
     */
    public function testHandlerDoesNotExecuteAfterThrowingAdviceIfNoExceptionOccurred()
    {
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $adviceCallback->expects($this->never())
                       ->method(self::CALLBACK_METHOD);
        $this->advisors->afterThrowing()->add($this->toAdvisor($adviceCallback));
        
        $this->handle($this->createCallbackMock());
    }
    
    /**
     * Ensures that the afterThrowing advice is called if an exception was thrown
     * by the compiled method.
     */
    public function testHandlerExecutesAfterThrowingAdviceIfExceptionOccurred()
    {
        $this->setExpectedException('RuntimeException');
        
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $adviceCallback->expects($this->once())
                       ->method(self::CALLBACK_METHOD);
        $this->advisors->afterThrowing()->add($this->toAdvisor($adviceCallback));
        
        $mock = $this->createCallbackMock();
        $mock->expects($this->any())
             ->method(self::CALLBACK_METHOD)
             ->will($this->throwException(new RuntimeException('Test exception.')));
        
        $this->handle($mock);
    }
    
    /**
     * Checks if the afterThrowing advice has access to the exception that
     * was thrown by the compiled method.
     */
    public function testAfterThrowingAdviceCanAccessOriginalException()
    {
        $this->setExpectedException('RuntimeException');
        
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $adviceCallback->expects($this->any())
                       ->method(self::CALLBACK_METHOD)
                       ->will($this->returnCallback(array($this, 'joinPointCheckException')));
        $this->advisors->afterThrowing()->add($this->toAdvisor($adviceCallback));
        
        $mock = $this->createCallbackMock();
        $mock->expects($this->any())
             ->method(self::CALLBACK_METHOD)
             ->will($this->throwException(new RuntimeException('Test exception.')));
        
        $this->handle($mock);
    }
    
    /**
     * Ensures that the handler suppresses an exception if the afterThrowing advice
     * provides a return value.
     */
    public function testHandlerSuppressesExceptionIfAfterThrowingAdviceProvidesReturnValue()
    {
        $this->setExpectedException(null);
        
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $adviceCallback->expects($this->any())
                       ->method(self::CALLBACK_METHOD)
                       ->will($this->returnCallback(array($this, 'joinPointReturnValue')));
        $this->advisors->afterThrowing()->add($this->toAdvisor($adviceCallback));
        
        $mock = $this->createCallbackMock();
        $mock->expects($this->any())
             ->method(self::CALLBACK_METHOD)
             ->will($this->throwException(new RuntimeException('Test exception.')));
        
        $this->handle($mock);
    }
    
    /**
     * Checks if the handler returns the result that is provided by an afterThrowing advice.
     */
    public function testHandlerReturnsReturnValueProvidedByAfterThrowingAdvice()
    {
        $this->setExpectedException(null);
        
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $adviceCallback->expects($this->any())
                       ->method(self::CALLBACK_METHOD)
                       ->will($this->returnCallback(array($this, 'joinPointReturnValue')));
        $this->advisors->afterThrowing()->add($this->toAdvisor($adviceCallback));
        
        $mock = $this->createCallbackMock();
        $mock->expects($this->any())
             ->method(self::CALLBACK_METHOD)
             ->will($this->throwException(new RuntimeException('Test exception.')));
        
        $result = $this->handle($mock);
        $this->assertEquals(42, $result);
    }
    
    /**
     * Ensures that the original exception is thrown if the afterThrowing advice provides
     * neither result nor exception.
     */
    public function testHandlerThrowsOriginalExceptionIfAfterThrowingAdviceDoesNotInterfere()
    {
        $this->setExpectedException('RuntimeException');
        
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $this->advisors->afterThrowing()->add($this->toAdvisor($adviceCallback));
        
        $mock = $this->createCallbackMock();
        $mock->expects($this->any())
             ->method(self::CALLBACK_METHOD)
             ->will($this->throwException(new RuntimeException('Test exception.')));
        
        $this->handle($mock);
    }
    
    /**
     * Checks if the handler throws the exception that was provided by the afterThrowing
     * advice via the setException() method of the JoinPoint.
     */
    public function testHandlerThrowsExceptionThatWasSetByAfterThrowingAdvice()
    {
        $this->setExpectedException('BadMethodCallException');
        
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $adviceCallback->expects($this->any())
                       ->method(self::CALLBACK_METHOD)
                       ->will($this->returnCallback(array($this, 'joinPointSetException')));
        $this->advisors->afterThrowing()->add($this->toAdvisor($adviceCallback));
        
        $mock = $this->createCallbackMock();
        $mock->expects($this->any())
             ->method(self::CALLBACK_METHOD)
             ->will($this->throwException(new RuntimeException('Test exception.')));
        
        $this->handle($mock);
    }
    
    /**
     * Ensures that the afterThrowing advice is executed even if the exception was thrown
     * by a before advice.
     */
    public function testHandlerExecutesAfterThrowingAdviceIfBeforeAdviceThrowsException()
    {
        $this->setExpectedException('RuntimeException');
        
        $this->simulateManager($this->createManagerMock());
        
        $beforeCallback = $this->createCallbackMock();
        $beforeCallback->expects($this->any())
                       ->method(self::CALLBACK_METHOD)
                       ->will($this->throwException(new RuntimeException('Test exception.')));
        $this->advisors->before()->add($this->toAdvisor($beforeCallback));
        
        $afterThrowingCallback = $this->createCallbackMock();
        $afterThrowingCallback->expects($this->once())
                              ->method(self::CALLBACK_METHOD);
        $this->advisors->afterThrowing()->add($this->toAdvisor($afterThrowingCallback));
        
        $this->handle($this->createCallbackMock());
    }
    
    /**
     * Checks if the handler executes after advices.
     */
    public function testHandlerExecutesAfterAdvices()
    {
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $adviceCallback->expects($this->once())
                       ->method(self::CALLBACK_METHOD);
        $this->advisors->after()->add($this->toAdvisor($adviceCallback));
        
        $this->handle($this->createCallbackMock());
    }
    
    /**
     * Ensures that the after advice is executed even if an exception was thrown.
     */
    public function testHandlerExecutesAfterAdviceEvenIfExceptionOccurred()
    {
        $this->setExpectedException('RuntimeException');
        
        $this->simulateManager($this->createManagerMock());
        
        $adviceCallback = $this->createCallbackMock();
        $adviceCallback->expects($this->once())
                       ->method(self::CALLBACK_METHOD);
        $this->advisors->after()->add($this->toAdvisor($adviceCallback));
        
        $mock = $this->createCallbackMock();
        $mock->expects($this->any())
             ->method(self::CALLBACK_METHOD)
             ->will($this->throwException(new RuntimeException('Test exception.')));
        
        $this->handle($mock);
    }
    
    /**
     * Uses the provided data to call the handler methods.
     *
     * The given mock object will be used to simulate the compiled method.
     * The parameters $args contains the method arguments that will be
     * passed to the compiled method.
     *
     * @param PHPUnit_Framework_MockObject_MockObject $mock
     * @param array(mixed) $args
     * @return mixed The return value of the handler call.
     */
    protected function handle(PHPUnit_Framework_MockObject_MockObject $mock, $args = array())
    {
        $handlerArgs = array(
            'original',
            self::CALLBACK_METHOD,
            $mock,
            $args
        );
        $handlerCallback = array(
            'AspectPHP_Transformation_Template_JoinPointHandler',
            'forwardToHandleCall'
        );
        return call_user_func_array($handlerCallback, $handlerArgs);
    }
    
    /**
     * Returns a mocked aspect manager.
     *
     * @return PHPUnit_Framework_MockObject_MockObject|AspectPHP_Manager
     */
    protected function createManagerMock()
    {
        $mock = $this->getMock('AspectPHP_Manager');
        $mock->expects($this->any())
             ->method('getAdvicesFor')
             ->will($this->returnValue($this->advisors));
        return $mock;
    }
    
    /**
     * Creates an empty advisor container for testing.
     *
     * @return AspectPHP_Advisor_Container
     */
    protected function createContainer()
    {
        return new AspectPHP_Advisor_Container();
    }
    
    /**
     * Creates a mock object that may be used as callback.
     *
     * The mock object offers an invoke method that may be
     * used as callback.
     *
     * @param mixed $returnValue The value that will be returned by invoke().
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function createCallbackMock($returnValue = null)
    {
        $mock = $this->getMock('stdClass', array(self::CALLBACK_METHOD, 'original'));
        $mock->expects($this->any())
             ->method(self::CALLBACK_METHOD)
             ->will($this->returnValue($returnValue));
        $message = 'Method "original" should not be called during testing.';
        $mock->expects($this->never())
             ->method('original');
        return $mock;
    }
    
    /**
     * Creates an advisor that invokes the provided callback mock.
     *
     * @param PHPUnit_Framework_MockObject_MockObject $mock
     * @return AspectPHP_Advisor
     */
    protected function toAdvisor(PHPUnit_Framework_MockObject_MockObject $mock)
    {
        return new AspectPHP_Advisor_Callback(new AspectPHP_Pointcut_All(), $this->toCallback($mock));
    }
    
    /**
     * Returns a callback identifier for the provided mock object
     * that was created by createCallbackMock().
     *
     * @param PHPUnit_Framework_MockObject_MockObject $mock
     * @return array(mixed)
     */
    protected function toCallback(PHPUnit_Framework_MockObject_MockObject $mock)
    {
        return array($mock, 'invoke');
    }
    
    /**
     * Uses the given aspect manager for testing.
     *
     * @param AspectPHP_Manager|null $manager
     */
    protected function simulateManager($manager)
    {
        AspectPHP_Container::setManager($manager);
    }
    
    /**
     * Stores the current aspect manager.
     */
    protected function storeManager()
    {
        $this->previousManager = AspectPHP_Container::hasManager() ? AspectPHP_Container::getManager() : null;
    }
    
    /**
     * Restores the previously stored aspect manager.
     */
    protected function restoreManager()
    {
        AspectPHP_Container::setManager($this->previousManager);
    }
    
    /**
     * Callback method that uses the provided join point to
     * return the value 42.
     *
     * @param AspectPHP_JoinPoint $joinPoint
     */
    public function joinPointReturnValue($joinPoint)
    {
        $this->assertInstanceOf('AspectPHP_JoinPoint', $joinPoint);
        $joinPoint->setReturnValue(42);
    }
    
    /**
     * Callback method that uses the provided join point to
     * modify the arguments.
     *
     * Sets the values 1, 2, 3 as arguments.
     *
     * @param AspectPHP_JoinPoint $joinPoint
     */
    public function joinPointModifyArgs($joinPoint)
    {
        $this->assertInstanceOf('AspectPHP_JoinPoint', $joinPoint);
        $joinPoint->setArguments(array(1, 2, 3));
    }
    
    /**
     * Callback method that checks if the join point provides
     * access to an exception.
     *
     * @param AspectPHP_JoinPoint $joinPoint
     */
    public function joinPointCheckException($joinPoint)
    {
        $this->assertInstanceOf('AspectPHP_JoinPoint', $joinPoint);
        $this->assertInstanceOf('Exception', $joinPoint->getException());
    }
    
    /**
     * Callback method that uses the join point to set
     * a BadMethodCallException.
     *
     * @param AspectPHP_JoinPoint $joinPoint
     */
    public function joinPointSetException($joinPoint)
    {
        $this->assertInstanceOf('AspectPHP_JoinPoint', $joinPoint);
        $joinPoint->setException(new BadMethodCallException('Exception provided via join point.'));
    }
    
}