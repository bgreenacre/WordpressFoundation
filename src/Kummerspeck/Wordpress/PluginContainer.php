<?php namespace Kummerspeck\Wordpress;
/**
 * Kummerspeck Wordpress Utilities
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Pimple;

class PluginContainer extends Pimple {

    public function bootstrap()
    {
        $this['fileloader'] = $this->share(function($c)
        {
            return new FileLoader(
                array(
                    'config'    => $c['paths.config'],
                    'resources' => $c['paths.resources'],
                    'seeds'     => $c['paths.seeds'],
                    )
            );
        });

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

        $this['view'] = $this->share(function($c)
        {
            return new ViewManager($c);
        });

        $this['config'] = $this->share(function($c)
        {
            return new Config(
                $c['fileloader'],
                (isset($c['plugin.slug'])) ? $c['plugin.slug'] : null
            );
        });

        return $this;
    }

    public function run()
    {
        $this['installer']->run();
        $this['section.admin']->run();
    }

}