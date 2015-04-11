<?php

namespace ConLayout\Updater;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface LayoutUpdateProviderInterface
{
    /**
     * retrieve layout structure/instructions as array
     *
     * @return array
     */
    public function getLayoutUpdate();
}
