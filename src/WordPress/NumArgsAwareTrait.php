<?php

namespace Dhii\EventManager\WordPress;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use OutOfRangeException;

/**
 * Common functionality for awareness of the number of arguments to be passed to native WordPress hook handlers.
 *
 * @since [*next-version*]
 */
trait NumArgsAwareTrait
{
    /**
     * The argument count value to pass to the native WordPress action and filter hooking functions.
     *
     * @since [*next-version*]
     *
     * @var int
     */
    protected $numArgs;

    /**
     * Retrieves the argument count value to pass to the native WordPress action and filter hooking functions.
     *
     * @since [*next-version*]
     *
     * @return int The number of arguments.
     */
    protected function _getNumArgs()
    {
        return $this->numArgs;
    }

    /**
     * Sets the argument count value to pass to the native WordPress action and filter hooking functions.
     *
     * @since [*next-version*]
     *
     * @param int $numArgs A positive argument count to pass to the native WordPress action and filter hooking
     *                     functions.
     */
    protected function _setNumArgs($numArgs)
    {
        $numArgs = $this->_normalizeInt($numArgs);

        if ($numArgs < 0) {
            throw $this->_createOutOfRangeException(
                $this->__('Number of arguments cannot be smaller than zero'), null, null, $numArgs
            );
        }

        $this->numArgs = $numArgs;
    }

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
     * Creates a new Dhii Out Of Range exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|int|float|bool|null $message  The message, if any.
     * @param int|float|string|Stringable|null      $code     The numeric error code, if any.
     * @param RootException|null                    $previous The inner exception, if any.
     * @param mixed|null                            $argument The value that is out of range, if any.
     *
     * @return OutOfRangeException The new exception.
     */
    abstract protected function _createOutOfRangeException(
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
