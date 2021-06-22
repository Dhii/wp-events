<?php

namespace Dhii\EventManager\WordPress\FuncTest;

use Dhii\EventManager\WordPress\Exception\StoppedPropagationExceptionInterface;
use Dhii\EventManager\WordPress\WpHookReplacer as TestSubject;
use Mockery;
use Mockery\MockInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\EventManager\EventInterface;
use stdClass;
use WP_Hook;
use WP_Mock;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class WpHookReplacerTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\EventManager\WordPress\WpHookReplacer';

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
     * Creates a new WP_Hook instance.
     *
     * @since [*next-version*]
     *
     * @return MockInterface|WP_Hook The new instance.
     */
    public function createWpHook()
    {
        return Mockery::mock('\WP_Hook');
    }

    /**
     * Creates a mock {@see StoppedPropagationExceptionInterface} exception instance.
     *
     * @since [*next-version*]
     *
     * @return MockObject|StoppedPropagationExceptionInterface
     */
    public function createStoppedPropagationException()
    {
        return $this->mockClassAndInterfaces(
            'Exception',
            [
                'Dhii\EventManager\WordPress\Exception\StoppedPropagationExceptionInterface',
            ]
        );
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
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $wpHook = $this->createWpHook();
        $subject = new TestSubject($wpHook);

        $this->assertInstanceOf(
            static::TEST_SUBJECT_CLASSNAME,
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests the constructor to assert whether the given WP_Hook instance is correctly assigned internally.
     *
     * @since [*next-version*]
     */
    public function testConstructor()
    {
        $wpHook = $this->createWpHook();
        $subject = new TestSubject($wpHook);
        $reflect = $this->reflect($subject);

        $this->assertSame($wpHook, $reflect->wpHook, 'Internal WP_Hook instance is incorrect. ');
    }

    /**
     * Tests the subject to assert whether arbitrary method calls result in the internal WP_Hook instance receiving the
     * redirected method call.
     *
     * @since [*next-version*]
     */
    public function testMagicCall()
    {
        $wpHook = $this->createWpHook();
        $subject = new TestSubject($wpHook);

        $method = 'myMethod';
        $arg1 = uniqid('arg-');
        $arg2 = rand(0, 100);
        $return = new stdClass();

        $wpHook->shouldReceive($method)
               ->with($arg1, $arg2)
               ->andReturn($return)
               ->once();

        $actual = $subject->$method($arg1, $arg2);

        $this->assertSame(
            $return,
            $actual,
            'Return value is not the return value of the original WP_Hook method call.'
        );
    }

    /**
     * Tests the `apply_filters()` method to assert whether the internal WP_Hook instance is correctly called to invoke
     * the filters.
     *
     * @since [*next-version*]
     */
    public function testApplyFilters()
    {
        $wpHook = $this->createWpHook();
        $subject = new TestSubject($wpHook);

        $value = uniqid('value-');
        $args = [
            uniqid('arg-'),
            uniqid('arg-'),
            rand(0, 100),
        ];
        $return = uniqid('return-');

        $wpHook->shouldReceive('apply_filters')
               ->with($value, $args)
               ->andReturn($return)
               ->once();

        $actual = $subject->apply_filters($value, $args);

        $this->assertSame(
            $return,
            $actual,
            'Return value is not the return value of the WP_Hook `apply_filters` call.'
        );
    }

    /**
     * Tests the `apply_filters()` method to assert whether the internal WP_Hook instance is correctly called to invoke
     * the filters and whether stopped propagation exceptions are correctly handled and filter value returned.
     *
     * @since [*next-version*]
     */
    public function testApplyFiltersStoppedPropagation()
    {
        $wpHook = $this->createWpHook();
        $subject = new TestSubject($wpHook);

        $value = uniqid('value-');
        $args = [
            uniqid('arg-'),
            uniqid('arg-'),
            rand(0, 100),
        ];
        $return = uniqid('return-');
        $params = [
            $return,
            uniqid('arg-'),
            uniqid('arg-'),
        ];
        $event = $this->createEvent(uniqid('event-'), null, $params);
        $event->method('getParam')->willReturnCallback(
            function ($idx) use ($params) {
                return isset($params[$idx])
                    ? $params[$idx]
                    : null;
            }
        );

        $exception = $this->createStoppedPropagationException();
        $exception->expects($this->once())
                  ->method('getEvent')
                  ->willReturn($event);

        $wpHook->shouldReceive('apply_filters')
               ->with($value, $args)
               ->andThrowExceptions(
                   [
                       $exception,
                   ]
               )
               ->once();

        $actual = $subject->apply_filters($value, $args);

        $this->assertSame(
            $return,
            $actual,
            'Return value is not the return value of the WP_Hook `apply_filters` call.'
        );
    }

    /**
     * Tests the `current()` method to assert whether the call is correctly redirect to the original WP_Hook instance.
     *
     * @since [*next-version*]
     */
    public function testCurrent()
    {
        $wpHook = $this->createWpHook();
        $subject = new TestSubject($wpHook);

        $return = uniqid('return-');

        $wpHook->shouldReceive('current')
               ->andReturn($return)
               ->once();

        $actual = $subject->current();

        $this->assertSame(
            $return,
            $actual,
            'Retrieved value is not the return value of the WP_Hook\'s `current` method.'
        );
    }

    /**
     * Tests the `key()` method to assert whether the call is correctly redirect to the original WP_Hook instance.
     *
     * @since [*next-version*]
     */
    public function testKey()
    {
        $wpHook = $this->createWpHook();
        $subject = new TestSubject($wpHook);

        $return = uniqid('return-');

        $wpHook->shouldReceive('key')
               ->andReturn($return)
               ->once();

        $actual = $subject->key();

        $this->assertSame(
            $return,
            $actual,
            'Retrieved value is not the return value of the WP_Hook\'s `key` method.'
        );
    }

    /**
     * Tests the `next()` method to assert whether the call is correctly redirect to the original WP_Hook instance.
     *
     * @since [*next-version*]
     */
    public function testNext()
    {
        $wpHook = $this->createWpHook();
        $subject = new TestSubject($wpHook);

        $wpHook->shouldReceive('next')
               ->once();

        $subject->next();
    }

    /**
     * Tests the `valid()` method to assert whether the call is correctly redirect to the original WP_Hook instance.
     *
     * @since [*next-version*]
     */
    public function testValid()
    {
        $wpHook = $this->createWpHook();
        $subject = new TestSubject($wpHook);

        $return = (bool) rand(0, 1);

        $wpHook->shouldReceive('valid')
               ->andReturn($return)
               ->once();

        $actual = $subject->valid();

        $this->assertSame(
            $return,
            $actual,
            'Retrieved value is not the return value of the WP_Hook\'s `valid` method.'
        );
    }

    /**
     * Tests the `rewind()` method to assert whether the call is correctly redirect to the original WP_Hook instance.
     *
     * @since [*next-version*]
     */
    public function testRewind()
    {
        $wpHook = $this->createWpHook();
        $subject = new TestSubject($wpHook);

        $wpHook->shouldReceive('rewind')
               ->once();

        $subject->rewind();
    }

    /**
     * Tests the `offsetExists()` method to assert whether the call is correctly redirect to the original WP_Hook
     * instance.
     *
     * @since [*next-version*]
     */
    public function testOffsetExists()
    {
        $wpHook = $this->createWpHook();
        $subject = new TestSubject($wpHook);

        $offset = uniqid('offset-');
        $return = (bool) rand(0, 1);

        $wpHook->shouldReceive('offsetExists')
               ->with($offset)
               ->andReturn($return)
               ->once();

        $actual = isset($subject[$offset]);

        $this->assertSame(
            $return,
            $actual,
            'Retrieved value is not the return value of the WP_Hook\'s `offsetExists` method.'
        );
    }

    /**
     * Tests the `offsetGet()` method to assert whether the call is correctly redirect to the original WP_Hook
     * instance.
     *
     * @since [*next-version*]
     */
    public function testOffsetGet()
    {
        $wpHook = $this->createWpHook();
        $subject = new TestSubject($wpHook);

        $offset = uniqid('offset-');
        $return = uniqid('value-');

        $wpHook->shouldReceive('offsetGet')
               ->with($offset)
               ->andReturn($return)
               ->once();

        $actual = $subject[$offset];

        $this->assertSame(
            $return,
            $actual,
            'Retrieved value is not the return value of the WP_Hook\'s `offsetGet` method.'
        );
    }

    /**
     * Tests the `offsetSet()` method to assert whether the call is correctly redirect to the original WP_Hook
     * instance.
     *
     * @since [*next-version*]
     */
    public function testOffsetSet()
    {
        $wpHook = $this->createWpHook();
        $subject = new TestSubject($wpHook);

        $offset = uniqid('offset-');
        $value = uniqid('value-');

        $wpHook->shouldReceive('offsetSet')
               ->with($offset, $value)
               ->once();

        $subject[$offset] = $value;
    }

    /**
     * Tests the `offsetUnset()` method to assert whether the call is correctly redirect to the original WP_Hook
     * instance.
     *
     * @since [*next-version*]
     */
    public function testOffsetUnset()
    {
        $wpHook = $this->createWpHook();
        $subject = new TestSubject($wpHook);

        $offset = uniqid('offset-');

        $wpHook->shouldReceive('offsetUnset')
               ->with($offset)
               ->once();

        unset($subject[$offset]);
    }
}
