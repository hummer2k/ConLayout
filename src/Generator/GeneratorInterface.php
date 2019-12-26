<?php

/**
 * @package
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */

namespace ConLayout\Generator;

use Zend\Config\Config;

interface GeneratorInterface
{
    /**
     * @param Config $layoutStructure
     * @return mixed
     */
    public function generate(Config $layoutStructure);
}
