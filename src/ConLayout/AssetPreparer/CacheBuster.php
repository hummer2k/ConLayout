<?php

namespace ConLayout\AssetPreparer;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class CacheBuster implements
    AssetPreparerInterface,
    OriginalValueAwareInterface
{
    /**
     *
     * @var string
     */
    protected $internalBasePath;

    /**
     *
     * @var string
     */
    protected $originalValue;

    /**
     *
     * @param string $internalBasePath
     */
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
            return $value . '?v=' . substr(md5_file($file), 0, 8);
        }
        return $value;
    }

    /**
     *
     * @param mixed $originalValue
     */
    public function setOriginalValue($originalValue)
    {
        $this->originalValue = $originalValue;
    }

    /**
     *
     * @param string $value
     * @return string
     */
    protected function getFilePath($value)
    {
        if (null !== $this->originalValue) {
            $value = $this->originalValue;
        }
        return $this->internalBasePath . '/' . ltrim($value, '/');
    }
}
