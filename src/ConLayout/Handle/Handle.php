<?php

namespace ConLayout\Handle;

use ConLayout\Exception\InvalidHandleNameException;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
final class Handle implements HandleInterface
{
    /**
     * handle name
     *
     * @var string
     */
    protected $name;

    /**
     * handle's priority
     *
     * @var int
     */
    protected $priority;

    /**
     *
     * @param string $name
     * @param int $priority
     */
    public function __construct($name, $priority)
    {
        $this->assertHandleName($name);
        $this->name     = $name;
        $this->priority = $priority;
    }

    /**
     *
     * @param string $name
     * @throws InvalidHandleNameException
     */
    private function assertHandleName($name)
    {
        if (!preg_match('/[^a-z0-9-]+/', $name)) {
            throw new InvalidHandleNameException(
                sprintf('Invalid handle name "%"', $name)
            );
        }
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
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getName();
    }
}
