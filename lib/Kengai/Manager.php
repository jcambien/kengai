<?php namespace Kengai;

use Kengai\CacheManagerInterface;
use Kengai\SourceReader;
use Kengai\Exception;
use Kengai\Event;
use Kengai\Events;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Manager
{
  protected $data = array();
  protected $keys = array();
  protected $sources = array();
  protected $cache;
  protected $checkSources = true;
  protected $loaded = false;

  /**
   * __construct function.
   *
   * @access public
   * @param CacheManagerInterface $cache (default: null)
   * @return void
   */
  public function __construct(CacheManagerInterface $cache = null, $checkSources = true)
  {
    $this->eventDispatcher = new EventDispatcher();
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
  public function add(SourceReader $source)
  {
    $this->sources[$source->getKey()] = $source;
  }

  public function set(array $data)
  {
    $this->add(new SourceReader\ArraySourceReader($data));
  }

  /**
   * fetch function.
   *
   * @access public
   * @return void
   */
  public function fetch()
  {
    $update = false;

    if ($this->hasCache() && $this->cache->exists($this)) {
      $array = $this->cache->restore($this);
      $this->data = array_merge($array['data'], $this->data);
      $this->keys = $array['keys'];
    } else {
      $update = true;
    }

    if ($this->checkSources === true || $this->hasCache() === false) {
      foreach ($this->sources as $key=>$source) {
        $isCached = false;

        if (isset($this->keys[$key])) {
          $cacheDate = $this->keys[$key];
          $isCached = true;
        }

        if (!$isCached || ($isCached && !$source->isFresh($cacheDate))) {
          $this->eventDispatcher->dispatch(Event\ResourceEvent::RESOURCE_MODIFIED, new Event\ResourceEvent($source));
          $update = true;
        }
      }
    }

    // Update data if needed
    if ($update === true) {
      $this->keys = array();
      $this->data = array();

      foreach ($this->sources as $key=>$source) {
        if ($source->validate($this)) {
          $this->eventDispatcher->dispatch(Event\ResourceEvent::RESOURCE_REFRESHING, new Event\ResourceEvent($source));
          $node = $source->fetch();

          if (!empty($node)){
            $this->update($source->getNamespace(), $node);
          }

          $this->keys[$key] = time();
        } else {
          throw new Exception\UnreachableResourceException($source);
        }
      }

      if ($this->hasCache()) {
        $this->eventDispatcher->dispatch(Event\CacheEvent::CACHE_WRITING, new Event\CacheEvent($this->cache));

        if ($this->cache->write($this)) {
          $this->eventDispatcher->dispatch(Event\CacheEvent::CACHE_WRITTEN, new Event\CacheEvent($this->cache));
        } else {
          throw new Exception\CacheException("Unable to write cache");
        }
      }
    }

    $this->loaded = true;
  }

  /**
   * get function.
   *
   * @access public
   * @param mixed $node
   * @param bool $create (default: false)
   * @return void
   */
  public function get($node, $default = null)
  {
    if ($this->loaded !== true) {
      throw new \Exception("Trying to read configuration tree before fetching data");
    }

    if (!is_null($default)) {
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
  public function getRoot()
  {
    if (!$this->loaded) {
      throw new \Exception("Trying to read configuration tree before fetching data");
    }

    return $this->data;
  }

  /**
   * getEventDispatcher function.
   *
   * @access public
   * @return object
   */
  public function getEventDispatcher()
  {
    return $this->eventDispatcher;
  }

  /**
   * getData function.
   *
   * @access public
   * @return void
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * getKeys function.
   *
   * @access public
   * @return void
   */
  public function getKeys()
  {
    return $this->keys;
  }

  /**
   * getUniqueCacheKey function.
   *
   * @access public
   * @return void
   */
  public function getUniqueCacheKey()
  {
    return sha1(implode('-', array_keys($this->sources)));
  }

  /**
   * addEvent function.
   *
   * @access public
   * @return boolean
   */
  public function addEvent($event, $callback)
  {
    return $this->eventDispatcher->addListener($event, $callback);
  }

  /**
   * setSourceChecking function.
   *
   * @access public
   * @param mixed $bool
   * @return void
   */
  public function setSourceChecking($bool)
  {
    $this->checkSources = ($bool === true);
  }

  /**
   * [setCacheManager description]
   * @param CacheManagerInterface $cache [description]
   */
  public function setCacheManager(CacheManagerInterface $cache)
  {
    $this->cache = $cache;
  }

  /**
   * hasCache function.
   *
   * @access public
   * @return void
   */
  public function hasCache()
  {
    return is_object($this->cache);
  }

  /**
   * resetCache function.
   *
   * @access public
   * @return void
   */
  public function resetCache()
  {
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

    if (empty($node)) {
      if (!is_array($value)) {
        throw new \Exception("You must provide an array when inserting data on tree root");
      }

      $this->data = $this->mergeArrays($this->data, $value);
    } else {
      $this->resolveNamespace($node, true, $value);
    }

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
  protected function resolveNamespace($node, $create=false, $insert=array())
  {
    $names = explode('.', $node);
    $current = &$this->data;
    $maxDepth = count($names);
    $currentName = '';
    $i = 0;

    foreach ($names as $depth=>$name) {
      $endReached = ($depth === ($maxDepth-1));
      $currentName .= ($depth>0 ? '.' : '').$name;

      if(!isset($current[$name])) {
        // new node
        if ($create===true) {
          $current[$name] = ($endReached) ? $insert : array();
        } else if(!$endReached) {
          throw new Exception\UndefinedNodeException('Trying to access an undefined namespace : '.$currentName);
        } else {
          return null;
        }
      } else {
        // existing node
        if ($endReached) {
          // updating value
          if(!empty($insert)) {
            // End of namespace search : he already exists so we merge new values with existing values
            if(is_array($current[$name])) {
              $current[$name] = $this->mergeArrays($current[$name], $insert);
            } else {
              $current[$name] = $insert;
            }
          }
        }
      }

      $current = &$current[$name];

      if (!is_array($current) || $endReached) {
        break;
      }
    }

    return $current;
  }

  /**
   * mergeArrays function.
   *
   * @access protected
   * @param mixed $arr1
   * @param mixed $arr2
   * @return void
   */
  protected function mergeArrays($arr1, $arr2)
  {
    return array_replace_recursive($arr1, $arr2);
  }
}