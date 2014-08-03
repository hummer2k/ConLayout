<?php
namespace ConLayout\Block;

/**
 * @package ConLayout
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
interface CacheableInterface
{
    public function getCacheKey();
    
    public function getCacheLifetime();
}
