<?php namespace Kummerspeck\Wordpress;
/**
 * Kummerspeck Wordpress Utilities
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Pimple;

/**
 * Wrapper for the [transient api](https://codex.wordpress.org/Transients_API)
 * 
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class Router {

    /**
     * Plugin container object.
     *
     * @access protected
     * @var Pimple
     */
    protected $_container;

    /**
     * Array of callbacks per route name.
     *
     * @access protected
     * @var array
     */
    protected $_callbacks = array();

    /**
     * Array of routes.
     *
     * @access protected
     * @var array
     */
    protected $_routes = array();

    /**
     * Construct object and optionally change default priority.
     *
     * @access public
     * @param $container Application container
     * @return void
     */
    public function __construct(Pimple $container)
    {
        $this->setContainer($container);

        $this->_container['hooks']
            ->activateHook(function($c)
            {
                $c['hooks']->addFilter(
                    'rewrite_rules_array',
                    function($c, $wp_rules)
                    {
                        $c['routes']->rewriteRules($wp_rules);
                    }
                );
            })
            ->addAction('init', function($c)
            {
                $c['hooks']->addFilter(
                    'rewrite_rules_array',
                    function($c, $wp_rules)
                    {
                        $c['routes']->rewriteRules($wp_rules);
                    }
                );
            });
    }

    public function rewriteRules(array $wp_rules)
    {
        //
    }

    public function add($name, $search, $callback)
    {
        $this->_routes[$name] = $search;
        $this->_callbacks[$name] = $callback;
    }

    /**
     * Set container object.
     *
     * @access public
     * @param Pimple $container Plugin container object.
     * @return $this
     */
    public function setContainer(Pimple $container)
    {
        $this->_container = $container;

        return $this;
    }

    /**
     * Get container object.
     *
     * @access public
     * @return Pimple
     */
    public function getContainer()
    {
        return $this->_container;
    }

}