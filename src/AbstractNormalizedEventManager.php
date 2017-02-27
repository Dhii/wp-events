<?php

namespace Dhii\WpEvents;

use Psr\EventManager\EventInterface;

/**
 * Common functionality for WordPress event managers that use callback wrappers
 * to normalize event handler parameters.
 *
 * @since [*next-version*]
 */
abstract class AbstractNormalizedEventManager extends AbstractWpEventManager
{
    /**
     * Gets an event callback wrapper.
     *
     * @since [*next-version*]
     *
     * @see _createCallbackWrapper()
     *
     * @param string $name Name of the event.
     * @param callable $callback Handler of the event.
     * @return \Closure The wrapper.
     */
    protected function _getHandlerWrapper($name, $callback)
    {
        return $this->_createHandlerWrapper($name, $callback);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _attach($name, $callback, $priority = self::DEFAULT_PRIORITY)
    {
        $handler = $this->_getHandlerWrapper($name, $callback);
        $this->_addHook($name, $handler, $priority, 1);

        return $this;
    }

    /**
     * Creates a wrapper for an event handler, containing and replacing it.
     *
     * If the wrapper receives an {@see EventInterface}, it will use that and assume it was
     * triggered or normalized earlier by the event manager.
     * Otherwise, it will assume that regular parameters are passed, such as
     * what WordPress does, and will create an event object from that.
     * Also allows events to stop propagation.
     *
     * @since [*next-version*]
     *
     * @param string $name The name of the event, for which a wrapper is created.
     * @param callable $callback The callback to wrap.
     * @return \Closure The wrapper.
     */
    protected function _createHandlerWrapper($name, $callback)
    {
        $me = $this;
        return function() use ($name, &$callback, &$me) {
            $args = \func_get_args();
            $event = \count($args) && $args[0] instanceof EventInterface
                    ? $args[0]
                    : $me->createEvent($name, $args);
            /* @var $event \Dhii\WpEvents\Event */
            if (!$event->isPropagationStopped()) {
                \call_user_func_array($callback, array($event));
            }

            return $event;
        };
    }
}
