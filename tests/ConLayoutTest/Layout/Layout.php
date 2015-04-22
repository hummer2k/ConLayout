<?php

namespace ConLayoutTest\Layout;

use ConLayout\Layout\Layout as DefaultLayout;

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
}