<?php

namespace Dhii\WpEvents\Exception;

/**
 * Common functionality for exceptions related to events.
 *
 * @since [*next-version*]
 */
abstract class AbstractException extends \RuntimeException
{
    /**
     * The name of a related exception.
     * 
     * @since [*next-version*]
     *
     * @var string
     */
    protected $eventName;

    /**
     * Retrieves the name of a related event.
     *
     * @since [*next-version*]
     *
     * @return string|null The event name, if any.
     */
    protected function _getEventName()
    {
        return $this->eventName;
    }

    /**
     * Assigns the name of a related event.
     *
     * @since [*next-version*]
     *
     * @param string $name The event name.
     *
     * @return AbstractException This instance.
     */
    protected function _setEventName($name)
    {
        $this->eventName = $name;

        return $this;
    }
}
