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
 * Registers the menus service provider.
 *
 * @package WordpressFoundation/Provider
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class MenusServiceProvider implements ServiceProviderInterface {

    /**
     * Register menus service provider functions to plugin
     * container object.
     * 
     * @return void
     */
    public function register(Container $app)
    {
    }

    /**
     * Boot the menus provider.
     * 
     * @return void
     */
    public function boot(Container $app)
    {
        // Add callback to admin_menu to inject menus into wordpress.
        add_action('admin_menu', function() use ($app)
        {
            // Get menus defined by config file.
            $menus = $app['config']->load('menus')->asArray();

            foreach ($menus as $menu)
            {
                switch(array_get($menu, 'type'))
                {
                    case 'submenu':
                        add_submenu_page(
                            array_get($menu, 'parent'),
                            array_get($menu, 'page_title'),
                            array_get($menu, 'menu_title'),
                            array_get($menu, 'capability', 'activate_plugins'),
                            array_get($menu, 'menu_slug'),
                            $app['controller'](array_get($menu, 'callback'))
                        );

                        break;
                    case 'page':
                    default:
                        add_menu_page(
                            array_get($menu, 'page_title'),
                            array_get($menu, 'menu_title'),
                            array_get($menu, 'capability', 'activate_plugins'),
                            array_get($menu, 'menu_slug'),
                            $app['controller'](array_get($menu, 'callback')),
                            array_get($menu, 'icon_url'),
                            array_get($menu, 'position')
                        );

                        break;
                }
            }
        });
    }

}