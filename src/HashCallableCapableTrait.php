<?php

namespace Dhii\EventManager;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use OutOfRangeException;

/**
 * Functionality for generating hashes for all types of callables.
 *
 * @since [*next-version*]
 */
trait HashCallableCapableTrait
{
    /**
     * Computes a hash of a given callable.
     *
     * @since [*next-version*]
     *
     * @param callable|Stringable $callable The callable to hash.
     *
     * @return string A hash of the callable.
     *
     * @throws OutOfRangeException If the argument is not a valid callable.
     * @throws InvalidArgumentException If argument is a string/stringable and cannot be normalized.
     */
    protected function _hashCallable($callable)
    {
        $key = null;

        if (is_string($callable) || $callable instanceof Stringable) {
            $key = $this->_normalizeString($callable);
        } elseif (is_object($callable)) {
            $key = spl_object_hash($callable);
        } elseif (is_array($callable)) {
            $key = serialize($callable);
        }

        if ($key === null) {
            throw $this->_createInvalidArgumentException(
                $this->__('Failed to hash - not a valid callable'),
                null,
                null,
                $callable
            );
        }

        return sha1($key);
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

    /**
     * Creates a new Dhii invalid argument exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param mixed|null             $argument The invalid argument, if any.
     *
     * @return InvalidArgumentException The new exception.
     */
    abstract protected function _createInvalidArgumentException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
    );

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     * @see   sprintf()
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = [], $context = null);
}
