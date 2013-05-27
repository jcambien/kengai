<?php namespace Kengai\Silex\Provider;

use Silex\ServiceProviderInterface;
use Silex\Application;
use Kengai;

class ConfigServiceProvider implements ServiceProviderInterface
{
  /**
   * {@inheritDoc}
   */
  public function register(Application $app)
  {
    $app['config.cache_dir'] = '';

    $app['config.cache'] = $app->share(function() use ($app) {
      if ($app['cache'] !== true || empty($app['config.cache_dir'])) {
        return null;
      }

      if (!is_dir($app['config.cache_dir'])) {
        throw new \Exception('Unable to find Kengai cache directory ('.$app['config.cache_dir'].')');
      }

      if (extension_loaded('apc')) {
        $appName = isset($app['name']) ? $app['name'] : 'app';
        $data = $appName.'_config_data';
        $keys = $appName.'_config_keys';

        return new Kengai\CacheManager\APC($data, $keys);
      }

      return new Kengai\Manager\FileSystem($app['config.cache_dir']);
    });

    $app['config'] = $app->share(function() use ($app) {
      return new Kengai\Manager($app['config.cache']);
    });
  }

  /**
   * {@inheritDoc}
   */
  public function boot(Application $app)
  {
    $app['config']->fetch();
  }
}