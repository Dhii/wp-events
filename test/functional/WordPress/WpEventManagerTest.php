<?php

namespace Dhii\EventManager\WordPress\UnitTest;

use Dhii\EventManager\WordPress\WpEventManager as TestSubject;
use Dhii\Exception\InternalException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\EventManager\EventInterface;
use stdClass;
use WP_Mock;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class WpEventManagerTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\EventManager\WordPress\WpEventManager';

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
     * Tests the constructor to assert whether a valid instance can be created without enabling propagation.
     *
     * @since [*next-version*]
     */
    public function testConstructor()
    {
        try {
            $subject = new TestSubject(false);

            $this->assertInstanceOf(
                static::TEST_SUBJECT_CLASSNAME,
                $subject,
                'A valid instance of the test subject could not be created.'
            );
        } catch (InternalException $e) {
            $this->fail('A valid instance of the test subject could not be created.');
        }
    }

    /**
     * Tests the event attaching functionality to assert whether a WordPress filter is registered.
     *
     * @since [*next-version*]
     */
    public function testAttach()
    {
        $propagation = true;
        $numArgs     = rand(1, 5);

        try {
            $subject = new TestSubject($propagation, $numArgs);
        } catch (InternalException $exception) {
            $this->fail('Failed to create an instance of the test subject.');
            return;
        }

        $hook     = uniqid('hook-');
        $handler  = function () {
        };
        $priority = rand(0, 10);

        WP_Mock::expectFilterAdded($hook, $handler, $priority, $numArgs);

        $subject->attach($hook, $handler, $priority);
    }

    /**
     * Tests the event triggering functionality to assert whether WordPress filters are applied.
     *
     * @since [*next-version*]
     */
    public function testTrigger()
    {
        $propagation = true;
        $numArgs     = rand(1, 5);

        try {
            $subject = new TestSubject($propagation, $numArgs);
        } catch (InternalException $exception) {
            $this->fail('Failed to create an instance of the test subject.');
            return;
        }

        $hook     = uniqid('hook-');
        $target   = new stdClass();
        $params   = [
            rand(1, 100),
            uniqid('arg-'),
        ];
        $expected = new stdClass();

        WP_Mock::onFilter($hook)
               ->with(WP_Mock\Functions::type('Psr\EventManager\EventInterface'))
               ->reply($expected);

        $actual = $subject->trigger($hook, $target, $params);

        $this->assertSame($actual, $expected);
    }

    /**
     * Tests the event detaching functionality to assert whether WordPress filters are correctly detached and not
     * invoked when the filter is applied.
     *
     * @since [*next-version*]
     */
    public function testDetach()
    {
        $propagation = true;
        $numArgs     = rand(1, 5);

        try {
            $subject = new TestSubject($propagation, $numArgs);
        } catch (InternalException $exception) {
            $this->fail('Failed to create an instance of the test subject.');
            return;
        }

        $hook     = uniqid('hook-');
        $handler  = function () {};
        $priority = rand(0, 10);

        WP_Mock::expectFilterAdded($hook, $handler, $priority, $numArgs);
        WP_Mock::wpFunction('remove_filter', ['times' => 1]);

        $subject->attach($hook, $handler, $priority);
        $subject->detach($hook, $handler, $priority);
    }

    /**
     * Tests the event listener clearing functionality to assert whether all attached WordPress filters are correctly
     * detached.
     *
     * @since [*next-version*]
     */
    public function tetClearListeners()
    {
        $propagation = true;
        $numArgs     = rand(1, 5);

        try {
            $subject = new TestSubject($propagation, $numArgs);
        } catch (InternalException $exception) {
            $this->fail('Failed to create an instance of the test subject.');
            return;
        }

        $hook     = uniqid('hook-');
        $handler1  = function () {};
        $handler2  = function () {};
        $handler3  = function () {};
        $priority1 = rand(0, 10);
        $priority2 = rand(0, 10);
        $priority3 = rand(0, 10);

        WP_Mock::expectFilterAdded($hook, $handler1, $priority1, $numArgs);
        WP_Mock::expectFilterAdded($hook, $handler2, $priority2, $numArgs);
        WP_Mock::expectFilterAdded($hook, $handler3, $priority3, $numArgs);
        WP_Mock::wpFunction('remove_all_filters', ['times' => 1]);

        $subject->attach($hook, $handler1, $priority1);
        $subject->attach($hook, $handler2, $priority2);
        $subject->attach($hook, $handler3, $priority3);

        $subject->clearListeners($hook);
    }
}
