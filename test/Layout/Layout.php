<?php

namespace ConLayoutTest\Layout;

use ConLayout\Layout\Layout as DefaultLayout;
use Laminas\View\Model\ModelInterface;

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Layout extends DefaultLayout
{
    public function isLoaded()
    {
        return $this->isLoaded;
    }

    public function getCaptureTo(ModelInterface $block)
    {
        return parent::getCaptureTo($block);
    }
}
