<?php

 namespace Kengai\Exception;
 
 use Exception;
 
 class UndefinedNodeException extends Exception {
   
   /**
    * __construct function.
    * 
    * @access public
    * @param mixed $node
    * @return void
    */
   public function __construct($node) {
     
     $this->message = "Undefined node : ".$node;
     
   }
   
 }