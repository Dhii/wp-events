<?php

namespace Dhii\WpEvents\Exception;

/**
 * An exception related to events.
 *
 * @since [*next-version*]
 */
class Exception extends AbstractException implements ExceptionInterface
{
    /**
     * @since [*next-version*]
     */
    public function __construct($message = '', $code = 0, \Exception $previous = null, $eventName = null)
    {
        parent::__construct($message, $code, $previous);

        if (!is_null($eventName)) {
            $this->_setEventName($eventName);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getEventName()
    {
        return $this->_getEventName();
    }
}
