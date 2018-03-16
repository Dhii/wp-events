<?php

namespace Dhii\EventManager\WordPress\Exception;

use Dhii\Exception\ThrowableInterface;
use Psr\EventManager\EventInterface;

/**
 * An exception thrown to indicate that propagation has been stopped.
 *
 * @since [*next-version*]
 */
interface StoppedPropagationExceptionInterface extends ThrowableInterface
{
    /**
     * Retrieves the event for which propagation was stopped.
     *
     * @since [*next-version*]
     *
     * @return EventInterface|null The event instance, if any.
     */
    public function getEvent();
}
