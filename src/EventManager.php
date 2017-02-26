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
     * A cache of callback wrappers, by hash.
     *
     * @since [*next-version*]
     *
     * @var callable[]
     */
    protected $callbackWrappers = array();

    /**
     * Constructs a new instance.
     *
     * @since [*next-version*]
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function attach($name, $callback, $priority = 10)
    {
        $handler = $this->_getCallbackWrapper($name, $callback);
        $this->_addHook($name, $handler, $priority, 1);
    }

    /**
     * Adds a hook to the environment.
     *
     * @since [*next-version*]
     *
     * @see add_filter()
     *
     * @param string $name
     * @param callable $handler
     * @param int $priority
     * @param int $numArgs
     * @return EventManager This instance.
     */
    protected function _addHook($name, $handler, $priority = 10, $numArgs = 1)
    {
        \add_filter($name, $handler, $priority, $numArgs);

        return $this;
    }

    /**
     * Gets a cached callback wrapper, creating it if no wrapper exists yet for given parameters.
     *
     * @since [*next-version*]
     *
     * @see _createCallbackWrapper()
     *
     * @param string $name
     * @param callable $callback
     * @return \Closure
     */
    protected function _getCallbackWrapper($name, $callback)
    {
        $cbHash = $this->_hashCallable($callback);
        if (!isset($this->callbackWrappers[$cbHash])) {
            $this->callbackWrappers[$cbHash] = $this->_createCallbackWrapper($name, $callback);
        }

        return $this->callbackWrappers[$cbHash];
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
     * @param string $name The name of the event, for which a wrapper is created.
     * @param callable $callback The callback to wrap.
     * @return \Closure The wrapper.
     */
    protected function _createCallbackWrapper($name, &$callback)
    {
        $me = $this;
        return function() use ($name, &$callback, &$me) {
            $args = \func_get_args();
            $event = \count($args) && $args[0] instanceof EventInterface
                    ? $args[0]
                    : $me->createEvent($name, $args);
            /* @var $event \Dhii\WpEvents\Event */
            if (!$event->isPropagationStopped()) {
                \call_user_func_array($callback, array($event));
            }

            return $event;
        };
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
    public function createEvent($name, $params = array(), $target = null, $propagation = true)
    {
        return $this->_createEvent($name, $params, $target, $propagation);
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

    /**
     * Computes a hash of a given callable.
     *
     * @since [*next-version*]
     *
     * @param callable $callable The callable to hash.
     * @return string A hash of the callable.
     * @throws \InvalidArgumentException If not a valid callable.
     */
    protected function _hashCallable($callable)
    {
        if (is_object($callable)) {
            return $this->_hashObject($callable);
        }

        if (is_array($callable)) {
            return $this->_hashArray($callable);
        }

        throw new \InvalidArgumentException('Could not hash: not a valid callback');
    }

    /**
     * Computes a hash of the array.
     *
     * Accounts for nested arrays.
     *
     * @since [*next-version*]
     *
     * @param array $array The array to hash.
     * @return string A hash of the array.
     */
    protected function _hashArray(array $array)
    {
        $itemHashes = array();
        foreach ($array as $_idx => $_item) {
            if (is_array($_item)) {
                $itemHashes[$_idx] = $this->_hashArray($_item);
            } elseif (is_object($_item)) {
                $itemHashes[$_idx] = $this->_hashObject($_item);
            } elseif (is_resource($_item)) {
                $itemHashes[$_idx] = (string) $_item;
            } else {
                $itemHashes[$_idx] = $_item;
            }
        }

        $itemHashes = serialize($itemHashes);

        return $this->_hashScalar($itemHashes);
    }

    /**
     * Computes a hash of an object.
     *
     * The same object will always have the same hash.
     * Different identical objects will produce different results.
     *
     * @since [*next-version*]
     *
     * @param object $object The object to hash.
     * @return string A hash of the object.
     */
    protected function _hashObject($object)
    {
        return \spl_object_hash($object);
    }

    /**
     * Computes a hash of a scalar value.
     *
     * @since [*next-version*]
     *
     * @param string|int|float|bool $value The value to hash.
     * @return string A hash of the scalar value.
     */
    protected function _hashScalar($value)
    {
        return \sha1($value);
    }
}
