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
        $args = empty($argv)
            ? array(null)
            : $argv;
        array_push($args, $target);
        $eventObject = $this->normalizeEvent($event);

        return $this->_runHandlers($eventObject->getName(), $args);
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
     * @param EventInterface|string $event Event instance or an event name string.
     *
     * @return EventInterface The event instance.
     */
    protected function normalizeEvent($event)
    {
        return ($event instanceof EventInterfacevent)
            ? $event
            : new Event($event);
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
