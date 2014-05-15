<?php namespace WordpressFoundation\Providers;
/**
 * WordpressFoundation Package
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

/**
 * An abstract class that all service providers to the
 * plugin container must extend from.
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
abstract class AbstractServiceProvider {

    /**
     * The application instance.
     *
     * @var \WordpressFoundation\Plugin
     */
    protected $app;

    /**
     * Create a new service provider instance.
     *
     * @param  \WordpressFoundation\Plugin $app
     * @return void
     */
    public function __construct(\WordpressFoundation\Plugin $app)
    {
        $this->app = $app;
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {}

    /**
     * Register the service provider.
     *
     * @return void
     */
    abstract public function register();

}