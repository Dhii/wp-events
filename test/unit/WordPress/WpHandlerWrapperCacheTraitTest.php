<?php

namespace Dhii\EventManager\WordPress\FuncTest;

use Dhii\EventManager\WordPress\WpHandlerWrapperCacheTrait as TestSubject;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class WpHandlerWrapperCacheTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\EventManager\WordPress\WpHandlerWrapperCacheTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return MockObject|TestSubject The new instance.
     */
    public function createInstance($methods = [])
    {
        $methods = $this->mergeValues(
            $methods,
            [
                '_hashWpHandler',
                '_getThrowOnPropStop',
                '_createWpHandlerWrapper',
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
     * Tests the WP handler wrapper getter method to assert whether a new wrapper is correctly created and cached.
     *
     * @since [*next-version*]
     */
    public function testGetWpHandlerWrapper()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('name-');
        $callback = function() {
        };
        $hash = uniqid('hash-');
        $throwPropStop = (bool) rand(0, 1);
        $wrapper = function() {
        };

        $subject->expects($this->once())
                ->method('_hashWpHandler')
                ->with($name, $callback)
                ->willReturn($hash);

        $subject->expects($this->once())
                ->method('_getThrowOnPropStop')
                ->willReturn($throwPropStop);

        $subject->expects($this->once())
                ->method('_createWpHandlerWrapper')
                ->with($name, $callback, $throwPropStop)
                ->willReturn($wrapper);

        $this->assertSame(
            $wrapper,
            $reflect->_getWpHandlerWrapper($name, $callback),
            'Retrieved callable is not the created wrapper for the given handler.'
        );

        $this->assertSame(
            $wrapper,
            $reflect->handlerWrappers[$hash],
            'Created wrapper for the given handler was not cached.'
        );
    }

    /**
     * Tests the WP handler wrapper getter method to assert whether an existing wrapper is correctly retrieved from
     * cache.
     *
     * @since [*next-version*]
     */
    public function testGetWpHandlerWrapperExisting()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('name-');
        $callback = function() {
        };
        $hash = uniqid('hash-');
        $wrapper = function() {
        };

        $reflect->handlerWrappers = [
            $hash => $wrapper,
        ];

        $subject->expects($this->once())
                ->method('_hashWpHandler')
                ->with($name, $callback)
                ->willReturn($hash);

        $subject->expects($this->never())
                ->method('_getThrowOnPropStop');

        $subject->expects($this->never())
                ->method('_createWpHandlerWrapper');

        $this->assertSame(
            $wrapper,
            $reflect->_getWpHandlerWrapper($name, $callback),
            'Retrieved callable is not the created wrapper for the given handler.'
        );
    }
}
