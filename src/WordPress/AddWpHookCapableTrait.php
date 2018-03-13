<?php

namespace Dhii\EventManager\WordPress;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;

/**
 * Functionality for registering WordPress hook handlers.
 *
 * @since [*next-version*]
 */
trait AddWpHookCapableTrait
{
    /**
     * Adds a hook handler to a WordPress event.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $name     The hook name.
     * @param callable          $handler  The hook handler callback.
     * @param int|null          $priority The priority of the hook - larger numbers signify later execution.
     * @param int|null          $numArgs  The number of arguments to accept from the event invoker.
     *
     * @throws InvalidArgumentException If the $name, $priority or $numArgs arguments are invalid.
     */
    protected function _addWpHook($name, callable $handler, $priority = null, $numArgs = 1)
    {
        $name = $this->_normalizeString($name);

        $priority = ($priority === null)
            ? $this->_getWpHookDefaultPriority()
            : $this->_normalizeInt($priority);

        $numArgs = ($numArgs === null)
            ? 1
            : $this->_normalizeInt($numArgs);

        add_filter($name, $handler, $priority, $numArgs);
    }

    /**
     * Retrieves the default WordPress hook priority.
     *
     * Higher numbers should indicate later execution, and lower numbers indicate earlier execution.
     *
     * @since [*next-version*]
     *
     * @return int The default hook priority.
     */
    abstract protected function _getWpHookDefaultPriority();

    /**
     * Normalizes a value into an integer.
     *
     * The value must be a whole number, or a string representing such a number,
     * or an object representing such a string.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|float|int $value The value to normalize.
     *
     * @throws InvalidArgumentException If value cannot be normalized.
     *
     * @return int The normalized value.
     */
    abstract protected function _normalizeInt($value);

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
