<?php

namespace Dhii\WpEvents;

use Psr\EventManager\EventInterface;
use Dhii\WpEvents\Exception\ExceptionInterface;

/**
 * Common functionality for event managers.
 *
 * @since [*next-version*]
 */
abstract class AbstractEventManager
{
    const DEFAULT_PRIORITY = 10;

    /**
     * Parameter-less constructor.
     * 
     * @since [*next-version*]
     */
    protected function _construct()
    {
    }

    /**
     * Normalizes the given event into an Event instance.
     *
     * @since [*next-version*]
     *
     * @param EventInterface|string $event Event instance or an event name string.
     * @param object $target The target of the event.
     * @param array $argv Arguments for the event.
     *
     * @return EventInterface The event instance.
     */
    protected function _normalizeEvent($event, $target = null, $argv = array())
    {
        $normalizedEvent = $event instanceof EventInterface
                ? $event
                : $this->_createEvent($event, $argv, $target);
        /* @var $normalizedEvent \Psr\EventManager\EventInterface */
        $normalizedEvent->setParams($this->_mergeArgs($normalizedEvent->getParams(), $argv));

        return $normalizedEvent;
    }

    /**
     * Merges values from an array into a base array, overwriting values irrespective of type.
     *
     * @since [*next-version*]
     *
     * @param array $base The array to merge into.
     * @param array $other The array to merge from.
     *
     * @return array The result of merging the arrays.
     */
    protected function _mergeArgs($base, $other)
    {
        foreach ($other as $_idx => $_element) {
            $base[$_idx] = $_element;
        }

        return $base;
    }

    /**
     * Creates a new exception related to events.
     *
     * @since [*next-version*]
     *
     * @see \Exception::__construct()
     *
     * @param string $eventName The name of a related event, if any.
     * See {@see ExceptionInterface::getEventName()}.
     *
     * @return ExceptionInterface The new exception.
     */
    abstract protected function _createException($message = '', $code = 0, \Exception $previous = null, $eventName = null);

    /**
     * Creates a new event instance.
     *
     * @since [*next-version*]
     *
     * @param string $name        The event name.
     * @param array  $params      The event parameters.
     * @param mixed  $target      The target object. Used for context.
     * @param bool   $propagation True to propagate the event, false to not.
     * @return EventInterface The new event.
     */
    abstract protected function _createEvent($name, $params = array(), $target = null, $propagation = true);

    /**
     * Attaches an event handler to an event identified by name.
     *
     * @since [*next-version*]
     *
     * @param string $name Name of the event.
     * @param callable $callback The event handler.
     * @param int $priority Priority of the event handler. Lower first.
     */
    abstract protected function _attach($name, $callback, $priority = self::DEFAULT_PRIORITY);

    /**
     * Fires an event with the specified name.
     *
     * @since [*next-version*]
     *
     * @param string|EventInterface $event An event to trigger.
     * Alternatively, the name of an event to create and trigger.
     * @param mixed $target Target of the event, if any.
     * A target is something that an event applies to.
     * @param array $argv A list of additional arguments for the event.
     * Arguments with same index as parameter names will overwrite corresponding parameters.
     *
     * @return EventInterface The event, after it has been processed by all available handlers.
     */
    abstract protected function _trigger($event, $target = null, $argv = array());

    /**
     * Detaches a handler from an event.
     *
     * @since [*next-version*]
     *
     * @param string $event The event to detach from.
     * @param callable $callback The handler to detach.
     */
    abstract protected function _detach($event, $callback);

    /**
     * Detaches all handlers from and event.
     *
     * @since [*next-version*]
     */
    abstract protected function _clearListeners($event);
}
