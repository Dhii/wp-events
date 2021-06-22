<?php

namespace Dhii\EventManager\WordPress\Exception;

use Dhii\Exception\AbstractBaseException;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use Psr\EventManager\EventInterface;

/**
 * An exception thrown when event propagation is stopped in a WordPress environment.
 *
 * @since [*next-version*]
 */
class StoppedPropagationException extends AbstractBaseException implements StoppedPropagationExceptionInterface
{
    /**
     * The event instance, if any.
     *
     * @since [*next-version*]
     *
     * @var EventInterface|null
     */
    protected $event;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|int|float|bool|null $message  The message, if any.
     * @param int|float|string|Stringable|null      $code     The numeric error code, if any.
     * @param RootException|null                    $previous The inner exception, if any.
     * @param EventInterface|null                   $event    The event, if any.
     */
    public function __construct(
        $message = null,
        $code = null,
        RootException $previous = null,
        EventInterface $event = null
    ) {
        $this->_initBaseException($message, $code, $previous);
        $this->event = $event;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getEvent()
    {
        return $this->event;
    }
}
