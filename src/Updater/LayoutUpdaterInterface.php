<?php

declare(strict_types=1);

namespace ConLayout\Updater;

use ConLayout\Handle\HandleInterface;
use Laminas\Config\Config;

interface LayoutUpdaterInterface
{
    public const string INSTRUCTION_INCLUDE   = 'include';

    public const string AREA_GLOBAL   = 'global';
    public const string AREA_DEFAULT  = 'frontend';

    /**
     * retrieve layout structure for current request
     * respectively current handles
     */
    public function getLayoutStructure(): Config;

    /**
     * adds a handle
          */
    public function addHandle(HandleInterface $handle): static;

    /**
     * set/replace handles
          */
    public function setHandles(array $handles): static;

    /**
     * removes a handle by name
          */
    public function removeHandle(string $handleName): static;

    /**
     * retrieve handles
     *
     * @return ($asObject is true ? array<int, HandleInterface> : array<int, string>)
     */
    public function getHandles(bool $asObject = false): array;

    /**
     * set current area
          */
    public function setArea(string $area): static;

    /**
     * retrieve current area
     */
    public function getArea(): string;
}
