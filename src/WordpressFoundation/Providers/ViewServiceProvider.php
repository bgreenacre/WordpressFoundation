<?php namespace WordpressFoundation\Providers;
/**
 * WordpressFoundation Package
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Exception;

/**
 * Registers the View provider functions.
 *
 * @package WordpressFoundation/Provider
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class ViewServiceProvider extends AbstractServiceProvider {

    /**
     * Register View service provider function to
     * plugin container object.
     * 
     * @return void
     */
    public function register()
    {
        $this->app['view'] = function($app, $view, array $data)
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
                include $app['path.views'] . $view . '.php';
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
    public function boot()
    {
    }

}