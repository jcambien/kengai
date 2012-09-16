<?php

 namespace Kengai\Exception;
 
 use Exception;
 use Kengai\CacheManagerInterface;

 class CacheException extends Exception {
   
   public
    $cache;
    
    /**
    * __construct function.
    * 
    * @access public
    * @param mixed $node
    * @return void
    */
   public function __construct(CacheManagerInterface $cache, $message) {
     
     $this->cache = $cache;
     $this->message = $message;
     
   }
   
 }