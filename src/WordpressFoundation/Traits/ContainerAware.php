<?php namespace WordpressFoundation\Traits;

use Pimple;

trait ContainerAware {

    /**
     * Plugin container object.
     *
     * @access protected
     * @var Pimple
     */
    protected $containter;

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