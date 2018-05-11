<?php

namespace Dhii\EventManager\WordPress\FuncTest;

use Dhii\EventManager\WordPress\AddWpHookCapableTrait as TestSubject;
use Exception as RootException;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use WP_Mock;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class AddWpHookCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\EventManager\WordPress\AddWpHookCapableTrait';

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function setUp()
    {
        WP_Mock::setUp();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function tearDown()
    {
        WP_Mock::tearDown();
    }

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
                '_getWpHookDefaultPriority',
                '_normalizeInt',
                '_normalizeString',
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
     * The resulting product will be a numeric array where the values of both inputs are present, without
     * duplicates.
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
     * Tests the `_addWpHook()` method to assert whether the WordPress function for adding hooks is called.
     *
     * @since [*next-version*]
     */
    public function testAddWpHook()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('name-');
        $nName = uniqid('name-');
        $handler = function () {
        };
        $priority = rand(0, 100);
        $nPriority = rand(0, 100);
        $numArgs = rand(0, 10);
        $nNumArgs = rand(0, 10);

        $subject->expects($this->once())
                ->method('_normalizeString')
                ->with($name)
                ->willReturn($nName);

        $subject->expects($this->exactly(2))
                ->method('_normalizeInt')
                ->withConsecutive([$priority], [$numArgs])
                ->willReturnOnConsecutiveCalls($nPriority, $nNumArgs);

        WP_Mock::expectFilterAdded($nName, $handler, $nPriority, $nNumArgs);

        $reflect->_addWpHook($name, $handler, $priority, $numArgs);
    }

    /**
     * Tests the `_addWpHook()` method to assert whether an exception is thrown when the integer normalization
     * fails.
     *
     * @since [*next-version*]
     */
    public function testAddWpHookNormalizeIntFail()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('name-');
        $nName = uniqid('name-');
        $handler = function () {
        };
        $priority = rand(0, 100);
        $numArgs = rand(0, 10);

        $subject->expects($this->once())
                ->method('_normalizeString')
                ->with($name)
                ->willReturn($nName);

        $subject->method('_normalizeInt')
                ->willThrowException(new InvalidArgumentException());

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_addWpHook($name, $handler, $priority, $numArgs);
    }

    /**
     * Tests the `_addWpHook()` method to assert whether an exception is thrown when the string normalization
     * fails.
     *
     * @since [*next-version*]
     */
    public function testAddWpHookNormalizeStringFail()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('name-');
        $handler = function () {
        };
        $priority = rand(0, 100);
        $numArgs = rand(0, 10);

        $subject->method('_normalizeString')
                ->willThrowException(new InvalidArgumentException());

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_addWpHook($name, $handler, $priority, $numArgs);
    }

    /**
     * Tests the `_addWpHook()` method to assert whether the priority defaults correctly when `null` is given.
     *
     * @since [*next-version*]
     */
    public function testAddWpHookUseDefaultPriority()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('name-');
        $nName = uniqid('name-');
        $handler = function () {
        };
        $defPriority = rand(0, 100);
        $numArgs = rand(0, 10);
        $nNumArgs = rand(0, 10);

        $subject->expects($this->once())
                ->method('_normalizeString')
                ->with($name)
                ->willReturn($nName);

        $subject->expects($this->once())
                ->method('_normalizeInt')
                ->with($numArgs)
                ->willReturn($nNumArgs);

        $subject->expects($this->once())
                ->method('_getWpHookDefaultPriority')
                ->willReturn($defPriority);

        WP_Mock::expectFilterAdded($nName, $handler, $defPriority, $nNumArgs);

        $reflect->_addWpHook($name, $handler, null, $numArgs);
    }

    /**
     * Tests the `_addWpHook()` method to assert whether the number of hook arguments defaults correctly when
     * `null` is given.
     *
     * @since [*next-version*]
     */
    public function testAddWpHookUseDefaultNumArgs()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('name-');
        $nName = uniqid('name-');
        $handler = function () {
        };
        $priority = rand(0, 100);
        $nPriority = rand(0, 100);

        $subject->expects($this->once())
                ->method('_normalizeString')
                ->with($name)
                ->willReturn($nName);

        $subject->expects($this->once())
                ->method('_normalizeInt')
                ->with($priority)
                ->willReturn($nPriority);

        WP_Mock::expectFilterAdded($nName, $handler, $nPriority, 1);

        $reflect->_addWpHook($name, $handler, $priority, null);
    }
}
