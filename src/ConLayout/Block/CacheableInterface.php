<?php
namespace ConLayout\Block;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface CacheableInterface
{
    /**
     * retrieve globally unique cache key
     *
     * @return string
     */
    public function getCacheKey();

    /**
     * retrieve cache ttl in seconds
     * if false, block will not be cached
     *
     * @return int|false
     */
    public function getCacheTtl();
}
