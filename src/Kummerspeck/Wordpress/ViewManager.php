<?php namespace Kummerspeck\Wordpress;
/**
 * Kummerspeck Wordpress Utilities
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

class ViewManager {

    protected $_container;

    public function __construct(PluginContainer $container)
    {
        $this->setContainer($container);
    }

    public function make($view, array $data = null)
    {
        $c = $this->getContainer();

        if ($data !== null)
        {
            extract($data, EXTR_SKIP);
        }

        // Capture the view output
        ob_start();

        try
        {
            // Use FileLoader to get rendered view.
            include $c['paths.views'] . $view . '.php';
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

    public function setContainer(PluginContainer $container)
    {
        $this->_container = $container;

        return $this;
    }

    public function getContainer()
    {
        return $this->_container;
    }

}