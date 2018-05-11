<?php

namespace Dhii\EventManager;

use Dhii\EventManager\Event;
use Psr\EventManager\EventInterface;

/**
 * Functionality for creating event instances.
 *
 * @since [*next-version*]
 */
trait CreateEventCapableTrait
{
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
    protected function _createEvent($name, $params = [], $target = null, $propagation = true)
    {
        return new Event($name, $params, $target, $propagation);
    }
}
