<?php namespace Kengai\SourceReader;

use Kengai\SourceReader;

class JsonSourceReader extends SourceReader
{
  /**
   * fetch function.
   *
   * @access public
   * @return void
   */
  public function fetch()
  {
    return ($data=json_decode($this->resource)) ? $data : false;
  }

  /**
   * validate function.
   *
   * @access public
   * @return boolean
   */
  public function validate()
  {
    return (is_file($this->resource) && is_readable($this->resource));
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
    return (!is_null($cacheDate) && ($sourceDate = @filemtime($this->resource)) && $cacheDate>=$sourceDate);
  }
}