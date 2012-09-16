<?php

 namespace Kengai;

 abstract class SourceReader {
   
   protected
    $resource,
    $namespace;

   /**
    * __construct function.
    *
    * @access public
    * @param mixed $resource
    * @return void
    */
   public function __construct($resource, $namespace=null) {

     $this->resource = $resource;
     $this->namespace = (is_string($namespace)) ? trim($namespace) : '';

   }

   /**
    * fetch function.
    *
    * @access public
    * @return void
    */
   abstract public function fetch();
   
   /**
    * validate resource availability
    *
    * @access public
    * @return boolean
    */
   abstract public function validate();
   
   /**
    * isFresh function.
    *
    * @access public
    * @param mixed $cacheDate (default: null)
    * @return void
    */
   abstract public function isFresh($cacheDate=null);

   /**
    * getKey function.
    *
    * @access public
    * @param mixed $namespace
    * @return void
    */
   public function getKey() {

     return md5(get_called_class().'@'.$this->namespace.'@'.$this->resource);

   }

   /**
    * getNamespace function.
    *
    * @access public
    * @return void
    */
   public function getNamespace() {

     return $this->namespace;

   }
   
   /**
    * getResource function.
    *
    * @access public
    * @return void
    */
   public function getResource() {

     return $this->resource;

   }

 }