<?php

namespace Dhii\WpEvents;

use Psr\EventManager\EventInterface;

/**
 * Event.
 *
 * @author Miguel Muscat <miguelmuscat93@gmail.com>
 */
class Event implements EventInterface
{
    /**
     * The event name.
     * 
     * @var string
     */
    protected $name;

    /**
     * The parameters.
     * 
     * @var array
     */
    protected $params;

    /**
     * The target context object.
     * 
     * @var mixed
     */
    protected $target;

    /**
     * The propagation flag.
     * 
     * @var booleans
     */
    protected $propagation;

    /**
     * Constructs a new instance.
     * 
     * @param string $name        The event name.
     * @param array  $params      The event parameters.
     * @param mixed  $target      The target object. Used for context.
     * @param bool   $propagation True to propagate the event, false to not.
     */
    public function __construct($name, array $params = array(), $target = null, $propagation = true)
    {
        $this->setName($name)
            ->setParams($params)
            ->setTarget($target)
            ->setPropagation($propagation);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getParam($name)
    {
        return isset($this->params[$name])
            ? $this->params[$name]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Gets whether or not the event propagates.
     *
     * @return bool True if the event propagate, false if not.
     */
    public function getPropagation()
    {
        return $this->propagation;
    }

    /**
     * {@inheritdoc}
     */
    public function isPropagationStopped()
    {
        return !$this->getPropagation();
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Sets the event propagation.
     * 
     * @param bool $propagation True to propagate, false to not.
     *
     * @return Event This instance.
     */
    public function setPropagation($propagation)
    {
        $this->propagation = $propagation;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function stopPropagation($flag)
    {
        $this->setPropagation(!$flag);

        return $this;
    }
}
