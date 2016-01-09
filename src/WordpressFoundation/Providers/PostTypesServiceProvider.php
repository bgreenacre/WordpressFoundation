<?php namespace WordpressFoundation\Providers;
/**
 * WordpressFoundation Package
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Registers the Post Types service provider.
 *
 * @package WordpressFoundation/Provider
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class PostTypesServiceProvider implements ServiceProviderInterface {

    /**
     * Register post types service provider functions
     * to plugin container object.
     * 
     * @return void
     */
    public function register(Container $app)
    {
    }

    /**
     * Boot the post types service provider.
     * 
     * @return void
     */
    public function boot(Container $app)
    {
        // Add an init callback to register post types.
        add_action('init', function() use ($app)
        {
            // Get the post types defined within the
            // post.php config file and types index.
            $postTypes = $app['config']
                ->load('post.types')
                ->asArray();

            // Register each post type.
            foreach ($postTypes as $type => $args)
            {
                register_post_type($type, $args);
            }
        });
    }

}