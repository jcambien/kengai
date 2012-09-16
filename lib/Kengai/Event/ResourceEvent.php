<?php

 namespace Kengai\Event;
 
 use Symfony\Component\EventDispatcher\Event;
 use Kengai\SourceReader;

 class ResourceEvent extends Event {
   
   protected
    $source;
    
   public function __construct(SourceReader $source) {
   
     $this->source = $source;

   }
   
   public function getSource() {
     
     return $this->source;
     
   }

 }