<?php namespace Kummerspeck\Wordpress;
/**
 * Kummerspeck Wordpress Utilities
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Pimple;
use Kummerspeck\Arr as Arr;

/**
 * Config class handles all interactions between loading and saving options
 * to the [wordpress options api](https://codex.wordpress.org/Options_API).
 * This class can also load option values from a config file which
 * is useful when setting default values in an options form.'
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class Urls {

    /**
     * Plugin container object.
     *
     * @access protected
     * @var Pimple
     */
    protected $_container;

    /**
     * Constructor.
     *
     * @access public
     * @param Pimple $container [description]
     * @return void
     */
    public function __construct(Pimple $container)
    {
        $this->setContainer($container);
    }

    public function asset($uri)
    {
        if (strpos($uri, '://') !== false)
        {
            return $uri;
        }

        return rtrim($this->_container['urls.assets'], '/')
            . '/'
            . ltrim($uri, '/');
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