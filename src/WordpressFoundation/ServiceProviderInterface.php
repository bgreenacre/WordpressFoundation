<?php namespace WordpressFoundation;

use Pimple;

interface ServiceProviderInterface {

    public function register(Pimple $container);

}