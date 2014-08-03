<?php
namespace ConLayout\ValuePreparer;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface ValuePreparerInterface
{
    /**
     * @param mixed $value value to prepare
     * @return mixed $value prepared value
     */
    public function prepare($value);
}
