<?php

 namespace Kengai;

 use Kengai\CacheManagerInterface;
 use Kengai\SourceReader;
 use Kengai\Exception;
 use Kengai\Event;
 use Kengai\Events;
 use Symfony\Component\EventDispatcher\EventDispatcher;

 class Manager {

   protected
    $data,
    $cache,
    $sources,
    $checkSources,
    $verbose = true,
    $fetched = false;

   /**
    * __construct function.
    *
    * @access public
    * @param CacheManagerInterface $cache (default: null)
    * @return void
    */
   public function __construct(CacheManagerInterface $cache=null, $checkSources=true) {

     // Event dispatcher
     $this->eventDispatcher = new EventDispatcher();
     
     // Cached parts
     $this->data = array();
     $this->keys = array();

     // Dynamic parts
     $this->sources = array();
     $this->cache = $cache;
     $this->checkSources = ($checkSources === true);

   }

   /**
    * add function.
    *
    * @access public
    * @param DataHandlerInterface $handler
    * @param mixed $namespace (default: null)
    * @return void
    */
   public function add(SourceReader $source) {

     $this->sources[$source->getKey()] = $source;

   }

   /**
    * fetch function.
    *
    * @access public
    * @return void
    */
   public function fetch() {

     $update = false;
     
     if($this->hasCache() && $this->cache->exists($this)) {
       $array = $this->cache->restore($this);
       $this->data = $array['data'];
       $this->keys = $array['keys'];
     } else {
       $update = true;
     }

     if($this->checkSources===true || !$this->hasCache()) {
       foreach($this->sources as $key=>$source) {
         $isCached = false;
         if(isset($this->keys[$key])) {
           $cacheDate = $this->keys[$key];
           $isCached = true;
         }
         if(!$isCached || ($isCached && !$source->isFresh($cacheDate))) {
           $this->eventDispatcher->dispatch(Events::RESOURCE_MODIFIED, new Event\ResourceEvent($source));
           $update = true;
         }
       }
     }

     // Update data if needed
     if($update===true) {
       $this->keys = array();
       $this->data = array();
       foreach($this->sources as $key=>$source) {
         if($source->validate($this)) {
           $this->eventDispatcher->dispatch(Events::RESOURCE_REFRESHING, new Event\ResourceEvent($source));
           $this->update($source->getNamespace(), $source->fetch());
           $this->keys[$key] = time();
         } else {
           throw new Exception\ResourceUnreachableException($source);
         }
       }
       if($this->hasCache()) {
         $this->eventDispatcher->dispatch(Events::CACHE_WRITING, new Event\CacheEvent($this->cache));
         if($this->cache->write($this)) {
           $this->eventDispatcher->dispatch(Events::CACHE_WRITTEN, new Event\CacheEvent($this->cache));
         } else {
           throw new Exception\CacheException('Unable to write cache');
         }
       }
     }

     $this->fetched = true;

   }

   /**
    * get function.
    *
    * @access public
    * @param mixed $node
    * @param bool $create (default: false)
    * @return void
    */
   public function get($node, $default=null) {

     if(!$this->fetched)
       throw new \Exception("Trying to read configuration tree before fetching data");
     
     if(!is_null($default)) {
       try {
         return $this->resolveNamespace($node);
       } catch(UndefinedNodeException $e) {
         return $default;
       }
     } else {
       return $this->resolveNamespace($node);
     }

   }

   /**
    * getRoot function.
    *
    * @access public
    * @return void
    */
   public function getRoot() {

     if(!$this->fetched)
       throw new \Exception("Trying to read configuration tree before fetching data");
     
     return $this->data;

   }
   
   /**
    * getEventDispatcher function.
    *
    * @access public
    * @return object
    */
   public function getEventDispatcher() {
   
     return $this->eventDispatcher;
   
   }
   
   /**
    * getData function.
    * 
    * @access public
    * @return void
    */
   public function getData() {
     
     return $this->data;
     
   }
   
   /**
    * getKeys function.
    * 
    * @access public
    * @return void
    */
   public function getKeys() {
     
     return $this->keys;
     
   }
   
   /**
    * getCacheUniqueKey function.
    * 
    * @access public
    * @return void
    */
   public function getCacheUniqueKey() {
     
     return sha1(implode('-', array_keys($this->sources)));
     
   }
   
   /**
    * addEvent function.
    *
    * @access public
    * @return boolean
    */
   public function addEvent($event, $callback) {
     
     return $this->eventDispatcher->addListener($event, $callback);
     
   }

   /**
    * setSourceChecking function.
    *
    * @access public
    * @param mixed $bool
    * @return void
    */
   public function setSourceChecking($bool) {

     $this->checkSources = ($bool === true);

   }

   /**
    * hasCache function.
    *
    * @access public
    * @return void
    */
   public function hasCache() {

     return is_object($this->cache);

   }

   /**
    * resetCache function.
    * 
    * @access public
    * @return void
    */
   public function resetCache() {

     return ($this->hasCache() ? $this->cache->clean($this) : false);

   }

   /**
    * update function.
    *
    * @access protected
    * @param mixed $node
    * @param mixed $value
    * @return void
    */
   protected function update($node, $value) {

     if(empty($node)) {

       if(!is_array($value))
         throw new \Exception("You must provide an array when inserting data at tree root");

       $this->data = array_merge_recursive($this->data, $value);

     } else {

       $reference = $this->resolveNamespace($node, true, $value);

     }

     //var_dump($this->data);
     return ($this->modified = true);

   }

   /**
    * resolveNamespace function
    *
    * @access protected
    * @param string $node
    * @param bool $create (default: false)
    * @param array $default (default: array())
    * @return void
    */
   protected function resolveNamespace($node, $create=false, $insert=array()) {

     $names = explode('.', $node);
     $current = &$this->data;
     $maxDepth = count($names);
     $currentName = '';
     $i = 0;

     foreach($names as $depth=>$name) {

       $endReached = ($depth === ($maxDepth-1));
       $currentName .= ($depth>0 ? '.' : '').$name;

       if(!isset($current[$name])) {
         if($create===true) {
           $current[$name] = ($endReached) ? $insert : array();
         }
         else if(!$endReached) {
           throw new Exception\UndefinedNodeException('Trying to access an undefined namespace : '.$currentName);
         }
         else {
           return null;
         }
       } else {
         if($endReached) {
           // End of namespace search : he already exists so we merge new values with existing values
           if(is_array($current[$name]) && !empty($insert)) {
             $current[$name] = array_merge_recursive($current[$name], $insert);
           } else if(!empty($insert)) {
             $current[$name] = $insert;
           }
         }
       }

       $current = &$current[$name];

       if(!is_array($current) || $endReached) {
         break;
       }

     }

     return $current;

   }

 }