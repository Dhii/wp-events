<?php

namespace Dhii\WpEvents\Exception;

/**
 * Something that can represent an exception related to events.
 *
 * @since [*next-version*]
 */
interface ExceptionInterface
{
    /**
     * Retrieves the name of an event related to this exception.
     *
     * @since [*next-version*]
     *
     * @return string The name of a related event, if any.
     */
    public function getEventName();
}
