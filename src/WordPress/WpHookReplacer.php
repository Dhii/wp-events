<?php

namespace Dhii\EventManager\WordPress;

use ArrayAccess;
use Dhii\EventManager\WordPress\Exception\StoppedPropagationExceptionInterface;
use Iterator;
use WP_Hook;

/**
 * A {@see WP_Hook} replacement wrapper class that detects {@see StoppedPropagationExceptionInterface} exceptions to
 * stop any further WordPress filters from invoking.
 *
 * @since [*next-version*]
 */
class WpHookReplacer implements Iterator, ArrayAccess
{
    /**
     * The original WP_Hook instance to proxy to.
     *
     * @since [*next-version*]
     *
     * @var WP_Hook
     */
    protected $wpHook;

    /**
     * Hook callbacks, pointing to the original.
     *
     * @var array
     */
    public $callbacks;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param WP_Hook $wpHook The original WP_Hook instance,
     */
    public function __construct(WP_Hook $wpHook)
    {
        $this->wpHook    = $wpHook;
        $this->callbacks = &$wpHook->callbacks;
    }

    /**
     * Proxies method calls to the original hook instance.
     *
     * @since [*next-version*]
     *
     * @param string $name      The name of the method that was called.
     * @param array  $arguments The arguments given in the method call.
     *
     * @return mixed The return value.
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->wpHook, $name], $arguments);
    }

    /**
     * Calls the callback functions added to a filter hook.
     *
     * This method is called instead of `$this->wpHook->apply_filters()`. This is done due to the fact that calls to
     * existing methods are not passed on to the `__call()` magic method.
     *
     * @param mixed $value The value to filter.
     * @param array $args  Arguments to pass to callbacks.
     *
     * @return mixed The filtered value after all hooked functions are applied to it.
     */
    public function apply_filters($value, $args)
    {
        try {
            $value = $this->wpHook->apply_filters($value, $args);
        } catch (StoppedPropagationExceptionInterface $stoppedPropagationException) {
            $event = $stoppedPropagationException->getEvent();
            $value = $event->getParam(0);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function current()
    {
        return $this->wpHook->current();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function next()
    {
        return $this->wpHook->next();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function key()
    {
        return $this->wpHook->key();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function valid()
    {
        return $this->wpHook->valid();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function rewind()
    {
        return $this->wpHook->rewind();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetExists($offset)
    {
        return $this->wpHook->offsetExists($offset);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetGet($offset)
    {
        return $this->wpHook->offsetGet($offset);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetSet($offset, $value)
    {
        return $this->wpHook->offsetSet($offset, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function offsetUnset($offset)
    {
        return $this->wpHook->offsetUnset($offset);
    }
}
