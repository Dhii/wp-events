<?php

namespace Dhii\EventManager\FuncTest;

use Dhii\EventManager\EventCacheTrait as TestSubject;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\EventManager\EventInterface;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class EventCacheTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\EventManager\EventCacheTrait';

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
                '_normalizeEvent'
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

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
     * Tests the `_createCachedEvent()` method to assert whether event instances are correctly created.
     *
     * @since [*next-version*]
     */
    public function testCreateCachedEvent()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('event-');
        $event = $this->createEvent($name);

        $subject->expects($this->once())
                ->method('_normalizeEvent')
                ->with($name)
                ->willReturn($event);

        $actual = $reflect->_createCachedEvent($name);

        $this->assertSame($event, $actual, 'Expected event instance is not the newly created instance.');
        $this->assertArrayHasKey($name, $reflect->eventCache, 'Event cache does not contain created event.');
        $this->assertSame($event, $reflect->eventCache[$name], 'Event in cache is incorrect.');
    }

    /**
     * Tests the `_hasCachedEvent()` method to assert whether the test subject correctly indicates the existence of a
     * cached event.
     *
     * @since [*next-version*]
     */
    public function testHasCachedEvent()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name1 = uniqid('event-');
        $name2 = uniqid('event-');

        $subject->method('_normalizeEvent')->willReturn($this->createEvent($name1));
        $reflect->_createCachedEvent($name1);

        $this->assertTrue(
            $reflect->_hasCachedEvent($name1),
            'Subject incorrectly indicates that a cached event does not exist.'
        );
        $this->assertFalse(
            $reflect->_hasCachedEvent($name2),
            'Subject incorrectly indicates that a cached event exists.'
        );
    }

    /**
     * Tests the `_getCachedEvent()` method to assert whether an existing event instance in the cache is correctly
     * retrieved and returned.
     *
     * @since [*next-version*]
     */
    public function testGetCachedEventExists()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('event-');
        $args = [
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
        ];
        $event = $this->createEvent($name);

        $subject->expects($this->once())
                ->method('_normalizeEvent')
                ->with($name)
                ->willReturn($event);

        $reflect->_createCachedEvent($name);

        // Expect method to not be called again
        $subject->expects($this->never())->method('_createEvent');

        $actual = $reflect->_getCachedEvent($name, $args);

        $this->assertSame($event, $actual, 'Retrieved event is not the normalized, created event.');
    }

    /**
     * Tests the `_getCachedEvent()` method to assert whether a non-existing event instance is implicitly created,
     * stored in the cache and returned.
     *
     * @since [*next-version*]
     */
    public function testGetCachedEventNotExists()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('event-');
        $args = [
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
        ];
        $event = $this->createEvent($name);

        $subject->expects($this->once())
                ->method('_normalizeEvent')
                ->with($name, $args)
                ->willReturn($event);

        $actual = $reflect->_getCachedEvent($name, $args);

        $this->assertSame($event, $actual, 'Retrieved event is not the normalized, created event.');
    }

    /**
     * Tests the `_removeCachedEvent()` method to assert whether an existing event instance in the cache is removed.
     *
     * @since [*next-version*]
     */
    public function testRemoveCachedEvent()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $name = uniqid('event-');
        $event = $this->createEvent($name);

        $subject->expects($this->once())
                ->method('_normalizeEvent')
                ->with($name)
                ->willReturn($event);

        $reflect->_createCachedEvent($name);

        $reflect->_removeCachedEvent($name);

        $this->assertFalse(
            $reflect->_hasCachedEvent($name),
            'Cached event was not removed.'
        );
    }
}
