<?php

namespace Dhii\EventManager\FuncTest;

use Dhii\EventManager\HashCallableCapableTrait as TestSubject;
use Dhii\Util\String\StringableInterface;
use Exception as RootException;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class HashCallableCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\EventManager\HashCallableCapableTrait';

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
                '_normalizeString',
                '_createInvalidArgumentException',
                '__',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($m, $c, $p, $a) {
                return new InvalidArgumentException($m, $c, $p);
            }
        );

        return $mock;
    }

    /**
     * Creates a mock stringable instance.
     *
     * @since [*next-version*]
     *
     * @param string $string The string that the stringable should be cast to.
     *
     * @return MockObject|StringableInterface
     */
    public function createStringable($string)
    {
        $mock = $this->getMockBuilder('Dhii\Util\String\StringableInterface')
                     ->setMethods(['__toString'])
                     ->getMockForAbstractClass();

        $mock->method('__toString')->willReturn($string);

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
     * @return object The object that extends and implements the specified class and interfaces.
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
     * Tests the callable hash method with a string to ensure that normalization happens and that the result is as
     * expected.
     *
     * @since [*next-version*]
     */
    public function testHashCallableWithString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $string = uniqid('string-');
        $expected = sha1($string);

        $subject->expects($this->once())
                ->method('_normalizeString')
                ->with($string)
                ->willReturn($string);

        $actual = $reflect->_hashCallable($string);

        $this->assertEquals($expected, $actual, 'Expected and retrieved hashes do not match.');
    }

    /**
     * Tests the callable hash method with a stringable object to ensure that normalization happens and that the result
     * is as expected.
     *
     * @since [*next-version*]
     */
    public function testHashCallableWithStringable()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $string = uniqid('string-');
        $stringable = $this->createStringable($string);
        $expected = sha1($string);

        $subject->expects($this->once())
                ->method('_normalizeString')
                ->with($stringable)
                ->willReturn($string);

        $actual = $reflect->_hashCallable($stringable);

        $this->assertEquals($expected, $actual, 'Expected and retrieved hashes do not match.');
    }

    /**
     * Tests the callable hash method with an object to ensure that the result is as expected.
     *
     * @since [*next-version*]
     */
    public function testHashCallableWithObject()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $object = new stdClass();
        $expected = sha1(spl_object_hash($object));

        $actual = $reflect->_hashCallable($object);

        $this->assertEquals($expected, $actual, 'Expected and retrieved hashes do not match.');
    }

    /**
     * Tests the callable hash method with an array to ensure that the result is as expected.
     *
     * @since [*next-version*]
     */
    public function testHashCallableWithArray()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $array = [uniqid('object-'), uniqid('method-')];
        $expected = sha1(serialize($array));

        $actual = $reflect->_hashCallable($array);

        $this->assertEquals($expected, $actual, 'Expected and retrieved hashes do not match.');
    }

    /**
     * Tests the callable hash method with an invalid input to ensure that an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testHashCallableWithInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $input = rand(0, 100);
        $this->setExpectedException('InvalidArgumentException');

        $reflect->_hashCallable($input);
    }
}
