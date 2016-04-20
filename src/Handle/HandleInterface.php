<?php

namespace ConLayout\Handle;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface HandleInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return int
     */
    public function getPriority();

    /**
     * @return string
     */
    public function __toString();
}
