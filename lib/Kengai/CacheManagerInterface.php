<?php

 namespace Kengai;

 use Kengai\Manager;

 interface CacheManagerInterface {

   /**
    * restore function.
    *
    * @access public
    * @return void
    */
   public function restore(&$data, &$keys);

   /**
    * write function.
    *
    * @access public
    * @param Tree $tree
    * @return void
    */
   public function write($data, $keys);

   /**
    * exists function.
    *
    * @access public
    * @return void
    */
   public function exists();

   /**
    * validate function.
    *
    * @access public
    * @return void
    */
   public function validate();

 }