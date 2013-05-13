<?php namespace Kengai\CacheManager;

use Kengai\CacheManagerInterface;
use Kengai\Tree;
use Kengai\Manager;
use Exception;

class APC implements CacheManagerInterface
{
  protected $dataTreeIdentifier;
  protected $keysListIdentifier;

  /**
   * __construct function.
   *
   * @access public
   * @param mixed $cacheFile
   * @return void
   */
  public function __construct($dataTreeIdentifier='kengai_apc_data', $keysListIdentifier='kengai_apc_keys')
  {
    if (!extension_loaded('apc')) {
      throw new Exception('APC extension is not available');
    }

    $this->dataTreeIdentifier = $dataTreeIdentifier;
    $this->keysListIdentifier = $keysListIdentifier;
  }

  /**
   * restore function.
   *
   * @access public
   * @return void
   */
  public function restore(Manager $manager)
  {
    if (!$this->validate($manager)) {
      return false;
    }

    $array = apc_fetch(array($this->dataTreeIdentifier, $this->keysListIdentifier));

    return ($data=$manager->getData() && $keys=$manager->getKeys());
  }

  /**
   * write function.
   *
   * @access public
   * @param Tree $tree
   * @return void
   */
  public function write(Manager $manager)
  {
    return apc_store($this->dataTreeIdentifier, $manager->getData())
        && apc_store($this->keysListIdentifier, $manager->getKeys());
  }

  /**
   * exists function.
   *
   * @access public
   * @return void
   */
  public function exists(Manager $manager)
  {
    $exists = apc_exists(array($this->dataTreeIdentifier, $this->keysListIdentifier));

    return (
         isset($exists[$this->dataTreeIdentifier])
      && isset($exists[$this->keysListIdentifier])
      && $exists[$this->dataTreeIdentifier]===true
      && $exists[$this->keysListIdentifier]===true
    );
  }

  /**
   * validate function.
   *
   * @access public
   * @return void
   */
  public function validate(Manager $manager)
  {
    if (!$this->exists($manager)) {
      return false;
    }

    return ($data = apc_fetch(array($this->dataTreeIdentifier, $this->keysListIdentifier)))
        && (isset($data[$this->dataTreeIdentifier]) && is_array($data[$this->dataTreeIdentifier]))
        && (isset($data[$this->keysListIdentifier]) && is_array($data[$this->keysListIdentifier]));
   }

  /**
   * clean function.
   *
   * @access public
   * @return void
   */
  public function clean(Manager $manager)
  {
    return apc_delete(array($this->dataTreeIdentifier, $this->keysListIdentifier));
  }
}