<?php

namespace Dhii\WpEvents;

use Psr\EventManager\EventInterface;

/**
 * Common functionality for WordPress event managers that use callback wrappers
 * to normalize event handler parameters.
 *
 * @since [*next-version*]
 */
abstract class AbstractNormalizedEventManager extends AbstractWpEventManager
{
    /**
     * The priority of the cache clear handler.
     *
     * @since [*next-version*]
     */
    const CACHE_CLEAR_HANDLER_PRIORITY = PHP_INT_MAX;

    /**
     * A cache of event instances.
     *
     * These instances are created during event chains and are destroyed at
     * the end of event chains.
     *
     * @since [*next-version*]
     *
     * @var EventInterface[]
     */
    protected $eventCache;

    /**
     * Detaches a listener from an event.
     *
     * @param string $event the event to attach too
     * @param callable $callback a callable function
     * @param int $priority The priority that was used to initially register the listener.
     *
     * @return bool True on success, false on failure
     */
    abstract public function detach($event, $callback, $priority = self::DEFAULT_PRIORITY);

    /**
     * Gets the cached event instance for the given name, creating it if needed.
     *
     * @since [*next-version*]
     *
     * @param string $name The name of the event.
     * @param array  $args Optional array of event arguments.
     *
     * @return EventInterface The event instance.
     */
    public function _zGetCachedEvent($name, $args)
    {
        return $this->_getCachedEvent($name, $args);
    }

    /**
     * Creates an event instance in cache.
     *
     * @since [*next-version*]
     *
     * @param string $name The name of the event.
     *
     * @return EventInterface The created event instance.
     */
    protected function _createCachedEvent($name)
    {
        return $this->eventCache[$name] = $this->_createEvent($name);
    }

    /**
     * Checks if an event instance exists in cache for the given event name.
     *
     * @since [*next-version*]
     *
     * @param string $name The name of the event.
     *
     * @return bool True if an event instance exists for the given event name, false otherwise.
     */
    protected function _hasCachedEvent($name)
    {
        return isset($this->eventCache[$name]);
    }

    /**
     * Gets the cached event instance for the given name, creating it if needed.
     *
     * @since [*next-version*]
     *
     * @param string $name The name of the event.
     * @param array  $args Optional array of event arguments.
     *
     * @return EventInterface The event instance.
     */
    protected function _getCachedEvent($name, array $args = array())
    {
        if (!$this->_hasCachedEvent($name)) {
            // Create event instance if it does not exist
            $event = $this->_createCachedEvent($name);
            // Register handler to delete the event instance at the end of the chain
            $this->_registerCacheClearHandler($event);
        }

        $event = $this->eventCache[$name];

        return $this->_normalizeEvent($event, null, $args);
    }

    /**
     * Removes a cached event instance.
     *
     * @since [*next-version*]
     *
     * @param string $name The name of the event.
     *
     * @return $this This instance.
     */
    protected function _removeCachedEvent($name)
    {
        unset($this->eventCache[$name]);

        return $this;
    }

    /**
     * Removes a cached event instance.
     *
     * @since [*next-version*]
     *
     * @param string $name The name of the event.
     *
     * @return $this This instance.
     */
    public function _zRemoveCachedEvent($name)
    {
        $this->_removeCachedEvent($name);

        return $this;
    }

    /**
     * Registers a clear cache handler.
     *
     * @since [*next-version*]
     *
     * @param EventInterface $event The event instance to clear from cache.
     *
     * @return $this This instance.
     */
    protected function _registerCacheClearHandler(EventInterface $event)
    {
        $priority = static::CACHE_CLEAR_HANDLER_PRIORITY;
        $callback = $this->_createCacheClearHandler($event);

        $this->_addHook($event->getName(), $callback, $priority);

        return $this;
    }

    /**
     * Creates a cache clear handler.
     *
     * @since [*next-version*]
     *
     * @param EventInterface $event The event instance to be cleared from cache by the handler.
     *
     * @return \callable The created handler.
     */
    protected function _createCacheClearHandler(EventInterface $event)
    {
        $me       = $this;

        $callback = function($value) use ($me, $event, &$callback, $priority) {
            $me->_zRemoveCachedEvent($event->getName());
            $me->detach($event, $callback, $priority);

            return $value;
        };

        return $callback;
    }

    /**
     * Gets an event callback wrapper.
     *
     * @since [*next-version*]
     * @see _createCallbackWrapper()
     *
     * @param string   $name     Name of the event.
     * @param callable $callback Handler of the event.
     *
     * @return \Closure The wrapper.
     */
    protected function _getHandlerWrapper($name, $callback)
    {
        return $this->_createHandlerWrapper($name, $callback);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _attach($name, $callback, $priority = self::DEFAULT_PRIORITY, $numArgs = 1)
    {
        $handler = $this->_getHandlerWrapper($name, $callback);
        $this->_addHook($name, $handler, $priority, $numArgs);

        return $this;
    }

    /**
     * Creates a wrapper for an event handler, containing and replacing it.
     *
     * If the wrapper receives an {@see EventInterface}, it will use that and assume it was
     * triggered or normalized earlier by the event manager.
     * Otherwise, it will assume that regular parameters are passed, such as
     * what WordPress does, and will create an event object from that.
     * Also allows events to stop propagation.
     *
     * @since [*next-version*]
     *
     * @param string   $name     The name of the event, for which a wrapper is created.
     * @param callable $callback The callback to wrap.
     *
     * @return \Closure The wrapper.
     */
    protected function _createHandlerWrapper($name, $callback)
    {
        $me = $this;

        return function () use ($name, &$callback, &$me) {
            /* @var $me AbstractNormalizedEventManager */

            $args  = \func_get_args();
            $isEvent = \count($args) && $args[0] instanceof EventInterface;

            $event = $isEvent
                ? $args[0]
                : $me->_zGetCachedEvent($name, $args);

            /* @var $event \Psr\EventManager\EventInterface */

            if (!$event->isPropagationStopped()) {
                \call_user_func_array($callback, array($event));
            }

            return $isEvent
                ? $event
                : $event->getParam(0);
        };
    }
}
