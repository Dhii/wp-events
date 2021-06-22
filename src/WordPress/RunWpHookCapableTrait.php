<?php

namespace Dhii\EventManager\WordPress;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;

/**
 * Functionality for running the handlers attached to a WordPress hook.
 *
 * @since [*next-version*]
 */
trait RunWpHookCapableTrait
{
    /**
     * Causes WordPress to execute all handlers of the specified hook.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $name Name of the hook to run handlers for.
     * @param array             $args Arguments to pass to the handler.
     *
     * @return mixed The result returned by the last handler.
     */
    protected function _runWpHook($name, array $args = [])
    {
        $name = $this->_normalizeString($name);

        // Add the event name to the args array
        \array_unshift($args, $name);

        return \call_user_func_array('apply_filters', $args);
    }

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
