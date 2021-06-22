<?php

namespace Dhii\EventManager\WordPress;

use WP_Hook;

/**
 * Functionality for replacing WordPress hook instances with replacement instances.
 *
 * @since [*next-version*]
 */
trait ReplaceWpHookCapableTrait
{
    /**
     * Replaces a WordPress hook instance.
     *
     * @since [*next-version*]
     *
     * @param string $hook The name of the hook.
     */
    protected function _replaceWpHook($hook)
    {
        global $wp_filter;

        if (!isset($wp_filter[$hook])) {
            return;
        }

        $wp_filter[$hook] = $this->_createWpHookReplacement($wp_filter[$hook]);
    }

    /**
     * Retrieves the replacement instance for the given WordPress hook.
     *
     * @since [*next-version*]
     *
     * @param WP_Hook $wpHook The WordPress hook instance.
     *
     * @return object The replacement instance.
     */
    abstract protected function _createWpHookReplacement($wpHook);
}
