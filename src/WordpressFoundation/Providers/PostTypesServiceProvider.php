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
 * Registers the Post Types service provider.
 *
 * @package WordpressFoundation/Provider
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class PostTypesServiceProvider extends AbstractServiceProvider {

    /**
     * Boot the post types service provider.
     * 
     * @return void
     */
    public function boot()
    {
        // Add an init callback to register post types.
        add_action('init', function()
        {
            // Get the post types defined within the
            // post.php config file and types index.
            $postTypes = $this->app['config']
                ->load('post.types')
                ->toArray();

            // Register each post type.
            foreach ($postTypes as $type => $args)
            {
                register_post_type($type, $args);
            }
        });
    }

}