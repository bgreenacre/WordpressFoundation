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
 * Provider class used to add widgets to wordpress.
 * 
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class Widgets {

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

    public function register()
    {
        foreach ($this->_widgets as $widgetDefinition)
        {
            wp_register_sidebar_widget(
                Arr\get_key('id', $widgetDefinition),
                Arr\get_key('name', $widgetDefinition),
                $this->_container['controller'](
                    Arr\get_key('frontController', $widgetDefinition)
                ),
                Arr\get_key('widgetOptions', $widgetDefinition, array())
            );

            wp_register_widget_control(
                Arr\get_key('id', $widgetDefinition),
                Arr\get_key('name', $widgetDefinition),
                $this->_container['controller'](
                    Arr\get_key(
                        'formController',
                        $widgetDefinition,
                        array($this, 'form')
                    )
                ),
                Arr\get_key('controlOptions', $widgetDefinition, array())
            );
        }
    }

    public function form()
    {
        //
    }

    public function load($dir)
    {
        if ( ! is_dir($dir))
        {
            throw new \InvalidArgumentException(
                sprintf(
                    '"%s" is not a valid directory for widgets loading.',
                    $dir
                )
            );
        }

        $dir = new \DirectoryIterator($dir);

        foreach ($dir as $file)
        {
            if ($file->isFile())
            {
                $pathInfo = pathinfo($file->getPathName());

                $widgetDefinition = $this->_container['file']
                    ->load(
                        $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'],
                        $pathInfo['extension']
                    );

                $this->addWidget($widgetDefinition);
            }
        }

        return $this;
    }

    public function addWidget(array $definition)
    {
        $this->_widgets[] = $definition;
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