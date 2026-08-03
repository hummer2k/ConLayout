<?php

declare(strict_types=1);

namespace ConLayout\Handle;

final class Handle implements HandleInterface
{
    /**
     * handle name
     */
    protected string $name;

    /**
     * handle's priority
     */
    protected int $priority;

    public function __construct(string $name, int $priority)
    {
        $this->name     = $name;
        $this->priority = $priority;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        return $this->getName();
    }
}
