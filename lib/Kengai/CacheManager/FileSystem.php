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
   public function __construct($cacheDir) {

     $this->cacheDir = $cacheDir;

   }

   /**
    * restore function.
    *
    * @access public
    * @return void
    */
   public function restore(Manager $manager) {

     if(!$this->validate($manager))
       throw new \Exception("Unable to restore configuration cache file : validation failed");

     $array = include($this->getCacheFilename($manager));
     
     return array('data'=>$array['data'], 'keys'=>$array['keys']);

   }

   /**
    * write function.
    *
    * @access public
    * @param Tree $tree
    * @return void
    */
   public function write(Manager $manager) {

     $cacheFile = $this->getCacheFilename($manager);

     // Write source to cache file
     $write = file_put_contents($cacheFile, "<?php return ".var_export(array('data'=>$manager->getData(), 'keys'=>$manager->getKeys()), true).";");

     // Minifying
     file_put_contents($cacheFile, php_strip_whitespace($cacheFile));
     
     return ($write !== false);

   }

   /**
    * exists function.
    *
    * @access public
    * @return void
    */
   public function exists(Manager $manager) {

     return is_file($this->getCacheFilename($manager));

   }

   /**
    * clean function.
    *
    * @access public
    * @return void
    */
   public function clean(Manager $manager) {

     return $this->exists($manager) ? (@unlink($this->getCacheFilename($manager)) === true) : false;

   }

   /**
    * validate function.
    *
    * @access public
    * @return void
    */
   public function validate(Manager $manager) {

     return ($this->exists($manager) && filesize($this->getCacheFilename($manager))>0) ? true : false;

   }

   /**
    * getCacheFilename function.
    * 
    * @access protected
    * @param mixed $manager
    * @return void
    */
   protected function getCacheFilename($manager) {
     
     return $this->cacheDir.'/'.$manager->getCacheUniqueKey().".php";
     
   }

 }