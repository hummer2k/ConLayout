<?php

declare(strict_types=1);

namespace ConLayout\Handle;

final readonly class Handle implements HandleInterface
{
    public function __construct(
        private string $name,
        private int $priority,
    ) {}

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
