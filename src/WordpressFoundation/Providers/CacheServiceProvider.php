<?php namespace WordpressFoundation\Providers;
/**
 * WordpressFoundation Package
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use WordpressFoundation\AbstractServiceProvider;

/**
 * Registers the cache provider functions.
 *
 * @package WordpressFoundation/Provider
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class CacheServiceProvider extends AbstractServiceProvider {

    public function register()
    {
        $this->app['cache.get'] = function($app, $key)
        {
            return get_transient($app['plugin.slug'] . '.' . $key);
        };

        $this->app['cache.set'] = function($app, $key, $value, $ttl = 1800)
        {
            set_transient($app['plugin.slug'] . '.' . $key, $value, $ttl);
        };

        $this->app['cache.delete'] = function($app, $key)
        {
            delete_transient($app['plugin.slug'] . '.' . $key);
        };
    }

}