<?php namespace Kengai;

use Kengai\Manager;

/**
 * CacheManagerInterface interface.
 */
interface CacheManagerInterface
{
  /**
   * restore function.
   *
   * @access public
   * @return void
   */
  public function restore(Manager $manager);

  /**
   * write function.
   *
   * @access public
   * @param Tree $tree
   * @return void
   */
  public function write(Manager $manager);

  /**
   * clean function.
   *
   * @access public
   * @param Tree $tree
   * @return void
   */
  public function clean(Manager $manager);

  /**
   * exists function.
   *
   * @access public
   * @return void
   */
  public function exists(Manager $manager);

  /**
   * validate function.
   *
   * @access public
   * @return void
   */
  public function validate(Manager $manager);
}