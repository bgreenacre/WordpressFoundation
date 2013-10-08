<?php namespace WordpressFoundation;
/**
 * WordpressFoundation Utilities
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Pimple;

/**
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class Menus {

    /**
     * Array of menus to add in wordpress.
     *
     * @var array
     * @access protected
     */
    protected $_menus = array();

    /**
     * Plugin container object.
     *
     * @access protected
     * @var Pimple
     */
    protected $_container;

    /**
     * Initialize and add an array of menus.
     *
     * @access public
     * @param Pimple $container Container object.
     * @param array  $menus     Array of menus.
     * @return void
     */
    public function __construct(Pimple $container, array $menus = null)
    {
        $this->setContainer($container);

        if ($menus !== null)
        {
            foreach ($menus as $menuDefinition)
            {
                $this->add($menuDefinition);
            }
        }
    }

    /**
     * Add a single menus definition array.
     *
     * @access public
     * @param array $properties Menu definition array.
     * @return void
     */
    public function add(array $properties)
    {
        $this->_menus[] = $properties;

        return $this;
    }

    /**
     * Iterate through all the menus and register them
     * in wordpress using appropriate function.
     *
     * @access public
     * @return void
     */
    public function register()
    {
        foreach ($this->_menus as $menu)
        {
            switch(Arr\get_key('type', $menu))
            {
                case 'submenu':
                    add_submenu_page(
                        array_get($menu, 'parent'),
                        array_get($menu, 'page_title'),
                        array_get($menu, 'menu_title'),
                        array_get($menu, 'capability', 'activate_plugins'),
                        array_get($menu, 'menu_slug'),
                        $this->_container['controller'](array_get($menu, 'controller'))
                    );

                    break;
                case 'page':
                default:
                    add_menu_page(
                        array_get($menu, 'page_title'),
                        array_get($menu, 'menu_title'),
                        array_get($menu, 'capability', 'activate_plugins'),
                        array_get($menu, 'menu_slug'),
                        $this->_container['controller'](array_get($menu, 'controller')),
                        array_get($menu, 'icon_url'),
                        array_get($menu, 'position')
                    );

                    break;
            }
        }
    }

    /**
     * Set's menus properties of the object.
     *
     * @access public
     * @param array $menus Array of menu definitions.
     * @return $this
     */
    public function setMenus(array $menus)
    {
        $this->_menus = $menus;

        return $this;
    }

    /**
     * Get menus property.
     *
     * @access public
     * @return array Menus array.
     */
    public function getMenus()
    {
        return $this->_menus;
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