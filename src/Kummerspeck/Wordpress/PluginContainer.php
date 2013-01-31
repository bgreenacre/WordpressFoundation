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
 * Plugin container.
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class PluginContainer extends Pimple {

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
                (isset($c['plugin.slug'])) ? $c['plugin.slug'] : null
            );
        });

        $this['hooks'] = $this->share(function($c)
        {
            return new Hooks();
        });

        return $this;
    }

    /**
     * Run the plugin.
     *
     * @access public
     * @return void
     */
    public function run()
    {
    }

}