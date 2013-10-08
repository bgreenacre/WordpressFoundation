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
 * Plugin container.
 *
 * @package WordpressFoundation
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
            return new Hooks($c);
        });

        $this['response'] = function($c)
        {
            return new Response($c);
        };

        $this['menus'] = $this->share(function($c)
        {
            return new Menus($c, $c['config']->load('menus')->asArray());
        });

        $this['post.types'] = $this->share(function($c)
        {
            return new PostTypes($c, $c['config']->load('post.types')->asArray());
        });

        $this['widgets'] = $this->share(function($c)
        {
            return new Widgets($c);
        });

        $this['assets'] = $this->share(function($c)
        {
            return new Assets($c);
        });

        $this['urls'] = $this->share(function($c)
        {
            return new Urls($c);
        });

        $this['controller'] = $this->protect(function($controller) use ($c)
        {
            if ($controller)
            {
                $c = $this->_container;

                $callback = function() use ($controller, $c)
                {
                    echo $c['controller.resolver']($controller, func_get_args());
                };
            }
            else
            {
                $callback = null;
            }

            return $callback;
        });

        $this['controller.resolver'] = $this->protect(function($controller, $args) use ($c)
        {
            if (is_callable($controller, true))
            {
                return call_user_func_array($controller, $args);
            }
            else
            {
                if ($sep = strpos($controller, '::'))
                {
                    $action = substr($controller, $sep+2);
                    $controller = substr($controller, 0, $sep);
                }
                else
                {
                    $action = 'indexAction';
                }

                $controllerObject = new $controller($c);

                return call_user_func_array(
                    array($controllerObject, $action),
                    $args
                );
            }
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
        $this['hooks']->activateHook(function($c)
        {
            flush_rewrite_rules();
        });

        $this['hooks']->deactivateHook(function($c)
        {
            flush_rewrite_rules();
        });

        $this['hooks']->addAction('init', function($c)
        {
            $c['post.types']->register();
        }, 2);

        $this['hooks']->addAction('admin_menu', function($c)
        {
            $c['menus']->register();
        });
    }

}