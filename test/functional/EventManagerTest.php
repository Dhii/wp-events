<?php

namespace Dhii\WpEvents\FuncTest;

use Xpmock\TestCase;
use WP_Mock;
use Mockery;
use Psr\EventManager\EventInterface;

/**
 * Tests {@see Dhii\WpEvents\EventManager}.
 *
 * @since [*next-version*]
 */
class EventManagerTest extends TestCase
{
    /**
     * Class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASS_NAME = 'Dhii\\WpEvents\\EventManager';

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
     * @return \Dhii\WpEvents\EventManager The new instance.
     */
    public function createInstance()
    {
        $mock = $this->mock(static::TEST_SUBJECT_CLASS_NAME)
                ->new();

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

        $this->assertInstanceOf('Psr\\EventManager\\EventManagerInterface', $subject, 'A valid instance of the test subject could not be created');
    }

    /**
     * Tests whether the `attach()` method correctly attaches the handler to the event.
     *
     * @since [*next-version*]
     */
    public function testAttach()
    {
        $subject = Mockery::mock(static::TEST_SUBJECT_CLASS_NAME)
                ->makePartial()
                ->shouldAllowMockingProtectedMethods();
        $name = uniqid('event');
        $priority = (int) rand(1, 100);
        $output = uniqid('Hello Test!');
        $callback = function() use ($output) {
            echo $output;
        };

        $subject->shouldReceive('_addHook')
                ->once()
                ->withArgs(array(
                    $name,
                    Mockery::type('callable'),
                    $priority,
                    1
                ));

        $subject->attach($name, $callback, $priority);
    }

    /**
     * Tests that the `_addHook()` method runs as expected.
     *
     * It must add a specific filter, depending on how it is called.
     *
     * @since [*next-version*]
     */
    public function testAddHook()
    {
        $subject = $this->createInstance();
        $name = uniqid('event');
        $priority = (int) rand(1, 100);
        $output = uniqid('Hello Test!');
        $callback = function() use ($output) {
            echo $output;
        };
        $numArgs = (int) rand(1, 10);

        WP_Mock::expectFilterAdded($name, $callback, $priority, $numArgs);
        $subject->this()->_addHook($name, $callback, $priority, $numArgs);
    }

    /**
     * Tests whether the `trigger()` method correctly triggers events.
     *
     * @since [*next-version*]
     */
    public function testTrigger()
    {
        $subject = Mockery::mock(static::TEST_SUBJECT_CLASS_NAME)
                ->makePartial()
                ->shouldAllowMockingProtectedMethods();
        $name = uniqid('event');
        $target = (object) array('name' => uniqid('name'));
        $args = array(
            'apple',
            'banana',
            'orange'
        );

        $subject->shouldReceive('_runHandlers')
                ->once()
                ->with(
                    $name,
                    Mockery::on(function($arg) use ($name, &$target, $args) {
                        if (!is_array($arg)) {
                            return false;
                        }

                        $count = count($arg);
                        if ($count !== 1) {
                            return false;
                        }

                        $arg = $arg[0];
                        if (!($arg instanceof EventInterface)) {
                            return false;
                        }

                        if ($arg->getName() !== $name) {
                            return false;
                        }

                        if ($arg->getTarget() !== $target) {
                            return false;
                        }

                        if ($arg->getParams() !== $args) {
                            return false;
                        }

                        return true;
                    })
                );

        $subject->trigger($name, $target, $args);
    }

    /**
     * Verifies that `_runHandlers()` executes a given action correctly.
     *
     * @since [*next-version*]
     */
    public function testRunHandlers()
    {
        $subject = $this->createInstance();
        $name = uniqid('event');
        $target = null;
        $primary = 'apple';
        $secondary = 'banana';
        $tertiary = 'kiwi';
        $args = array(
            $primary,
        );
        $return = 'pineapple';

        WP_Mock::onFilter($name)
                ->with($args)
                ->reply($return);

        $result = $subject->this()->_runHandlers($name, $args, $target);

        $this->assertEquals($return, $result, 'Handlers were not run correctly');
    }
}
