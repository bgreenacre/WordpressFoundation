<?php namespace WordpressFoundation\Providers;

/**
 * WordpressFoundation Package
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Pimple\Container;

/**
 * Extend the Pimple service provider interface with a new
 * method called boot which will essentially call all the
 * wordpress hooks for the plugin **after** all services
 * are registered within the container class.
 *
 * @package WordpressFoundation/Provider
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
interface ServiceProviderInterface extends \Pimple\ServiceProviderInterface {

	/**
	 * Boot the service provider in Wordpress.
	 *
	 * @param  Container $app Plugin container object.
	 * @return void
	 */
	public function boot(Container $app);

}