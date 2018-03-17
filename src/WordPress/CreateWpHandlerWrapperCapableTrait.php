<?php

namespace Dhii\EventManager\WordPress;

use Closure;
use Dhii\EventManager\WordPress\Exception\StoppedPropagationExceptionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use Psr\EventManager\EventInterface;
use ReflectionException;
use ReflectionMethod;

/**
 * Functionality for creating WordPress handler wrappers.
 *
 * The wrappers created by this trait are compatible with both event managers and WordPress. By detecting whether the
 * handler was invoked with a single {@see EventInterface} instance or not, the handler can always return the
 * appropriate return value for the hook.
 *
 * @since [*next-version*]
 */
trait CreateWpHandlerWrapperCapableTrait
{
    /**
     * Creates a wrapper for a WordPress event handler, containing and replacing it.
     *
     * If the wrapper receives an {@see EventInterface}, it will use that and assume it was triggered and normalized by
     * a PSR-14 event manager.
     *
     * Otherwise it will assume that regular arguments have been passed (as would be done through the WordPress
     * functional API for hooks) and an event object will be created for those arguments.
     *
     * This consequently allows handlers to stop event propagation in WordPress. Profit!
     *
     * @since [*next-version*]
     *
     * @param string   $name            The name of the event, for which a wrapper is created.
     * @param callable $callback        The callback to wrap.
     * @param bool     $throwOnPropStop If true, a {@see StoppedPropagationExceptionInterface} exception
     *                                  is thrown when propagation is stopped.
     *
     * @return Closure The wrapper.
     */
    protected function _createWpHandlerWrapper($name, $callback, $throwOnPropStop = false)
    {
        $eventCache = $this;

        /*
         * $name - The name of the event
         * $callback - The actual handler callback
         * $eventCache - The callback for retrieving events from cache
         * $throwOnPropStop - If true, an exception is thrown when propagation is stopped.
         */

        return function() use ($name, &$callback, $eventCache, $throwOnPropStop) {
            $fnArgs = func_get_args();
            // Detect whether the first argument given to the handler is an EventInterface
            $firstArg = count($fnArgs) === 1
                ? $fnArgs[0]
                : null;
            $isEvent = $firstArg instanceof EventInterface;

            // Use argument-given event instance or get event from cache
            /* @var $event \Psr\EventManager\EventInterface */
            $event = $isEvent
                ? $firstArg
                : $eventCache->_getCachedEvent($name, $fnArgs);

            // Call original handler if propagation is not stopped
            if (!$event->isPropagationStopped()) {
                \call_user_func_array($callback, [$event]);
            }

            if ($event->isPropagationStopped() && $throwOnPropStop) {
                throw $this->_createStoppedPropagationException(
                    $this->__('Propagation has been stopped.'),
                    0,
                    null,
                    $event
                );
            }

            // Return event, or first event param if argument was not an event
            if ($isEvent) {
                return $event;
            }

            $params = $event->getParams();

            return reset($params);
        };
    }

    /**
     * Gets the cached event instance for the given name, creating it if needed.
     *
     * @since [*next-version*]
     *
     * @param string $name The name of the event.
     * @param array  $args Optional array of event arguments.
     *
     * @return EventInterface The event instance.
     */
    abstract protected function _getCachedEvent($name, array $args = []);

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
    abstract protected function _createStoppedPropagationException(
        $message = null,
        $code = null,
        $previous = null,
        $event = null
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
