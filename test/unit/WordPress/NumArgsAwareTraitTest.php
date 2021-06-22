<?php

namespace Dhii\EventManager\WordPress\FuncTest;

use Dhii\EventManager\WordPress\NumArgsAwareTrait as TestSubject;
use InvalidArgumentException;
use OutOfRangeException;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class NumArgsAwareTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\EventManager\WordPress\NumArgsAwareTrait';

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
        $methods = $this->mergeValues($methods, [
            '__',
            '_normalizeInt',
            '_createOutOfRangeException',
        ]);

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createOutOfRangeException')->willReturnCallback(
            function ($m, $c, $p, $a) {
                return new OutOfRangeException($m, $c, $p);
            }
        );

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
        $definition       = vsprintf('abstract class %1$s extends %2$s implements %3$s {}', [
            $paddingClassName,
            $className,
            implode(', ', $interfaceNames),
        ]);
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
     * Tests the getter and setter methods to assert whether the number of arguments is correctly stored and retrieved.
     *
     * @since [*next-version*]
     */
    public function testGetSetNumArgs()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $subject->method('_normalizeInt')->willReturnArgument(0);

        $numArgs = rand(0, 100);
        $reflect->_setNumArgs($numArgs);

        $this->assertEquals($numArgs, $reflect->_getNumArgs(), 'Expected and retrieved values are not equal.');
    }

    /**
     * Tests the getter and setter methods to assert whether invalid arguments throw an exception.
     *
     * @since [*next-version*]
     */
    public function testGetSetNumArgsInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $subject->method('_normalizeInt')->willThrowException(new InvalidArgumentException());
        $this->setExpectedException('InvalidArgumentException');

        $numArgs = uniqid('not-an-int-');
        $reflect->_setNumArgs($numArgs);
    }

    /**
     * Tests the getter and setter methods to assert whether negative numbers throw an exception.
     *
     * @since [*next-version*]
     */
    public function testGetSetNumArgsNegative()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $subject->method('_normalizeInt')->willReturnArgument(0);
        $this->setExpectedException('OutOfRangeException');

        $numArgs = rand(- 1, - 100);
        $reflect->_setNumArgs($numArgs);
    }

    /**
     * Tests the getter methods to assert whether zero is returned when the property is not initialized.
     *
     * @since [*next-version*]
     */
    public function testGetNumArgsUninitialized()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $this->assertSame(0, $reflect->_getNumArgs(), 'Expected and retrieved values are not equal.');
    }
}
