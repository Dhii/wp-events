<?php

namespace Dhii\EventManager\WordPress;

use Dhii\EventManager\CreateEventCapableTrait;
use Dhii\EventManager\EventCacheTrait;
use Dhii\EventManager\HashCallableCapableTrait;
use Dhii\EventManager\NormalizeEventCapableTrait;
use Dhii\EventManager\WordPress\Exception\CreateStoppedPropagationExceptionCapableTrait;
use Dhii\Exception\CreateInternalExceptionCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Exception\InternalException;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Exception;
use Psr\EventManager\EventInterface;
use Psr\EventManager\EventManagerInterface;
use ReflectionMethod;

/**
 * A PSR-14 compliant WordPress event manager implementation.
 *
 * This implementation treats all events as filters. This is because in WordPress, all hooks are indeed filters -
 * {@link https://goo.gl/9kPjcE actions are simply filters} that ignore the return value. As such, functions such as
 * {@link https://goo.gl/Ro65KV `doing_action()`} might not give a reliable result. It is recommended to use the
 * filter counterparts of such functions.
 *
 * Handlers attached via {@see attach()} will always receive a single argument - an {@see EventInterface} instance.
 *
 * For filters triggered by {@link https://goo.gl/vB77MC `apply_filters()`} and other similar WordPress functions,
 * the first event parameter is used as the filter result.
 *
 * For filters triggered by the event manager, the result is the event instance. Data can then be read from the event
 * via {@see EventInterface::getParam()} and {@see EventInterface::getParams()}.
 *
 * Propagation can be stopped for all handlers attached via {@see attach()}. However, handlers attached through the
 * WordPress functional API will not respect the request to stop propagation. This implementation _can_ wrap all
 * attached handlers and add this functionality - but it comes at a performance cost. The option can be enabled during
 * construction by passing an argument of `true` for the `$wrapAllHandlers` parameter.
 *
 * @since [*next-version*]
 */
class WpEventManager implements EventManagerInterface
{
    /*
     * Provides functionality for adding handlers to a WordPress hook.
     *
     * @since [*next-version*]
     */
    use AddWpHookCapableTrait;

    /*
     * Provides functionality for removing handlers from a WordPress hook.
     *
     * @since [*next-version*]
     */
    use RemoveWpHookCapableTrait;

    /*
     * Provides functionality for clearing all handlers from a WordPress hook.
     *
     * @since [*next-version*]
     */
    use ClearWpHookCapableTrait;

    /*
     * Provides functionality for triggering WordPress hooks.
     *
     * @since [*next-version*]
     */
    use RunWpHookCapableTrait;

    /*
     * Provides functionality for wrapping WordPress handlers for PSR-14 event manager functionality.
     *
     * @since [*next-version*]
     */
    use WpHandlerWrapperCacheTrait;

    /*
     * Provides functionality for creating wrapper handlers for WordPress event handlers.
     *
     * @since [*next-version*]
     */
    use CreateWpHandlerWrapperCapableTrait;

    /*
     * Provides callable hashing functionality.
     *
     * @since [*next-version*]
     */
    use HashCallableCapableTrait;

    /*
     * Provides event instance caching functionality.
     *
     * @since [*next-version*]
     */
    use EventCacheTrait;

    /*
     * Provides functionality for enabling the auto-clearing of previously cached events.
     *
     * @since [*next-version*]
     */
    use AttachMethodHandlerCapableTrait;

    /*
     * Provides functionality for replacing WordPress hook instances.
     *
     * @since [*next-version*]
     */
    use ReplaceWpHookCapableTrait;

    /*
     * Provides event normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeEventCapableTrait;

    /*
     * Provides integer normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeIntCapableTrait;

    /*
     * Provides string normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /*
     * Provides functionality for creating event instances.
     *
     * @since [*next-version*]
     */
    use CreateEventCapableTrait;

    /*
     * Provides functionality for creating invalid argument exception instances.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * Provides functionality for creating out-of-range exception instances.
     *
     * @since [*next-version*]
     */
    use CreateOutOfRangeExceptionCapableTrait;

    /*
     * Provides functionality for creating internal exception instances.
     *
     * @since [*next-version*]
     */
    use CreateInternalExceptionCapableTrait;

    /*
     * Provides functionality for creating stopped propagation exception instances.
     *
     * @since [*next-version*]
     */
    use CreateStoppedPropagationExceptionCapableTrait;

    /*
     * Provides string translating functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * The default priority for event handlers.
     *
     * @since [*next-version*]
     */
    const DEFAULT_PRIORITY = 10;

    /**
     * The hook name to which to attach the CCE handler.
     *
     * @since [*next-version*]
     */
    const WP_ALL_HOOK = 'all';

    /**
     * If true, hook instances are replaced with wrapping instances that detect stopped propagation.
     *
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $replaceWpHooks;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param bool $enablePropagation If true, {@see \WP_HOOK} instances are replaced with wrapping instances that can
     *                                detect thrown {@see Exception\StoppedPropagationExceptionInterface} exceptions and
     *                                stop propagation. If false, stopping propagation will not affect handlers
     *                                registered through WordPress functions.
     *
     * @throws InternalException If a problem occurred while registering the CCE handler.
     * @throws InternalException If a problem occurred while registering the WP_Hook replacer handler.
     */
    public function __construct($enablePropagation = false)
    {
        try {
            $this->_attachMethodHandler(static::WP_ALL_HOOK, '_removeCachedEvent', 0);
        } catch (Exception $e) {
            throw $this->_createInternalException(
                $this->__('Failed to register the clear-cache-event handler to the `all` hook'),
                null,
                $e
            );
        }

        try {
            if ($this->replaceWpHooks = $enablePropagation) {
                $this->_attachMethodHandler(static::WP_ALL_HOOK, '_replaceWpHook', 1);
            }
        } catch (Exception $e) {
            throw $this->_createInternalException(
                $this->__('Failed to register the `WP_Hook` replacer handler to the `all` hook'),
                null,
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function attach($event, $callback, $priority = null)
    {
        $event   = $this->_normalizeEvent($event)->getName();
        $handler = $this->_getWpHandlerWrapper($event, $callback);

        $this->_addWpHook($event, $handler, $priority, 1);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function detach($event, $callback, $priority = null)
    {
        $event   = $this->_normalizeEvent($event)->getName();
        $handler = $this->_getWpHandlerWrapper($event, $callback);

        $this->_removeWpHook($event, $handler, $priority);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function clearListeners($event)
    {
        $this->_clearWpHooks($this->_normalizeEvent($event)->getName());
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function trigger($event, $target = null, $argv = [])
    {
        $event  = $this->_normalizeEvent($event, $argv, $target);
        $result = $this->_runWpHook($event->getName(), [$event]);

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getWpHookDefaultPriority()
    {
        return static::DEFAULT_PRIORITY;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _hashWpHandler($name, $handler)
    {
        $handlerHash = $this->_hashCallable($handler);
        $pair        = sprintf('%1$s|%2$s', $name, $handlerHash);

        return sha1($pair);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getThrowOnPropStop()
    {
        return $this->replaceWpHooks;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createWpHookReplacement($wpHook)
    {
        return ($wpHook instanceof WpHookReplacer)
            ? $wpHook
            : new WpHookReplacer($wpHook);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createReflectionMethod($className, $methodName)
    {
        return new ReflectionMethod($className, $methodName);
    }
}
