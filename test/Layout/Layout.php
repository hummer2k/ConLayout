<?php

declare(strict_types=1);

namespace ConLayoutTest\Layout;

use ConLayout\Layout\Layout as DefaultLayout;
use Laminas\View\Model\ModelInterface;

class Layout extends DefaultLayout
{
    public function isLoaded(): bool
    {
        return $this->isLoaded;
    }

    public function getCaptureTo(ModelInterface $block): array
    {
        return parent::getCaptureTo($block);
    }
}
