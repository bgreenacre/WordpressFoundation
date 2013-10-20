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
class Plugin extends Pimple {

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
        $this['fileloader'] = $this->share(function()
        {
            return new FileLoader(
                $this,
                array(
                    'config'    => $this['paths.config'],
                    'resources' => $this['paths.resources'],
                )
            );
        });

        // Add the input class that handles GLOBAL inputs.
        $this['input'] = $this->share(function()
        {
            return new Input(
                $this,
                array(
                    'post'    => $_POST,
                    'query'   => $_GET,
                    'cookies' => $_COOKIE,
                    )
            );
        });

        // Add the view manager.
        $this['view'] = $this->share(function()
        {
            return new ViewManager($this);
        });

        // Add the config/options loader.
        $this['config'] = $this->share(function()
        {
            return new Config(
                $this,
                $this['fileloader'],
                (isset($this['plugin.slug'])) ? $this['plugin.slug'] : null
            );
        });

        $this['hooks'] = $this->share(function()
        {
            return new Hooks($this);
        });

        $this['menus'] = $this->share(function()
        {
            return new Menus($this, $this['config']->load('menus')->asArray());
        });

        $this['post.types'] = $this->share(function()
        {
            return new PostTypes($this, $this['config']->load('post.types')->asArray());
        });

        $this['taxonomies'] = $this->share(function()
        {
            return new Taxonomies($this, $this['config']->load('taxonomies')->asArray());
        });

        $this['widgets'] = $this->share(function()
        {
            return new Widgets($this);
        });

        $this['assets'] = $this->share(function()
        {
            return new Assets($this, $this['config']->load('assets')->asArray());
        });

        $this['urls'] = $this->share(function()
        {
            return new Urls($this);
        });

        $this['controller'] = $this->protect(function($controller)
        {
            if ($controller)
            {
                $callback = function() use ($controller)
                {
                    echo $this['controller.resolver']($controller, func_get_args());
                };
            }
            else
            {
                $callback = null;
            }

            return $callback;
        });

        $this['controller.resolver'] = $this->protect(function($controller, $args)
        {
            if ($sep = strpos($controller, '@'))
            {
                $action = substr($controller, $sep+1);
                $controller = substr($controller, 0, $sep);
            }
            else
            {
                $action = 'indexAction';
            }

            $controllerObject = new $controller($this);

            switch (count($args))
            {
                case 0:
                    return $controllerObject->$action();

                case 1:
                    return $controllerObject->$action($args[0]);

                case 2:
                    return $controllerObject->$action($args[0], $args[1]);

                case 3:
                    return $controllerObject->$action($args[0], $args[1], $args[2]);

                case 4:
                    return $controllerObject->$action($args[0], $args[1], $args[2], $args[3]);

                default:
                    return call_user_func_array(array($controllerObject, $action), $args);
            }
        });

        // Insert providers provided by plugin
        foreach ($this['config']->load('providers')->asArray() as $provider)
        {
            $this->register($provider);
        }

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
        $this['assets']->register();

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
            $this['post.types']->register();
            $this['taxonomies']->register();
        }, 2);

        $this['hooks']->addAction('admin_menu', function($c)
        {
            $this['menus']->register();
        });
    }

    public function register($provider)
    {
        $interfaces = class_implements($provider);

        if (is_array($interfaces) && ! in_array($this->interfaceToImplement, $interfaces))
        {
            throw new InvalidArgumentException();
        }

        $provider = new $provider();

        $provider->register($this);

        return $this;
    }

}