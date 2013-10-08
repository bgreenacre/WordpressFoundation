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
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class PostTypes {

    protected $_types = array();

    /**
     * Plugin container object.
     *
     * @access protected
     * @var Pimple
     */
    protected $_container;

    public function __construct(Pimple $container, array $types)
    {
        $this->setContainer($container);
        $this->setTypes($types);
    }

    public function register()
    {
        foreach ($this->_types as $type => $args)
        {
            register_post_type($type, $args);
        }

        return $this;
    }

    public function addType($type, array $args = array(), array $subMenus = null)
    {
        $this->_types[$type] = $args;

        if ($subMenus !== null)
        {
            $this->_container['menus']->add($subMenus);
        }

        return $this;
    }

    public function setTypes(array $types)
    {
        foreach ($types as $type)
        {
            $this->addType(
                Arr\get_key('name', $type),
                Arr\get_key('args', $type),
                Arr\get_key('sub_menus', $type)
            );
        }

        return $this;
    }

    public function getTypes()
    {
        return $this->_types;
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