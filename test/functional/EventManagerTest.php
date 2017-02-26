<?php

namespace Dhii\WpEvents\FuncTest;

use Xpmock\TestCase;
use WP_Mock;
use Mockery;

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
}
