<?php

namespace Dhii\WpEvents\FuncTest;

use Dhii\WpEvents\AbstractNormalizedEventManager;
use Mockery;
use Psr\EventManager\EventInterface;
use WP_Mock;
use Xpmock\TestCase;

/**
 * Tests {@see Dhii\WpEvents\AbstractNormalizedEventManager}.
 *
 * @since [*next-version*]
 */
class AbstractNormalizedEventManagerTest extends TestCase
{
    /**
     * The name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'Dhii\\WpEvents\\AbstractNormalizedEventManager';

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
     * @return AbstractNormalizedEventManager
     */
    public function createInstance()
    {
        $me   = $this;
        $mock = $this->mock(static::TEST_SUBJECT_CLASSNAME)
            ->detach(function($event, $callback, $priority) {
                // do nothing
            })
            ->_createException()
            ->_createEvent(function($name, $args = array(), $target = null, $propagation = true) use ($me) {
                return $me->createEventMock($name, $args, $target, $propagation);
            })
            ->new();

        return $mock;
    }

    /**
     * Creates a mock instance of an event instance.
     *
     * @since [*next-version*]
     *
     * @param string $name
     * @param array $args
     * @param mixed $target
     * @param bool $propagation
     *
     * @return EventInterface
     */
    public function createEventMock($name, array $args = array(), $target = null, $propagation = true)
    {
        $mock = $this->mock('Psr\\EventManager\\EventInterface')
            ->getName($name)
            ->getParams($args)
            ->getTarget($target)
            ->isPropagationStopped(!$propagation)
            ->setName()
            ->setTarget()
            ->setParams()
            ->stopPropagation()
            ->getParam()
            ->new();

        return $mock;
    }

    /**
     * Tests the cache event instance creation method.
     *
     * @since [*next-version*]
     */
    public function testCreateCachedEvent()
    {
        $subject = $this->createInstance();

        $event = $subject->this()->_createCachedEvent('yolo');
        $cache = $subject->this()->eventCache;

        $this->assertEquals($cache, array('yolo' => $event));
        $this->assertInstanceOf('Psr\\EventManager\\EventInterface', $event);
    }

    /**
     * Tests the cache event instance checker method.
     *
     * @since [*next-version*]
     */
    public function testHasCachedEvent()
    {
        $subject = $this->createInstance();

        $subject->this()->eventCache = array(
            'who_the_best' => $this->createEventMock('dhii')
        );

        $this->assertTrue($subject->this()->_hasCachedEvent('who_the_best'));
        $this->assertFalse($subject->this()->_hasCachedEvent('nonexistent'));
    }

    /**
     * Tests the event instance cache getter, both for on demand creation and simple retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetCachedEvent()
    {
        $subject = $this->createInstance();

        // Test retrieval
        $event = $this->createEventMock('12345');
        $subject->this()->eventCache = array(
            'foobar' => $event
        );
        $this->assertEquals($event, $subject->this()->_getCachedEvent('12345'));

        // Test creation on demand
        $this->assertEquals(
            $this->createEventMock('some_event'),
            $subject->this()->_getCachedEvent('some_event')
        );
    }

    /**
     * Tests the cached event instance removal method.
     *
     * @since [*next-version*]
     */
    public function testRemoveCachedEvent()
    {
        $subject = $this->createInstance();

        $subject->this()->eventCache = array(
            'foobar' => $this->createEventMock('12345'),
            'test'   => $this->createEventMock('mock'),
        );

        $subject->this()->_removeCachedEvent('foobar');
        $subject->this()->_removeCachedEvent('nonexistent');

        $this->assertEquals(
            array('test' => $this->createEventMock('mock')),
            $subject->this()->eventCache
        );
    }

    /**
     * Tests the clear cache handler creation method.
     *
     * @since [*next-version*]
     */
    public function testRemoveCachedEventHandler()
    {
        $subject = Mockery::mock(static::TEST_SUBJECT_CLASSNAME)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
        ;

        $name = uniqid('event');

        $subject->shouldReceive('_removeCachedEvent')
            ->once()
            ->withArgs(array(
                $name
            ))
        ;

        $this->reflect($subject)->_zRemoveCachedEventHandler($name);
    }

    /**
     * Tests the clear cache handler registration method.
     *
     * @since [*next-version*]
     */
    public function testRegisterEventCacheClearHandler()
    {
        $subject = $this->createInstance();

        $name = uniqid('event');
        $event = $this->createEventMock($name);

        $hook     = AbstractNormalizedEventManager::CACHE_CLEAR_HANDLER_EVENT;
        $callback = array($subject, '_zRemoveCachedEventHandler');
        $priority = AbstractNormalizedEventManager::CACHE_CLEAR_HANDLER_PRIORITY;

        WP_Mock::expectFilterAdded($hook, $callback, $priority, 1);

        $subject->this()->_registerEventCacheClearHandler($event);
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInstanceOf(
            static::TEST_SUBJECT_CLASSNAME, $subject, 'Subject is not a valid instance.'
        );
    }
}
