<?php

namespace Dhii\WpEvents;

/**
 * Common functionality for WordPress event managers.
 *
 * @since [*next-version*]
 */
abstract class AbstractWpEventManager extends AbstractEventManager
{
    /**
     * Adds a hook to WordPress.
     *
     * @since [*next-version*]
     *
     * @see add_filter()
     *
     * @param string $name
     * @param callable $handler
     * @param int $priority
     * @param int $numArgs
     * @return EventManager This instance.
     */
    protected function _addHook($name, $handler, $priority = self::DEFAULT_PRIORITY, $numArgs = 1)
    {
        \add_filter($name, $handler, $priority, $numArgs);

        return $this;
    }

    /**
     * Causes WordPress to execute all handlers of the specified hook.
     *
     * @since [*next-version*]
     *
     * @param string $name Name of the hook to run handlers for.
     * @param array $args Arguments to pass to the handler.
     *
     * @return mixed The result returned by the last handler.
     */
    protected function _runHandlers($name, array $args = array())
    {
        \array_unshift($args, $name);
        $result = \call_user_func_array('apply_filters', $args);

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _trigger($event, $target = null, $argv = array())
    {
        $event = $this->_normalizeEvent($event, $target, $argv);
        $result = $this->_runHandlers($event->getName(), array($event));

        return $result;
    }
    
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @return AbstractWpEventManager This instance.
     */
    protected function _attach($name, $callback, $priority = self::DEFAULT_PRIORITY, $numArgs = 1)
    {
        $this->_addHook($name, $callback, $priority, $numArgs);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @return AbstractWpEventManager This instance.
     */
    protected function _clearListeners($event)
    {
        $eventObject = $this->_normalizeEvent($event);
        \remove_all_filters($eventObject->getName());

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @return AbstractWpEventManager This instance.
     */
    protected function _detach($event, $callback, $priority = self::DEFAULT_PRIORITY)
    {
        $eventObject = $this->_normalizeEvent($event);
        \remove_filter($eventObject->getName(), $callback);

        return $this;
    }
}
