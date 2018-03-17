<?php

namespace Dhii\EventManager\WordPress\FuncTest;

use Dhii\EventManager\WordPress\CreateWpHandlerWrapperCapableTrait as TestSubject;
use Exception;
use Psr\EventManager\EventInterface;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class CreateWpHandlerWrapperCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\EventManager\WordPress\CreateWpHandlerWrapperCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return TestSubject|MockObject The new instance.
     */
    public function createInstance($methods = [])
    {
        $methods = $this->mergeValues(
            $methods,
            [
                '_getCachedEvent',
                '_createStoppedPropagationException',
                '__',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        $mock->method('__')
             ->will($this->returnArgument(0));

        return $mock;
    }

    /**
     * Creates a mock event instance.
     *
     * @since [*next-version*]
     *
     * @param string $name        The event name.
     * @param null   $target      The event target.
     * @param array  $params      The event params.
     * @param bool   $propagation True if the event can propagate, false otherwise.
     *
     * @return MockObject|EventInterface The created instance.
     */
    public function createEvent($name = '', $target = null, $params = [], $propagation = true)
    {
        $mock = $this->getMockBuilder('Psr\EventManager\EventInterface')
                     ->setMethods(
                         [
                             'getName',
                             'getTarget',
                             'getParams',
                             'getParam',
                             'setName',
                             'setTarget',
                             'setParams',
                             'stopPropagation',
                             'isPropagationStopped',
                         ]
                     )
                     ->getMockForAbstractClass();

        $mock->method('getName')->willReturn($name);
        $mock->method('getTarget')->willReturn($target);
        $mock->method('getParams')->willReturnCallback(
            function() use (&$params) {
                return $params;
            }
        );
        $mock->method('getParam')->willReturnCallback(
            function($name) use (&$params) {
                return isset($params[$name])
                    ? $params[$name]
                    : null;
            }
        );
        $mock->method('setParams')->willReturnCallback(
            function($arg) use (&$params) {
                $params = $arg;
            }
        );
        $mock->method('isPropagationStopped')->willReturn(!$propagation);

        return $mock;
    }

    /**
     * Merges the values of two arrays.
     *
     * The resulting product will be a numeric array where the values of both inputs are present, without duplicates.
     *
     * @since [*next-version*]
     *
     * @param array $destination The base array.
     * @param array $source      The array with more keys.
     *
     * @return array The array which contains unique values
     */
    public function mergeValues($destination, $source)
    {
        return array_keys(array_merge(array_flip($destination), array_flip($source)));
    }

    /**
     * Creates a mock that both extends a class and implements interfaces.
     *
     * This is particularly useful for cases where the mock is based on an
     * internal class, such as in the case with exceptions. Helps to avoid
     * writing hard-coded stubs.
     *
     * @since [*next-version*]
     *
     * @param string   $className      Name of the class for the mock to extend.
     * @param string[] $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return MockObject The object that extends and implements the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf(
            'abstract class %1$s extends %2$s implements %3$s {}',
            [
                $paddingClassName,
                $className,
                implode(', ', $interfaceNames),
            ]
        );
        eval($definition);

        return $this->getMockForAbstractClass($paddingClassName);
    }

    /**
     * Creates a new exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException|MockObject The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
                     ->setConstructorArgs([$message])
                     ->getMock();

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInternalType(
            'object',
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests the `_createWpHandlerWrapper` method to assert whether the return wrapper function is a callable.
     *
     * @since [*next-version*]
     */
    public function testCreateWpHandlerWrapper()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('name-');
        $handler = function() {
        };
        $throw = false;

        $actual = $reflect->_createWpHandlerWrapper($name, $handler, $throw);

        $this->assertInternalType(
            'callable',
            $actual,
            'Created handler wrapper is not a callable.'
        );
    }

    /**
     * Tests the `_createWpHandlerWrapper` method to assert whether the return wrapper function correctly passes the
     * given event to the original handler and whether its returned value is also the event instance.
     *
     * @since [*next-version*]
     */
    public function testHandlerWrapperWithEvent()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('name-');
        $event = $this->createEvent($name);
        $throw = false;

        $subject->expects($this->never())->method('_getCachedEvent');

        $handler = function($arg) use ($event) {
            $this->assertSame($event, $arg, 'Handler did not receive the event given to the wrapper.');
        };
        $wrapper = $reflect->_createWpHandlerWrapper($name, $handler, $throw);
        $result = $wrapper($event);

        $this->assertSame($event, $result, 'Result of the wrapper is not the event the event given to it.');
    }

    /**
     * Tests the `_createWpHandlerWrapper` method to assert whether the return wrapper function correctly retrieves the
     * event from cache and assigns the given args, and whether the wrapper's returned value is the first arg.
     *
     * @since [*next-version*]
     */
    public function testHandlerWrapperWithArgs()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('name-');
        $arg1 = uniqid('arg-');
        $arg2 = uniqid('arg-');
        $params = [$arg1, $arg2];
        $event = $this->createEvent($name, null, $params);
        $throw = false;

        $subject->expects($this->once())
                ->method('_getCachedEvent')
                ->with($name, $params)
                ->willReturn($event);

        $handler = function($arg) use ($event) {
            $this->assertSame($event, $arg, 'Handler did not receive the event given to the wrapper.');
        };
        $wrapper = $reflect->_createWpHandlerWrapper($name, $handler, $throw);
        $result = $wrapper($arg1, $arg2);

        $this->assertSame($arg1, $result, 'Result of the wrapper is not the first param of the event.');
    }

    /**
     * Tests the `_createWpHandlerWrapper` method to assert whether the return wrapper function can return the first
     * event argument when the arguments as all mapped to string keys.
     *
     * @since [*next-version*]
     */
    public function testHandlerWrapperWithAssocArgs()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('name-');
        $arg1 = uniqid('arg-');
        $arg2 = uniqid('arg-');
        $params = [$arg1, $arg2];
        $event = $this->createEvent($name, null, $params);
        $throw = false;

        $subject->expects($this->once())
                ->method('_getCachedEvent')
                ->with($name, $params)
                ->willReturn($event);

        $key = uniqid('key-');
        $param = uniqid('param-');
        $handler = function($arg) use ($event, $key, $param) {
            $this->assertSame($event, $arg, 'Handler did not receive the event given to the wrapper.');
            // Change params, using a non-numeric key
            $arg->setParams([$key => $param]);
        };
        $wrapper = $reflect->_createWpHandlerWrapper($name, $handler, $throw);
        $result = $wrapper($arg1, $arg2);

        $this->assertSame($param, $result, 'Result of the wrapper is not the first param of the event.');
    }

    /**
     * Tests the `_createWpHandlerWrapper` method to assert whether the return wrapper function does not call the
     * original callback when the event signals that propagation is stopped.
     *
     * @since [*next-version*]
     */
    public function testHandlerWrapperPropagationStopped()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('name-');
        $event = $this->createEvent($name, null, [], false);
        $throw = false;

        $handler = function($arg) use ($event) {
            $this->fail('Handler was not expected to be invoked.');
        };
        $wrapper = $reflect->_createWpHandlerWrapper($name, $handler, $throw);

        $wrapper($event);
    }

    /**
     * Tests the `_createWpHandlerWrapper` method to assert whether the return wrapper function does not call the
     * original callback and throw an exception when the event signals that propagation is stopped and the wrapper is
     * configured to throw.
     *
     * @since [*next-version*]
     */
    public function testHandlerWrapperPropagationStoppedThrow()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('name-');
        $event = $this->createEvent($name, null, [], false);
        $handler = function($arg) use ($event) {
        };
        $throw = true;

        $event->method('isPropagationStopped')->willReturn(true);

        $subject->expects($this->once())
                ->method('_createStoppedPropagationException')
                ->with($this->isType('string'), $this->anything(), $this->anything(), $event)
                ->willReturn(new Exception());

        $this->setExpectedException('Exception');

        $wrapper = $reflect->_createWpHandlerWrapper($name, $handler, $throw);

        $wrapper($event);
    }
}
