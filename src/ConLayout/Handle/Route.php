<?php

namespace ConLayout\Handle;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Route implements HandleInterface
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPriority()
    {
        return substr_count($this->name, '/');
    }

    public function __toString()
    {
        return $this->getName();
    }
}
