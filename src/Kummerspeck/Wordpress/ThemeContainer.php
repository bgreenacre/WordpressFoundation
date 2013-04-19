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
                $c,
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
                    'files'   => $_FILES,
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

        $this['widgets'] = $this->share(function($c)
        {
            return new Widgets($c);
        });

        $this['assets'] = $this->share(function($c)
        {
            return new Assets($c);
        });

        return $this;
    }

    public function run()
    {
        if (isset($this['paths.widgets']))
        {
            $this['hooks']->addAction(
                'widget_init',
                function($c)
                {
                    $c['widgets']
                        ->load($c['paths.widgets'])
                        ->register();
                }
            );
        }

        // Add in theme support based on config.
        $themeOptions = $this['config']->load('theme.options')->asArray();

        foreach ($themeOptions as $optName => $optValue)
        {
            if ($optValue === true)
            {
                add_theme_support($optName);
            }
            elseif (is_array($optValue))
            {
                add_theme_support($optName, $optValue);
            }
        }

        $this['assets']->load(
            $this['config']
                ->load('theme.assets')
                ->asArray()
        )
        ->register();

    }

}