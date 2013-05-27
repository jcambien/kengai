<?php namespace Kengai\Event;

use Symfony\Component\EventDispatcher\Event;
use Kengai\CacheManagerInterface;

class CacheEvent extends Event
{
  /**
   * Event fired when a cache is being written by Kengai manager
   */
  const CACHE_WRITING = "cache.writing";

  /**
   * Event fired when Kengai manager has correctly wrote cache resource
   */
  const CACHE_WRITTEN = "cache.written";

  protected $source;

  public function __construct(CacheManagerInterface $cache)
  {
    $this->cache = $cache;
  }

  public function getCache()
  {
    return $this->cache;
  }
}