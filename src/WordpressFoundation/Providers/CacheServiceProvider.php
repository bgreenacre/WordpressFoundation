<?php namespace WordpressFoundation\Providers;

use WordpressFoundation\AbstractServiceProvider;

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