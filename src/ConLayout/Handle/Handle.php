<?php

namespace ConLayout\Handle;

/**
 * @package 
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Handle implements HandleInterface
{
    protected $name;
    
    protected $priority;
    
    public function __construct($name, $priority)
    {
        $this->name     = $name;
        $this->priority = $priority;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
