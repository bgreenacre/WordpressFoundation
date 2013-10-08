<?php namespace WordpressFoundation;
/**
 * WordpressFoundation Utilities
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

/**
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class Menus {

    use WordpressFoundation\Traits\ContainerAware;

    /**
     * Array of menus to add in wordpress.
     *
     * @var array
     * @access protected
     */
    protected $menus = array();

    /**
     * Initialize and add an array of menus.
     *
     * @access public
     * @param array  $menus     Array of menus.
     * @return void
     */
    public function __construct(array $menus = null)
    {
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
        $this->menus[] = $properties;

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
        $controller = $this->getProvider('controller');

        foreach ($this->menus as $menu)
        {
            switch(array_get($menu, 'type'))
            {
                case 'submenu':
                    add_submenu_page(
                        array_get($menu, 'parent'),
                        array_get($menu, 'page_title'),
                        array_get($menu, 'menu_title'),
                        array_get($menu, 'capability', 'activate_plugins'),
                        array_get($menu, 'menu_slug'),
                        $controller(array_get($menu, 'controller'))
                    );

                    break;
                case 'page':
                default:
                    add_menu_page(
                        array_get($menu, 'page_title'),
                        array_get($menu, 'menu_title'),
                        array_get($menu, 'capability', 'activate_plugins'),
                        array_get($menu, 'menu_slug'),
                        $controller(array_get($menu, 'controller')),
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
        $this->menus = $menus;

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
        return $this->menus;
    }

}