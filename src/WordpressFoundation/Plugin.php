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
     * Object of container.
     * 
     * @var Plugin
     */
    static protected $instance;

    /**
     * Return the instance of this plugin container.
     *
     * @access public
     * @return Plugin Container of plugin.
     */
    public static function instance()
    {
        return self::$instance;
    }

    /**
     * Expand constructor to set the container instance.
     *
     * @access public
     * @param array $values [description]
     */
    public function __construct(array $values = array())
    {
        parent::__construct($values);

        self::$instance = $this;
    }

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
                    'config'    => $this['paths.config'],
                    'resources' => $this['paths.resources'],
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
            $provider = new ViewManager();

            $provider->setContainer($this);

            return $provider;
        });

        // Add the config/options loader.
        $this['config'] = $this->share(function($c)
        {
            return new Config(
                $this['fileloader'],
                (isset($this['plugin.slug'])) ? $this['plugin.slug'] : null
            );
        });

        $this['hooks'] = $this->share(function($c)
        {
            $provider = new Hooks();

            $provider->setContainer($this);

            return $provider;
        });

        $this['menus'] = $this->share(function($c)
        {
            $provider = new Menus($this['config']->load('menus')->asArray());

            $provider->setContainer($this);

            return $provider;
        });

        $this['post.types'] = $this->share(function($c)
        {
            $provider = new PostTypes($this['config']->load('post.types')->asArray());

            $provider->setContainer($this);

            return $provider;
        });

        $this['taxonomies'] = $this->share(function($c)
        {
            $provider = new Taxonomies($this['config']->load('taxonomies')->asArray());

            $provider->setContainer($this);

            return $provider;
        });

        $this['widgets'] = $this->share(function($c)
        {
            return new Widgets($c);
        });

        $this['assets'] = $this->share(function($c)
        {
            $provider = new Assets();

            $provider->setContainer($this);

            return $provider;
        });

        $this['urls'] = $this->share(function($c)
        {
            $provider = new Urls();

            $provider->setContainer($this);

            return $provider;
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

            $controllerObject = new $controller();

            $controllerObject->setContainer($this);

            return call_user_func_array(
                array($controllerObject, $action),
                $args
            );
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
            $this['post.types']->register();
            $this['taxonomies']->register();
        }, 2);

        $this['hooks']->addAction('admin_menu', function($c)
        {
            $this['menus']->register();
        });
    }

}