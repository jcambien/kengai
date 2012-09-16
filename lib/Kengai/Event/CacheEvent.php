<?php

 namespace Kengai\Event;
 
 use Symfony\Component\EventDispatcher\Event;
 use Kengai\CacheManagerInterface;

 class CacheEvent extends Event {
   
   protected
    $source;
    
   public function __construct(CacheManagerInterface $cache) {
   
     $this->cache = $cache;

   }
   
   public function getCache() {
     
     return $this->cache;
     
   }

 }