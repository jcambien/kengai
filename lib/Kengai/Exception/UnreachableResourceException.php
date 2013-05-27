<?php namespace Kengai\Exception;

use Exception;

class UnreachableResourceException extends Exception
{
  public function __construct($resource)
  {
    $this->message = "Unreachable configuration resource: ".(string)$resource;
  }
}