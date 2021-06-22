<?php

namespace Dhii\EventManager\UnitTest;

use Dhii\EventManager\NormalizeEventCapableTrait as TestSubject;
use Dhii\Util\String\StringableInterface;
use Exception as RootException;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\EventManager\EventInterface;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class NormalizeEventCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\EventManager\NormalizeEventCapableTrait';

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
                '_createEvent',
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
            function ($m, $c, $p, $a) {
                return new InvalidArgumentException($m, $c, $p);
            }
        );

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
        $mock->method('getParams')->willReturn($params);
        $mock->method('isPropagationStopped')->willReturn($propagation);

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
     * @return MockObject|RootException The new exception.
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
     * Tests the event normalization method with a string event to assert whether the result is a valid event instance
     * with the appropriate data set to it.
     *
     * @since [*next-version*]
     */
    public function testNormalizeEventWithString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $eventName = uniqid('event-');
        $eventNameNormalized = uniqid('event-');

        $subject->expects($this->once())
                ->method('_normalizeString')
                ->with($eventName)
                ->willReturn($eventNameNormalized);

        $target = new stdClass();
        $params = [
            uniqid('key-') => uniqid('arg-'),
            uniqid('key-') => uniqid('arg-'),
        ];

        $eventObj = $this->createEvent($eventNameNormalized, $target, $params, true);
        $subject->expects($this->once())
                ->method('_createEvent')
                ->with($eventNameNormalized, $params, $target, true)
                ->willReturn($eventObj);

        /* @var $actual EventInterface */
        $actual = $reflect->_normalizeEvent($eventName, $params, $target);

        $this->assertSame($eventObj, $actual, 'Returned event object is not the internally created instance.');
    }

    /**
     * Tests the event normalization method with a stringable event to assert whether the result is a valid event
     * instance with the appropriate data set to it.
     *
     * @since [*next-version*]
     */
    public function testNormalizeEventWithStringable()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $eventName = $this->createStringable(uniqid('event-'));
        $eventNameNormalized = uniqid('event-');

        $subject->expects($this->once())
                ->method('_normalizeString')
                ->with($eventName)
                ->willReturn($eventNameNormalized);

        $target = new stdClass();
        $params = [
            uniqid('key-') => uniqid('arg-'),
            uniqid('key-') => uniqid('arg-'),
        ];

        $eventObj = $this->createEvent($eventNameNormalized, $target, $params, true);
        $subject->expects($this->once())
                ->method('_createEvent')
                ->with($eventNameNormalized, $params, $target, true)
                ->willReturn($eventObj);

        /* @var $actual EventInterface */
        $actual = $reflect->_normalizeEvent($eventName, $params, $target);

        $this->assertSame($eventObj, $actual, 'Returned event object is not the internally created instance.');
    }

    /**
     * Tests the event normalization method with an event instance to assert whether the result is the same event
     * instance.
     *
     * @since [*next-version*]
     */
    public function testNormalizeEventWithEvent()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $eventName = uniqid('event-');
        $eventTarget = new stdClass();
        $eventParams = [
            uniqid('key-') => uniqid('arg-'),
            uniqid('key-') => uniqid('arg-'),
        ];
        $event = $this->createEvent($eventName, $eventTarget, $eventParams);

        /* @var $actual EventInterface */
        $actual = $reflect->_normalizeEvent($event);

        $this->assertSame($event, $actual, 'Returned event object is not the internally created instance.');
    }

    /**
     * Tests the event normalization method with an event instance to assert whether the result is the same event
     * instance with the new target assigned to it.
     *
     * @since [*next-version*]
     */
    public function testNormalizeEventWithEventOverwriteTarget()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $eventName = uniqid('event-');
        $eventTarget = new stdClass();
        $eventParams = [
            uniqid('key-') => uniqid('arg-'),
            uniqid('key-') => uniqid('arg-'),
        ];
        $event = $this->createEvent($eventName, $eventTarget, $eventParams);
        $newTarget = new stdClass();

        $event->expects($this->once())
              ->method('setTarget')
              ->with($newTarget);

        /* @var $actual EventInterface */
        $actual = $reflect->_normalizeEvent($event, [], $newTarget);

        $this->assertSame($event, $actual, 'Returned event object is not the internally created instance.');
    }

    /**
     * Tests the event normalization method with an event instance to assert whether the result is the same event
     * instance with the new params assigned to it.
     *
     * @since [*next-version*]
     */
    public function testNormalizeEventWithEventOverwriteParams()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $eventName = uniqid('event-');
        $eventTarget = new stdClass();
        $eventParams = [
            uniqid('key-') => uniqid('arg-'),
            uniqid('key-') => uniqid('arg-'),
        ];
        $event = $this->createEvent($eventName, $eventTarget, $eventParams);

        $newParams = [
            uniqid('key-') => uniqid('arg-'),
            uniqid('key-') => uniqid('arg-'),
        ];

        $combinedParams = $eventParams;
        foreach ($newParams as $_i => $_p) {
            $combinedParams[$_i] = $_p;
        }

        $event->expects($this->once())
              ->method('setParams')
              ->with($combinedParams);

        /* @var $actual EventInterface */
        $actual = $reflect->_normalizeEvent($event, $newParams);

        $this->assertSame($event, $actual, 'Returned event object is not the internally created instance.');
    }

    /**
     * Tests the event normalization method with an invalid argument to assert whether an invalid-argument-exception
     * is thrown.
     *
     * @since [*next-version*]
     */
    public function testNormalizeEventWithInvalidEvent()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $event = new stdClass();

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_normalizeEvent($event);
    }
}
