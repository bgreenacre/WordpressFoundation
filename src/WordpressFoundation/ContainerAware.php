<?php namespace WordpressFoundation;
/**
 * WordpressFoundation Utilities
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use InvalidArgumentException;
use Pimple;

class ContainerAware {

    /**
     * Plugin container object.
     *
     * @access protected
     * @var Pimple
     */
    protected $containter;

    /**
     * Set the container object.
     *
     * @param Pimple $container [description]
     */
    public function __construct(Pimple $container)
    {
        $this->container = $container;
    }

    /**
     * Set container object.
     *
     * @access public
     * @param Pimple $container Plugin container object.
     * @return $this
     */
    public function setContainer(Pimple $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get container object.
     *
     * @access public
     * @return Pimple
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Get a provider from the container object.
     *
     * @access public
     * @param  string  $provider Name of the provider to get.
     * @return Closure           Provider object.
     */
    public function getProvider($provider)
    {
        if ( ! isset($this->container[$provider]))
        {
            throw new InvalidArgumentException(
                sprintf(
                    'WordpressFoundation provider "%s" does not exist.',
                    $provider
                )
            );
        }

        return $this->container[$provider];
    }

}