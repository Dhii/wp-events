<?php

namespace Dhii\WpEvents;

use \Psr\EventManager\EventInterface;
use \Psr\EventManager\EventManagerInterface;

/**
 * Event Manager.
 *
 * @author Miguel Muscat <miguelmuscat93@gmail.com>
 */
class EventManager implements EventManagerInterface
{
    
    /**
     * Constructs a new instance.
     */
    public function __construct()
    {
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function attach($event, $callback, $priority = 10)
    {
        $eventObject = $this->normalizeEvent($event);
        $numArgsToPass = $this->getCallableNumParams($callback);
        \add_filter($eventObject->getName(), $callback, $priority, $numArgsToPass + 1);
    }
    
    /**
     * {@inheritdoc}
     */
    public function detach($event, $callback)
    {
        $eventObject = $this->normalizeEvent($event);
        \remove_filter($eventObject->getName(), $callback);
    }
    
    /**
     * {@inheritdoc}
     */
    public function clearListeners($event)
    {
        $eventObject = $this->normalizeEvent($event);
        \remove_all_filters($eventObject->getName());
    }
    
    /**
     * {@inheritdoc}
     */
    public function trigger($event, $target = null, $argv = array())
    {
        $eventObject = $this->normalizeEvent($event);
        $args = empty($argv)
            ? array(null)
            : $argv;
        array_push($args, $target);
        \apply_filters_ref_array($eventObject->getName(), $args);
    }
    
    /**
     * Normalizes the given event into an Event instance.
     * 
     * @param EventInterfaceevent string or Event instance.
     * @return EventInterface The event instance.
     */
    protected function normalizeEvent($event)
    {
        return ($event instanceof EventInterfacevent)
            ? $event
            : new Event($event);
    }
    
    /**
     * Gets the number of parameters for a callable.
     * 
     * @param callable $callable The callable.
     * @return integer The number of parameters.
     */
    protected function getCallableNumParams($callable)
    {
        return $this->getCallableReflection($callable)->getNumberOfParameter();
    }
    
    /**
     * Gets the reflection instance for a callable.
     * 
     * @param callable $callable The callable.
     * @return ReflectionFunction|ReflectionMethod The reflection instance.
     */
    protected function getCallableReflection($callable)
    {
        return is_array($callable) ? 
            new ReflectionMethod($callable[0], $callable[1]) : 
            new ReflectionFunction($callable);
    }

}
