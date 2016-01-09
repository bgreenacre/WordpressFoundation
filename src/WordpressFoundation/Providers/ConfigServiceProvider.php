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
 * Registers the Config class to the plugin container.
 *
 * @package WordpressFoundation/Provider
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class ConfigServiceProvider implements ServiceProviderInterface {

    /**
     * Register the Config object to the container.
     * 
     * @return void
     */
    public function register(Container $app)
    {
        $app['config'] = function($app)
        {
            return new Config($app, $app['fileloader'], $app['plugin.slug']);
        });
    }

    /**
     * Boot config service provider.
     * 
     * @return void
     */
    public function boot(Container $app)
    {
    }

}