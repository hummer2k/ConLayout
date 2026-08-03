<?php

declare(strict_types=1);

namespace ConLayout\Updater;

use ConLayout\Handle\HandleInterface;
use Laminas\Config\Config;

interface LayoutUpdaterInterface
{
    public const INSTRUCTION_INCLUDE   = 'include';

    public const AREA_GLOBAL   = 'global';
    public const AREA_DEFAULT  = 'frontend';

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
     * @return string[]|HandleInterface[]
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
