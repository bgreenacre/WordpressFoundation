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
 * Theme container.
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class ThemeContainer extends Pimple {

    /**
     * Bootstrap the plugin by loading/setting
     * default container dependencys.
     *
     * @access public
     * @return $this
     */
    public function bootstrap()
    {
        // Add the plugin file loader.
        $this['fileloader'] = $this->share(function($c)
        {
            return new FileLoader(
                array(
                    'config'    => $c['paths.config'],
                    'resources' => $c['paths.resources'],
                    )
            );
        });

        // Add the input class that handles GLOBAL inputs.
        $this['input'] = $this->share(function($c)
        {
            return new Input(
                array(
                    'post'    => $_POST,
                    'query'   => $_GET,
                    'cookies' => $_COOKIE,
                    )
            );
        });

        // Add the view manager.
        $this['view'] = $this->share(function($c)
        {
            return new ViewManager($c);
        });

        // Add the config/options loader.
        $this['config'] = $this->share(function($c)
        {
            return new Config(
                $c['fileloader'],
                (isset($c['theme.slug'])) ? $c['theme.slug'] : null
            );
        });

        $this['hooks'] = $this->share(function($c)
        {
            return new Hooks($c);
        });

        $this['response'] = function($c)
        {
            return new Response($c);
        };

        return $this;
    }

}