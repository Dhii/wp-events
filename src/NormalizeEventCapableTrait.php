<?php

namespace Dhii\EventManager;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use Psr\EventManager\EventInterface;
use Exception as RootException;

/**
 * Functionality for normalizing an event.
 *
 * @since [*next-version*]
 */
trait NormalizeEventCapableTrait
{
    /**
     * Normalizes the given event into an Event instance.
     *
     * @since [*next-version*]
     *
     * @param EventInterface|string|Stringable $event  Event instance or an event name string.
     * @param array                            $params The event parameters; will be added to existing parameters if
     *                                                 the $event argument is an {@see EventInterface} instance.
     * @param object                           $target The target of the event.
     *
     * @return EventInterface The event instance.
     */
    protected function _normalizeEvent($event, $params = [], $target = null)
    {
        if (is_string($event) || $event instanceof Stringable) {
            return $this->_createEvent($this->_normalizeString($event), $params, $target);
        }

        if (!($event instanceof EventInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a string, stringable object or event instance'),
                null,
                null,
                $event
            );
        }

        if ($target !== null) {
            $event->setTarget($target);
        }

        if (count($params) > 0) {
            $event->setParams($params + $event->getParams());
        }

        return $event;
    }

    /**
     * Creates a new event instance.
     *
     * @since [*next-version*]
     *
     * @param string $name        The event name.
     * @param array  $params      The event parameters.
     * @param mixed  $target      The target object. Used for context.
     * @param bool   $propagation True to propagate the event, false to not.
     *
     * @return EventInterface The new event.
     */
    abstract protected function _createEvent($name, $params = [], $target = null, $propagation = true);

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
