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

        $this->addHooks();
        $this->addFilters();

        return $this;
    }

    public function addHooks()
    {
        $this['hooks']->addAction('wp_enqueue_scripts', function($c)
        {
            $jqueryUrl     = $c['config']['theme.jqueryUrl'];
            $jqueryVersion = $c['config']['theme.jqueryVersion'];

            wp_deregister_script('jquery');
            wp_register_script('jquery', $jqueryUrl, array(), $jqueryVersion);

            $jqueryUiUrl     = $c['config']['theme.jqueryUiUrl'];
            $jqueryUiVersion = $c['config']['theme.jqueryUiVersion'];

            wp_deregister_script('jquery-ui');
            wp_register_script(
                'jquery-ui',
                $jqueryUiUrl,
                array('jquery'),
                $jqueryUiVersion
            );

            wp_register_script(
                'bootstrap',
                $c['url.assets'] . '/js/bootstrap.min.js',
                array('jquery'),
                '2.3.0'
            );

            wp_enqueue_script('jquery');
            wp_enqueue_script('bootstrap');

            wp_register_style(
                'bootstrap',
                $c['url.assets'] . '/css/bootstrap.min.css',
                array(),
                '2.3.0'
            );

            wp_register_style(
                'bootstrap-responsive',
                $c['url.assets'] . '/css/bootstrap-responsive.min.css',
                array(),
                '2.3.0'
            );

            wp_enqueue_style('bootstrap');
            wp_enqueue_style('bootstrap-responsive');
        });

        return $this;
    }

    public function addFilters()
    {
        //
    }

}