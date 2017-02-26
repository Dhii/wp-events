<?php

namespace Dhii\WpEvents;

use Psr\EventManager\EventInterface;
use Psr\EventManager\EventManagerInterface;

/**
 * Event Manager implementation for WordPress.
 *
 * This class complies with the PSR-14 Event Manager standard (as of date 15/10/2016).
 *
 * It wraps around the WordPress hook mechanism by utilizing filters as generic events, since in WordPress actions
 * are actually also filters. For this reason, an event will always be capable of yeilding a result.
 *
 * @author Miguel Muscat <miguelmuscat93@gmail.com>
 */
class EventManager implements EventManagerInterface
{
    /**
     * Constructs a new instance.
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function attach($event, $callback, $priority = 10)
    {
        $eventObject   = $this->normalizeEvent($event);
        $numArgsToPass = $this->getCallableNumParams($callback);
        $this->_addHook($name, $callback, $priority, 1);
    }

    protected function _addHook($name, $handler, $priority = 10, $numArgs = 1)
    {
        \add_filter($name, $handler, $priority, $numArgs);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function detach($event, $callback)
    {
        $eventObject = $this->normalizeEvent($event);
        \remove_filter($eventObject->getName(), $callback);
    }

    /**
     * {@inheritdoc}
     */
    public function clearListeners($event)
    {
        $eventObject = $this->normalizeEvent($event);
        \remove_all_filters($eventObject->getName());
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function trigger($event, $target = null, $argv = array())
    {
        $event = $this->normalizeEvent($event, $target, $argv);

        return $this->_runHandlers($event->getName(), array($event));
    }

    /**
     * Runs all handlers for the specified hook.
     *
     * @since [*next-version*]
     *
     * @param string $name Name of the hook to run handlers for.
     * @param array $args Args to pass to the handler.
     * @return mixed The result returned by the handlers.
     */
    protected function _runHandlers($name, array $args = array())
    {
        array_unshift($args, $name);
        $result = call_user_func_array('apply_filters', $args);
        return $result;
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
    protected function normalizeEvent($event, $target = null, $argv = array())
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
     */
    protected function _mergeArgs($base, $other)
    {
        foreach ($other as $_idx => $_element) {
            $base[$_idx] = $_element;
        }

        return $base;
    }

    /**
     * Creates a new event instance.
     *
     * @since [*next-version*]
     *
     * @param string $name        The event name.
     * @param array  $params      The event parameters.
     * @param mixed  $target      The target object. Used for context.
     * @param bool   $propagation True to propagate the event, false to not.
     * @return Event The new event.
     */
    protected function _createEvent($name, $params = array(), $target = null, $propagation = true)
    {
        return new Event($name, $params, $target, $propagation);
    }

    /**
     * Gets the number of parameters for a callable.
     *
     * @param callable $callable The callable.
     *
     * @return int The number of parameters.
     */
    protected function getCallableNumParams($callable)
    {
        return $this->getCallableReflection($callable)->getNumberOfParameters();
    }

    /**
     * Gets the reflection instance for a callable.
     *
     * @param callable $callable The callable.
     *
     * @return ReflectionFunction|ReflectionMethod The reflection instance.
     */
    protected function getCallableReflection($callable)
    {
        return is_array($callable) ?
            new \ReflectionMethod($callable[0], $callable[1]) :
            new \ReflectionFunction($callable);
    }
}
