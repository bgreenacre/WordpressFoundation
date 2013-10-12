<?php namespace WordpressFoundation;
/**
 * WordpressFoundation Utilities
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Exception;

/**
 * Loads and parse view files.
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class ViewManager {

    use \WordpressFoundation\Traits\ContainerAware;

    /**
     * Load a view and render it.
     *
     * @access public
     * @param  string $view filename of view
     * @param  array  $data Associative array of data to pass to view file.
     * @return string       Rendered view.
     */
    public function make($view, array $data = null)
    {
        $container =& $this->getContainer();

        if ($data !== null)
        {
            extract($data, EXTR_SKIP);
        }

        // Capture the view output
        ob_start();

        try
        {
            // Use FileLoader to get rendered view.
            include $this->container['paths.views'] . $view . '.php';
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
    }

}