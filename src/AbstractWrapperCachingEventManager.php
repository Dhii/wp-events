<?php

namespace Dhii\WpEvents;

/**
 * Common functionality for normalized WordPress event managers that cache
 * handler wrappers.
 *
 * @since [*next-version*]
 */
abstract class AbstractWrapperCachingEventManager extends AbstractNormalizedEventManager
{
    /**
     * A cache of callback wrappers, by hash.
     *
     * @since [*next-version*]
     *
     * @var callable[]
     */
    protected $callbackWrappers = array();

    /**
     * {@inheritdoc}
     *
     * For the same name/callback pair, returns the same wrapper.
     *
     * @since [*next-version*]
     */
    protected function _getHandlerWrapper($name, $callback)
    {
        $cbHash = $this->_hashEventHandler($name, $callback);
        if (!isset($this->callbackWrappers[$cbHash])) {
            $this->callbackWrappers[$cbHash] = $this->_createHandlerWrapper($name, $callback);
        }

        return $this->callbackWrappers[$cbHash];
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
    protected function _hashEventHandler($name, $handler)
    {
        $handlerHash = $this->_hashCallable($handler);
        $pair        = $name . '|' . $handlerHash;

        return $this->_hashScalar($pair);
    }

    /**
     * Computes a hash of a given callable.
     *
     * @since [*next-version*]
     *
     * @param callable $callable The callable to hash.
     *
     * @throws \InvalidArgumentException If not a valid callable.
     *
     * @return string A hash of the callable.
     */
    protected function _hashCallable($callable)
    {
        if (\is_object($callable)) {
            return $this->_hashObject($callable);
        }

        if (\is_array($callable)) {
            return $this->_hashArray($callable);
        }

        throw new \InvalidArgumentException('Could not hash: not a valid callback');
    }

    /**
     * Computes a hash of the array.
     *
     * Accounts for nested arrays.
     *
     * @since [*next-version*]
     *
     * @param array $array The array to hash.
     *
     * @return string A hash of the array.
     */
    protected function _hashArray(array $array)
    {
        $itemHashes = array();
        foreach ($array as $_idx => $_item) {
            if (\is_array($_item)) {
                $itemHashes[$_idx] = $this->_hashArray($_item);
            } elseif (\is_object($_item)) {
                $itemHashes[$_idx] = $this->_hashObject($_item);
            } elseif (\is_resource($_item)) {
                $itemHashes[$_idx] = (string) $_item;
            } else {
                $itemHashes[$_idx] = $_item;
            }
        }

        $itemHashes = \serialize($itemHashes);

        return $this->_hashScalar($itemHashes);
    }

    /**
     * Computes a hash of an object.
     *
     * The same object will always have the same hash.
     * Different identical objects will produce different results.
     *
     * @since [*next-version*]
     *
     * @param object $object The object to hash.
     *
     * @return string A hash of the object.
     */
    protected function _hashObject($object)
    {
        return \spl_object_hash($object);
    }

    /**
     * Computes a hash of a scalar value.
     *
     * @since [*next-version*]
     *
     * @param string|int|float|bool $value The value to hash.
     *
     * @return string A hash of the scalar value.
     */
    protected function _hashScalar($value)
    {
        return \sha1($value);
    }
}
