<?php
namespace ConLayout\AssetPreparer;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface AssetPreparerInterface
{
    /**
     * @param mixed $value value to prepare
     * @return mixed $value prepared value
     */
    public function prepare($value);
}
