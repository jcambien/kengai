<?php namespace Kengai\Event;

use Symfony\Component\EventDispatcher\Event;
use Kengai\SourceReader;

class ResourceEvent extends Event
{
  /**
   * Event fired when a resource was found modified
   * while checking registered sources
   */
  const RESOURCE_MODIFIED = "resource.modified";

  /**
   * Event fired when a resource is being refreshed by Kengai manager
   */
  const RESOURCE_REFRESHING = "data.refreshing";

  protected $source;

  public function __construct(SourceReader $source)
  {
    $this->source = $source;
  }

  public function getSource()
  {
    return $this->source;
  }
}