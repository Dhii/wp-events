<?php

namespace Dhii\EventManager;

use Dhii\Util\String\StringableInterface as Stringable;
use Psr\EventManager\EventInterface;

/**
 * Functionality for caching event instances.
 *
 * @since [*next-version*]
 */
trait EventCacheTrait
{
    /**
     * A cache of event instances.
     *
     * @since [*next-version*]
     *
     * @var EventInterface[]
     */
    protected $eventCache;

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
    protected function _getCachedEvent($name, array $args = [])
    {
        // Create event instance if it does not exist
        if (!$this->_hasCachedEvent($name)) {
            $this->_createCachedEvent($name);
        }

        return $this->_normalizeEvent($this->eventCache[$name], $args);
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
     * Normalizes the given event into an Event instance.
     *
     * @since [*next-version*]
     *
     * @param EventInterface|string|Stringable $event  Event instance or an event name string.
     * @param array                            $params The event parameters; will be added to existing parameters if
     *                                                 the $event argument is an {@see EventInterface} instance.
     * @param object                           $target The target of the event.
     *
     * @return EventInterface The event instance.
     */
    abstract protected function _normalizeEvent($event, $params = [], $target = null);

    /**
     * Creates a new event instance.
     *
     * @since [*next-version*]
     *
     * @param string $name        The event name.
     * @param array  $params      The event parameters.
     * @param mixed  $target      The target object. Used for context.
     * @param bool   $propagation True to propagate the event, false to not.
     *
     * @return EventInterface The new event.
     */
    abstract protected function _createEvent($name, $params = [], $target = null, $propagation = true);
}
