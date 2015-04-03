<?php

namespace ConLayout\AssetPreparer;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class CacheBuster implements AssetPreparerInterface
{
    protected $internalBasePath = './public';

    public function __construct($internalBasePath = './public')
    {
        $this->internalBasePath = $internalBasePath;
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public function prepare($value)
    {
        $file = $this->getFilePath($value);
        if (is_file($file) && is_readable($file)) {
            return $value . '?' . md5_file($file);
        }
        return $value;
    }

    protected function getFilePath($value)
    {
        return $this->internalBasePath . '/' . ltrim($value, '/');
    }
}