<?php
namespace ConLayout\Service\Config;
/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface SorterInterface
{
    /**
     * 
     * @param array $data
     */
    public function sort(array &$data);
}
