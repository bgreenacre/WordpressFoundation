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
class Menus {

    protected $_menus = array();

    /**
     * Plugin container object.
     *
     * @access protected
     * @var Pimple
     */
    protected $_container;

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

    public function add(array $properties)
    {
        $this->_menus[] = $properties;

        return $this;
    }

    public function register()
    {
        foreach ($this->_menus as $menu)
        {
            $controller = Arr\get_key('controller', $menu);

            if ($controller)
            {
                $c = $this->_container;

                $callback = function() use ($controller, $c)
                {
                    if ($sep = strpos('::', $controller))
                    {
                        $action = substr($controller, $sep+2);
                        $controller = substr($controller, 0, $sep);
                    }
                    else
                    {
                        $action = 'indexAction';
                    }

                    $controllerObject = new $controller($c);

                    return (string) $controllerObject->$action();
                };
            }

            var_dump($callback());
            switch(Arr\get_key('type', $menu))
            {
                case 'submenu':
                    add_submenu_page(
                        Arr\get_key('parent', $menu),
                        Arr\get_key('page_title', $menu),
                        Arr\get_key('menu_title', $menu),
                        Arr\get_key('capability', $menu, 'manage_sites'),
                        Arr\get_key('menu_slug', $menu),
                        $callback
                    );

                    break;
                case 'page':
                default:
                    add_menu_page(
                        Arr\get_key('page_title', $menu),
                        Arr\get_key('menu_title', $menu),
                        Arr\get_key('capability', $menu, 'manage_sites'),
                        Arr\get_key('menu_slug', $menu),
                        $callback,
                        Arr\get_key('icon_url', $menu),
                        Arr\get_key('position', $menu)
                    );

                    break;
            }
        }
    }

    public function setMenus(array $menus)
    {
        $this->_menus = $menus;

        return $this;
    }

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