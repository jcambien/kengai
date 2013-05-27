<?php namespace Kengai\Silex\Application;

use Kengai\Exception\UnreachableResourceException;
use Kengai\SourceReader;

trait ConfigTrait
{
  /**
   * Read a configuration from Kengai manager
   *
   * @param  string $node    Node name to read
   * @param  mixed  $default Default value if node is not found
   * @return mixed           Node value or default value
   */
  public function getConfig($node, $default = null)
  {
    return $this['config']->get($node, $default);
  }

  /**
   * Import a resource with format detection
   * @param  [type] $resource [description]
   * @return [type]           [description]
   */
  public function importConfig($resource)
  {
    if (is_array($resource)) {
      $this['config']->add(new SourceReader\ArraySourceReader($resource));

      return;
    }

    if (!is_file($resource) || !is_readable($resource)) {
      throw new UnreachableResourceException($resource);
    }

    $infos = pathinfo($resource);

    switch (strtolower($infos['extension'])) {
      case 'yml':
        $this['config']->add(new SourceReader\YamlSourceReader($resource));
      break;

      case 'json':
        $this['config']->add(new SourceReader\JsonSourceReader($resource));
      break;

      case 'ini':
        $this['config']->add(new SourceReader\IniSourceReader($resource));
      break;

      default:
        throw new \Exception("Unsupported or unrecognized configuration format: ".$resource);
      break;
    }
  }
}