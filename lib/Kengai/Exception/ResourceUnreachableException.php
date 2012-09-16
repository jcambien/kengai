<?php

 namespace Kengai\Exception;
 
 use Exception;
 
 class ResourceUnreachableException extends Exception { 
   
   /**
    * __construct function.
    * 
    * @access public
    * @param mixed $node
    * @return void
    */
   public function __construct($loader) {
     
     $this->message = "Configuration resource was not reachable : ".$loader->getResource();
     
   }
   
 }