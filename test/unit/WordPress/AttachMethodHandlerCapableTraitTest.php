<?php

namespace Dhii\EventManager\WordPress\FuncTest;

use Dhii\EventManager\WordPress\AttachMethodHandlerCapableTrait as TestSubject;
use InvalidArgumentException;
use ReflectionException;
use ReflectionMethod;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class AttachMethodHandlerCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\EventManager\WordPress\AttachMethodHandlerCapableTrait';

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
                '_addWpHook',
                '_normalizeString',
                '_createReflectionMethod',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

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
     * Tests the `_attachMethodHandler()` method to assert whether a handler for the method with the given name is
     * correctly attached to the event with the given name.
     *
     * @since [*next-version*]
     */
    public function testAttachMethodHandler()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $eventName = uniqid('event-');
        $methodName = uniqid('method-');
        $nMethodName = uniqid('method-');
        $priority = rand(0, 100);
        $closure = function () {
        };
        $refMethod = $this->getMockBuilder('ReflectionMethod')
                          ->setMethods(['getClosure'])
                          ->disableOriginalConstructor()
                          ->getMock();
        $refMethod->expects($this->once())
                  ->method('getClosure')
                  ->willReturn($closure);

        $subject->expects($this->once())
                ->method('_normalizeString')
                ->with($methodName)
                ->willReturn($nMethodName);

        $subject->expects($this->once())
                ->method('_createReflectionMethod')
                ->with(get_class($subject), $nMethodName)
                ->willReturn($refMethod);

        $subject->expects($this->once())
                ->method('_addWpHook')
                ->with($eventName, $closure, $priority);

        $reflect->_attachMethodHandler($eventName, $methodName, $priority);
    }

    /**
     * Tests the `_attachMethodHandler()` method to assert whether reflection exceptions thrown internally bubble out.
     *
     * @since [*next-version*]
     */
    public function testAttachMethodHandlerReflectionException()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $eventName = uniqid('event-');
        $methodName = uniqid('method-');
        $nMethodName = uniqid('method-');
        $priority = rand(0, 100);

        $subject->expects($this->once())
                ->method('_normalizeString')
                ->with($methodName)
                ->willReturn($nMethodName);

        $subject->expects($this->once())
                ->method('_createReflectionMethod')
                ->with(get_class($subject), $nMethodName)
                ->willThrowException(new ReflectionException());

        $this->setExpectedException('ReflectionException');

        $reflect->_attachMethodHandler($eventName, $methodName, $priority);
    }

    /**
     * Tests the `_attachMethodHandler()` method to assert whether invalid-arg exceptions thrown internally by failure
     * to normalize the method string name will bubble out.
     *
     * @since [*next-version*]
     */
    public function testAttachMethodHandlerStringNormalizationFail()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $eventName = uniqid('event-');
        $methodName = uniqid('method-');
        $priority = rand(0, 100);

        $subject->expects($this->once())
                ->method('_normalizeString')
                ->with($methodName)
                ->willThrowException(new InvalidArgumentException());

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_attachMethodHandler($eventName, $methodName, $priority);
    }
}
