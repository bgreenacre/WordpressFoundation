<?php namespace WordpressFoundation;
/**
 * WordpressFoundation Utilities
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use InvalidArgumentException;
use DirectoryIterator;

/**
 * Provider class used to add widgets to wordpress.
 * 
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class Widgets {

    use WordpressFoundation\Traits\ContainerAware;

    public function register()
    {
        foreach ($this->widgets as $widgetDefinition)
        {
            wp_register_sidebar_widget(
                array_get($widgetDefinition, 'id'),
                array_get($widgetDefinition, 'name'),
                $this->getProvider('controller')(
                    array_get($widgetDefinition, 'frontController')
                ),
                array_get($widgetDefinition, 'widgetOptions', array())
            );

            wp_register_widget_control(
                array_get($widgetDefinition, 'id'),
                array_get($widgetDefinition, 'name'),
                $this->getProvider('controller')(
                    array_get(
                        $widgetDefinition,
                        'formController',
                        array($this, 'form')
                    )
                ),
                array_get($widgetDefinition, 'controlOptions', array())
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
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" is not a valid directory for widgets loading.',
                    $dir
                )
            );
        }

        $dir = new DirectoryIterator($dir);

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
        $this->widgets[] = $definition;
    }

}