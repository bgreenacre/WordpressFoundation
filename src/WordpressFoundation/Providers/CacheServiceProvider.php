<?php namespace WordpressFoundation\Providers;
/**
 * WordpressFoundation Package
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Pimple\Container;

/**
 * Registers the cache provider functions.
 *
 * @package WordpressFoundation/Provider
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class CacheServiceProvider implements ServiceProviderInterface {

    /**
     * Register functions for cache provider to the plugin
     * container object.
     * 
     * @return void
     */
    public function register(Container $app)
    {
        $app['cache.get'] = function($app, $key)
        {
            return get_transient($app['plugin.slug'] . '.' . $key);
        };

        $app['cache.set'] = function($app, $key, $value, $ttl = 1800)
        {
            set_transient($app['plugin.slug'] . '.' . $key, $value, $ttl);
        };

        $app['cache.delete'] = function($app, $key)
        {
            delete_transient($app['plugin.slug'] . '.' . $key);
        };
    }

    /**
     * Boot cache service provider.
     * 
     * @return void
     */
    public function boot(Container $app)
    {
    }

}