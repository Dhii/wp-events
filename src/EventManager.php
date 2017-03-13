<?php

namespace Dhii\WpEvents;

use Psr\EventManager\EventManagerInterface;

/**
 * Event Manager implementation for WordPress.
 *
 * This class aims to comply with the PSR-14 Event Manager standard (as of date 15/10/2016).
 *
 * It wraps around the WordPress hook mechanism by utilizing filters as generic events, since in WordPress actions
 * are actually also filters. Hook handlers are standardized to receive only
 * one parameter - the event instance, avoiding the need to specify number of
 * handler args, and allowing more than one piece of data to be changed by
 * one event's handlers.
 *
 * @since [*next-version*]
 */
class EventManager extends AbstractWrapperCachingEventManager implements EventManagerInterface
{
    /**
     * Constructs a new instance.
     *
     * @since [*next-version*]
     */
    public function __construct()
    {
        $this->_construct();
        $this->_registerEventCacheClearHandler();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @return EventManager This instance.
     */
    public function attach($name, $callback, $priority = self::DEFAULT_PRIORITY)
    {
        $this->_attach($name, $callback, $priority);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function detach($event, $callback, $priority = self::DEFAULT_PRIORITY)
    {
        $this->_detach($event, $callback, $priority);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function clearListeners($event)
    {
        $this->_clearListeners($event);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function trigger($event, $target = null, $argv = array())
    {
        return $this->_trigger($event, $target, $argv);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createException($message = '', $code = 0, \Exception $previous = null, $eventName = null)
    {
        return new Exception\Exception($message, $code, $previous, $eventName);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createEvent($name, $params = array(), $target = null, $propagation = true)
    {
        return new Event($name, $params, $target, $propagation);
    }
}
