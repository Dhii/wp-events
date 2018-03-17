<?php

namespace Dhii\EventManager\WordPress;

use Closure;

/**
 * Provides functionality for retrieving, and creating on-request, WordPress event handler wrapper functions.
 *
 * @since [*next-version*]
 */
trait WpHandlerWrapperCacheTrait
{
    /**
     * A cache of WordPress handler wrappers, by hash.
     *
     * @since [*next-version*]
     *
     * @var callable[]
     */
    protected $handlerWrappers = [];

    /**
     * Gets a WordPress event handler wrapper.
     *
     * For the same name/callback pair, returns the same wrapper.
     *
     * @since [*next-version*]
     *
     * @param string   $name     Name of the event.
     * @param callable $callback Handler of the event.
     *
     * @return Closure The wrapper.
     */
    protected function _getWpHandlerWrapper($name, $callback)
    {
        $cbHash = $this->_hashWpHandler($name, $callback);

        // Create if not exists
        if (!isset($this->handlerWrappers[$cbHash])) {
            $this->handlerWrappers[$cbHash] = $this->_createWpHandlerWrapper(
                $name,
                $callback,
                $this->_getThrowOnPropStop()
            );
        }

        return $this->handlerWrappers[$cbHash];
    }

    /**
     * Hashes an event name / event handler pair.
     *
     * @since [*next-version*]
     *
     * @param string   $name    The name of the event.
     * @param callable $handler The handler of the event.
     *
     * @return string The hash of the pair.
     */
    abstract protected function _hashWpHandler($name, $handler);

    /**
     * Retrieves whether or not WP handler wrappers should throw stopped-propagation exceptions.
     *
     * @since [*next-version*]
     *
     * @return bool True if the WP handler wrappers should throw stopped-propagation exceptions, false if not.
     */
    abstract protected function _getThrowOnPropStop();

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
    abstract protected function _createWpHandlerWrapper($name, $callback, $throwOnPropStop = false);
}
