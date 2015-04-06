<?php

namespace ConLayout\Handle;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class ControllerAction extends Controller
{
    public function getPriority()
    {
        return parent::getPriority() + 5;
    }
}
