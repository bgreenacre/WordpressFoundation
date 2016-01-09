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
 * Registers the Taxonomies service provider.
 *
 * @package WordpressFoundation/Provider
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class TaxonomiesServiceProvider implements ServiceProviderInterface {

    /**
     * Register taxonomies service provider function to
     * plugin container object.
     * 
     * @return void
     */
    public function register(Container $app)
    {
    }

    /**
     * Boot the taxonomies service provider.
     * 
     * @return void
     */
    public function boot(Container $app)
    {
        // Add init callback to register new wordpress
        // taxonomies.
        add_action('init', function() use ($app)
        {
            // Get taxonomies defined in the taxonomies.php
            // config file.
            $taxonomies = $app['config']
                ->load('taxonomies')
                ->asArray();

            // Register each taxonomy.
            foreach ($taxonomies as $name => $taxonomy)
            {
                register_taxonomy(
                    $name,
                    array_get($taxonomy, 'type'),
                    array_get($taxonomy, 'args')
                );
            }
        });
    }

}