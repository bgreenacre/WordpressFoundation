<?php namespace WordpressFoundation;
/**
 * WordpressFoundation Package
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use WordpressFoundation\Providers\ConfigServiceProvider;
use WordpressFoundation\Providers\CacheServiceProvider;
use WordpressFoundation\Providers\MenusServiceProvider;
use WordpressFoundation\Providers\AssetsServiceProvider;
use WordpressFoundation\Providers\PostTypesServiceProvider;
use WordpressFoundation\Providers\TaxonomiesServiceProvider;
use WordpressFoundation\Providers\ViewServiceProvider;
use Illuminate\Container\Container;

/**
 * Plugin container. This class represents the entire
 * plugin that's utilizing this package.
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class Plugin extends Container {

    /**
     * All of the registered service providers.
     *
     * @var array
     */
    protected $serviceProviders = array();

    /**
     * The names of the loaded service providers.
     *
     * @var array
     */
    protected $loadedProviders = array();

    /**
     * Build a new container object.
     * 
     * @param array $instances Pass any global properties here.
     */
    public function __construct(array $instances = array())
    {
        foreach ($instances as $name => $value)
        {
            $this->instance($name, $value);
        }
    }

    /**
     * Bootstrap the plugin. This is where all the
     * providers are registered.
     * 
     * @return void
     */
    public function bootstrap()
    {
        $this->registerSingletons();
        $this->registerProviders();
    }

    /**
     * Run the plugin. Here is where the plugin should
     * be starting to add it's actions in wordpress and
     * can start using other providers that have been
     * registered in the plugin container.
     * 
     * @return void
     */
    public function run()
    {
        $this->bootProviders();
    }

    /**
     * Boot up all registered providers.
     * 
     * @return void
     */
    public function bootProviders()
    {
        foreach ($this->serviceProviders as $provider)
        {
            $provider->boot();
        }
    }

    /**
     * Registers singletons for the plugin core.
     * 
     * @return void
     */
    public function registerSingletons()
    {
        $this->instance(
            'fileloader',
            new FileLoader(
                array(
                    'config' => $this['path.config'],
                    'views'  => $this['path.views'],
                )
            )
        );
    }

    /**
     * Register all the internal core providers here.
     * 
     * @return void
     */
    public function registerProviders()
    {
        $this->register(new ConfigServiceProvider($this));
        $this->register(new AssetsServiceProvider($this));
        $this->register(new CacheServiceProvider($this));
        $this->register(new MenusServiceProvider($this));
        $this->register(new PostTypesServiceProvider($this));
        $this->register(new TaxonomiesServiceProvider($this));
        $this->register(new ViewServiceProvider($this));
    }

    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @param  array  $options
     * @return void
     */
    public function register($provider, $options = array())
    {
        // If the given "provider" is a string, we will resolve it, passing in the
        // application instance automatically for the developer. This is simply
        // a more convenient way of specifying your service provider classes.
        if (is_string($provider))
        {
            $provider = $this->resolveProviderClass($provider);
        }

        $provider->register();

        // Once we have registered the service we will iterate through the options
        // and set each of them on the application so they will be available on
        // the actual loading of the service objects and for developer usage.
        foreach ($options as $key => $value)
        {
            $this[$key] = $value;
        }

        $this->serviceProviders[] = $provider;

        $this->loadedProviders[get_class($provider)] = true;
    }

    /**
     * Resolve a service provider instance from the class name.
     *
     * @param  string  $provider
     * @return \Illuminate\Support\ServiceProvider
     */
    protected function resolveProviderClass($provider)
    {
        return new $provider($this);
    }

}