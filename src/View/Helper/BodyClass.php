<?php

declare(strict_types=1);

namespace ConLayout\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class BodyClass extends AbstractHelper
{
    /**
     * @var string[]
     */
    protected array $classes = [];

    public function __invoke(?string $class = null): static
    {
        if (null !== $class) {
            $this->addClass($class);
        }
        return $this;
    }

    public function removeClass(string $class): static
    {
        $this->classes = array_diff($this->classes, [$class]);
        return $this;
    }

    public function addClass(string $class): static
    {
        $this->classes[] = $class;
        return $this;
    }

    /**
     *
     * @return string class names space separated
     */
    public function __toString(): string
    {
        return implode(' ', array_unique($this->classes));
    }
}
