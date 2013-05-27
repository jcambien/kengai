<?php namespace Kengai\SourceReader;

use Kengai\SourceReader;

class ArraySourceReader extends SourceReader
{
  /**
   * fetch function.
   *
   * @access public
   * @return void
   */
  public function fetch()
  {
    return (array) $this->resource;
  }

  /**
   * validate function.
   *
   * @access public
   * @return boolean
   */
  public function validate()
  {
    return is_array($this->resource);
  }

  /**
   * isFresh function.
   *
   * @access public
   * @param mixed $cacheDate (default: null)
   * @return void
   */
  public function isFresh($cacheDate=null)
  {
    return true;
  }
}