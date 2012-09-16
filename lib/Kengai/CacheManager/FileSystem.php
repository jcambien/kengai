<?php

 namespace Kengai\CacheManager;

 use Kengai\CacheManagerInterface;
 use Kengai\Manager;

 class FileSystem implements CacheManagerInterface {

   /**
    * __construct function.
    *
    * @access public
    * @param mixed $cacheFile
    * @return void
    */
   public function __construct($cacheFile) {

     $this->cacheFile = $cacheFile;

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

     $array = include($this->cacheFile);

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

     return (file_put_contents($this->cacheFile, "<?php return ".var_export(array('data'=>$data, 'keys'=>$keys), true)."; ?>") === false) ? false : true;

   }

   /**
    * exists function.
    *
    * @access public
    * @return void
    */
   public function exists() {

     return is_file($this->cacheFile);

   }

   /**
    * clean function.
    *
    * @access public
    * @return void
    */
   public function clean() {

     return $this->exists() ? @unlink($this->cacheFile) : false;

   }

   /**
    * validate function.
    *
    * @access public
    * @return void
    */
   public function validate() {

     return (!$this->exists() || filesize($this->cacheFile)==0) ? false : true;

   }

 }