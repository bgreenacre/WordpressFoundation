<?php namespace WordpressFoundation\Providers;
/**
 * WordpressFoundation Package
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use WordpressFoundation\Config;
use WordpressFoundation\AbstractServiceProvider;

/**
 * Registers the Config class to the plugin container.
 *
 * @package WordpressFoundation/Provider
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class ConfigServiceProvider extends AbstractServiceProvider {

    /**
     * Register the Config object to the container.
     * 
     * @return void
     */
    public function register()
    {
        $this->app['config'] = $this->app->share(function($app)
        {
            new Config($app['fileloader'], $app['plugin.slug']);
        });
    }

}