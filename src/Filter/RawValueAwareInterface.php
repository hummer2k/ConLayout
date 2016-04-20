<?php

namespace ConLayout\Filter;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface RawValueAwareInterface
{
    /**
     * @var mixed $value raw value
     */
    public function setRawValue($value);
}
