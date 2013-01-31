<?php namespace Kummerspeck\Wordpress;
/**
 * Kummerspeck Wordpress Utilities
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

/**
 * Loads and parse view files.
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class ViewManager {

    /**
     * Plugin container object.
     *
     * @access protected
     * @var PluginContainer
     */
    protected $_container;

    /**
     * Construct object.
     *
     * @access public
     * @param PluginContainer $container Plugins container object.
     * @return void
     */
    public function __construct(PluginContainer $container)
    {
        $this->setContainer($container);
    }

    /**
     * Load a view and render it.
     *
     * @access public
     * @param  string $view filename of view
     * @param  [type] $data Associative array of data to pass to view file.
     * @return string       Rendered view.
     */
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
            $c['fileloader']->load($c['paths.views'] . $view, '.php');
        }
        catch (\Exception $e)
        {
            // Delete the output buffer
            ob_end_clean();

            // Re-throw the exception
            wp_die($e->getMessage());
        }

        // Get the captured output and close the buffer
        return ob_get_clean();
    }

    /**
     * Set container object.
     *
     * @access public
     * @param PluginContainer $container Plugin container object.
     * @return $this
     */
    public function setContainer(PluginContainer $container)
    {
        $this->_container = $container;

        return $this;
    }

    /**
     * Get container object.
     *
     * @access public
     * @return PluginContainer
     */
    public function getContainer()
    {
        return $this->_container;
    }

}