<?php namespace WordpressFoundation\Providers;
/**
 * WordpressFoundation Package
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Exception;
use Pimple\Container;

/**
 * Registers the View provider functions.
 *
 * @package WordpressFoundation/Provider
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class ViewServiceProvider implements ServiceProviderInterface {

    /**
     * Register View service provider function to
     * plugin container object.
     * 
     * @return void
     */
    public function register(Container $app)
    {
        $app['view'] = $app->factory(function($app, $view, array $data)
        {
            if ($data !== null)
            {
                extract($data, EXTR_SKIP);
            }

            // Capture the view output
            ob_start();

            try
            {
                // Use FileLoader to get rendered view.
                include rtrim($app['path.views'], '/') . DIRECTORY_SEPARATOR . $view . '.php';
            }
            catch (Exception $e)
            {
                // Delete the output buffer
                ob_end_clean();

                // Re-throw the exception
                wp_die($e->getMessage());
            }

            // Get the captured output and close the buffer
            return ob_get_clean();
        };
    }

    /**
     * Boot View service provider.
     * 
     * @return void
     */
    public function boot(Container $app)
    {
    }

}