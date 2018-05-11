<?php

namespace Dhii\EventManager\WordPress\Exception;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use Psr\EventManager\EventInterface;

/**
 * Functionality for creating exceptions for stopped event propagation.
 *
 * @since [*next-version*]
 */
trait CreateStoppedPropagationExceptionCapableTrait
{
    /**
     * Creates a new exception for stopped event propagation.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The previous exception for chaining, if any.
     * @param EventInterface|null    $event    The event that stopped, if any.
     *
     * @return StoppedPropagationExceptionInterface The created exception instance.
     */
    protected function _createStoppedPropagationException(
        $message = null,
        $code = null,
        $previous = null,
        $event = null
    ) {
        return new StoppedPropagationException($message, $code, $previous, $event);
    }
}
