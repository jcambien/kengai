<?php

 namespace Kengai\CacheManager;

 use Kengai\CacheManagerInterface;
 use Kengai\Tree;
 use Exception;

 class APC implements CacheManagerInterface {

   protected
    $dataTreeIdentifier,
    $keysListIdentifier;

   /**
    * __construct function.
    *
    * @access public
    * @param mixed $cacheFile
    * @return void
    */
   public function __construct($dataTreeIdentifier='cfgman_data', $keysListIdentifier='cfgman_keys') {

     if(!extension_loaded('apc'))
       throw new Exception('APC extension is not available');

     $this->dataTreeIdentifier = $dataTreeIdentifier;
     $this->keysListIdentifier = $keysListIdentifier;

   }

   /**
    * restore function.
    *
    * @access public
    * @return void
    */
   public function restore(&$data, &$keys) {

     if(!$this->validate())
       return false;

     $array = apc_fetch(array($this->dataTreeIdentifier, $this->keysListIdentifier));

     return ($data=$array['data'] && $keys=$array['keys']);

   }

   /**
    * write function.
    *
    * @access public
    * @param Tree $tree
    * @return void
    */
   public function write($data, $keys) {

     return apc_store($this->dataTreeIdentifier, $data)
         && apc_store($this->keysListIdentifier, $keys);

   }

   /**
    * clean function.
    *
    * @access public
    * @return void
    */
   public function clean() {

     return apc_delete(array($this->dataTreeIdentifier, $this->keysListIdentifier));

   }

   /**
    * exists function.
    *
    * @access public
    * @return void
    */
   public function exists() {

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
   public function validate() {

     if(!$this->exists())
       return false;

     return ($data = apc_fetch(array($this->dataTreeIdentifier, $this->keysListIdentifier)))
         && (isset($data[$this->dataTreeIdentifier]) && is_array($data[$this->dataTreeIdentifier]))
         && (isset($data[$this->keysListIdentifier]) && is_array($data[$this->keysListIdentifier]));

   }

 }