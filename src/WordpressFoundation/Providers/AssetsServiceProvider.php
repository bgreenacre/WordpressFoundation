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
 * Registers the Assets service provider.
 *
 * @package WordpressFoundation/Provider
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class AssetsServiceProvider implements ServiceProviderInterface {

    /**
     * Register the assets functions to the plugin container.
     * 
     * @return void
     */
    public function register(Container $app)
    {
         // Return an array of assets separated by context
         // in which each asset should be loaded.
        $app['assets'] = function($app)
        {
            // Intialize an empty assets array.
            $assets = [
                'front' => [],
                'admin' => [],
                'login' => [],
            ];

            // Iterate over the assets defined in the assets.php config
            // file and add it to the proper context index of the assets array.
            foreach ($app['config']->load('assets') as $key => $asset)
            {
                // If it's a string then gather some information
                // based on the uri path.
                if (is_string($asset))
                {
                    // Find the context within the URI.
                    foreach (['admin', 'login', 'front'] as $inUri)
                    {
                        if (strpos($asset, $inUri) !== false)
                        {
                            $context = $inUri;
                        }
                    }

                    // Set the asset array based on string
                    $asset = [
                        'handle'  => sprintf('%s.%s', $app['plugin.slug'], basename($asset)),
                        'uri'     => $asset,
                        'context' => ($context) ?: 'front',
                        'version' => $app['plugin.version'],
                    ];
                }

                // Should this be enqueued?
                $asset['enqueue'] = (bool) array_get($asset, 'enqueue', true);

                // Set the correct context and default to "front" context.
                $asset['context'] = array_get($asset, 'context', 'front');

                $defaultType = null;

                // Set the defaultType based on file extension in the uri.
                if (false !== ($pos = strrpos($asset['uri'], '.')))
                {
                    $defaultType = strtolower(substr($asset['uri'], $pos + 1));
                }

                // Make sure type is set in the asset array.
                $asset['type'] = array_get($asset, 'type', $defaultType);

                // Add asset to the assets array.
                $assets[$asset['context']][] = $asset;
            }

            return $assets;
        };

        // Register a new asset to the wordpress system
        $app['asset.register'] = function($app, $asset)
        {
            if ($asset['type'] == 'css')
            {
                wp_register_style(
                    $asset['handle'],
                    $app['url.assets'] . $asset['uri'],
                    array_get($asset, 'depends', []),
                    array_get($asset, 'version'),
                    array_get($asset, 'in_footer', false)
                );
            }
            elseif ($asset['type'] == 'js')
            {
                wp_register_script(
                    $asset['handle'],
                    $app['url.assets'] . $asset['uri'],
                    array_get($asset, 'depends', []),
                    array_get($asset, 'version'),
                    array_get($asset, 'in_footer', false)
                );
            }
        };

        // Unregister an asset from the wordpress system
        $app['asset.unregister'] = function($app, $name, $type)
        {
            if ($type == 'css')
            {
                wp_deregister_style($name);
            }
            elseif ($type == 'js')
            {
                wp_deregister_script($name);
            }
        };

        // Enqueue an asset in wordpress.
        $app['asset.enqueue'] = function($app, $name, $type)
        {
            if ($type == 'css')
            {
                wp_enqueue_style($name);
            }
            elseif ($type == 'js')
            {
                wp_enqueue_script($name);
            }
        };
    }

    /**
     * Boot this provider by adding it's required actions to the
     * wordpress hooks system.
     * 
     * @return void
     */
    public function boot(Container $app)
    {
        // Register frontend and login page assets.
        add_action('init', function() use ($app)
        {
            $frontAssets = array_get($app, 'assets.front', []);
            $loginAssets = array_get($app, 'assets.login', []);

            foreach ([$frontAssets, $loginAssets] as $assets)
            {
                foreach ($assets as $asset)
                {
                    if (isset($asset['replaces']))
                    {
                        foreach ( (array) $asset['replaces'] as $replace)
                        {
                            $app['asset.unregister']($replace, $asset['type']);
                        }
                    }

                    $app['asset.register']($asset);
                }
            }
        });

        // Register admin assets.
        add_action('admin_init', function() use ($app)
        {
            $adminAssets = array_get($app, 'assets.admin', []);

            foreach ($adminAssets as $asset)
            {
                if (isset($asset['replaces']))
                {
                    foreach ( (array) $asset['replaces'] as $replace)
                    {
                        $app['asset.unregister']($replace, $asset['type']);
                    }
                }

                $app['asset.register']($asset);
            }
        });

        // Map of asset context to wordpress action hook name.
        $actions = [
            'front' => 'wp_enqueue_scripts',
            'login' => 'login_enqueue_scripts',
            'admin' => 'admin_enqueue_scripts',
        ];

        // Iterate each action and add a callback to the hook.
        foreach ($actions as $context => $action)
        {
            add_action($action, function() use ($app, $context)
            {
                // Get assets array for context.
                $assets = array_get($app, 'assets.' . $context, []);

                // Enqueue each asset that's required to be.
                foreach ($assets as $asset)
                {
                    if (array_get($asset, 'enqueue') == true)
                    {
                        $app['asset.enqueue']($asset['handle'], $asset['type']);
                    }
                }
            });
        }

        unset($actions);
    }

}