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
     * @param string|Stringable|EventInterface $event The name of the event, or the event instance.
     * @param array                            $args  Optional array of event arguments.
     *
     * @return EventInterface The created event instance.
     */
    protected function _createCachedEvent($event, array $args = [])
    {
        $event = $this->_normalizeEvent($event, $args);
        $name  = $event->getName();

        return $this->eventCache[$name] = $event;
    }

    /**
     * Checks if an event instance exists in cache for the given event name.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|EventInterface $event The name of the event, or the event instance.
     *
     * @return bool True if an event instance exists for the given event name, false otherwise.
     */
    protected function _hasCachedEvent($event)
    {
        $name = ($event instanceof EventInterface)
            ? $event->getName()
            : $event;

        return isset($this->eventCache[$name]);
    }

    /**
     * Gets the cached event instance for the given name, creating it if needed.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|EventInterface $event The name of the event, or the event instance.
     * @param array                            $args  Optional array of event arguments.
     *
     * @return EventInterface The event instance.
     */
    protected function _getCachedEvent($event, array $args = [])
    {
        $name = ($event instanceof EventInterface)
            ? $event->getName()
            : $event;

        // Create event instance if it does not exist
        if (!$this->_hasCachedEvent($name)) {
            $this->_createCachedEvent($event, $args);
        }

        return $this->eventCache[$name];
    }

    /**
     * Removes a cached event instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|EventInterface $event The name of the event, or the event instance.
     *
     * @return $this This instance.
     */
    protected function _removeCachedEvent($event)
    {
        $name = ($event instanceof EventInterface)
            ? $event->getName()
            : $event;

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
}
