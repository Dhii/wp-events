<?php

namespace Dhii\EventManager\WordPress;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use ReflectionException;
use ReflectionMethod;

/**
 * Functionality for allowing objects to attach handlers for their own methods to an event.
 *
 * @since [*next-version*]
 */
trait AttachMethodHandlerCapableTrait
{
    /**
     * Attaches a handler for a method on this instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $eventName  The name of the event to attach to.
     * @param string|Stringable $methodName The name of the method to attach.
     * @param int|null          $priority   The priority to attach with.
     *
     * @throws ReflectionException      If a handler for the method could not be retrieved.
     * @throws InvalidArgumentException If the given event name is not a valid string.
     * @throws InvalidArgumentException If the given method name is not a valid string.
     * @throws InvalidArgumentException If the given priority is not a valid integer.
     */
    protected function _attachMethodHandler($eventName, $methodName, $priority = null)
    {
        $methodName = $this->_normalizeString($methodName);
        $reflection = $this->_createReflectionMethod(get_class($this), $methodName);
        $handler    = $reflection->getClosure($this);

        $this->_addWpHook($eventName, $handler, $priority);
    }

    /**
     * Creates a new reflection for a method of a class.
     *
     * The function must exist.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $className  The class name.
     * @param string|Stringable $methodName The method name.
     *
     * @throws ReflectionException If the reflection could not be created.
     *
     * @return ReflectionMethod The new reflection.
     */
    abstract protected function _createReflectionMethod($className, $methodName);

    /**
     * Adds a hook handler to a WordPress event.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $name     The hook name.
     * @param callable          $handler  The hook handler callback.
     * @param int|null          $priority The priority of the hook - larger numbers signify later execution.
     * @param int               $numArgs  The number of arguments to accept from the event invoker.
     */
    abstract protected function _addWpHook($name, callable $handler, $priority = null, $numArgs = 1);

    /**
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see StringableInterface).
     *
     * @since [*next-version*]
     *
     * @param Stringable|string|int|float|bool $subject The value to normalize to string.
     *
     * @throws InvalidArgumentException If the value cannot be normalized.
     *
     * @return string The string that resulted from normalization.
     */
    abstract protected function _normalizeString($subject);
}
