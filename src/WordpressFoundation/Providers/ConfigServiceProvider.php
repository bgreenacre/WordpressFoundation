<?php namespace WordpressFoundation\Providers;

use WordpressFoundation\Config;
use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app['config'] = $this->app->share(function($app)
        {
            new Config($app['fileloader']);
        });
    }

}