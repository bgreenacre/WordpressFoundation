<?php namespace WordpressFoundation;
/**
 * WordpressFoundation Utilities
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

/**
 * Config class handles all interactions between loading and saving options
 * to the [wordpress options api](https://codex.wordpress.org/Options_API).
 * This class can also load option values from a config file which
 * is useful when setting default values in an options form.'
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class Assets {

    use \WordpressFoundation\Traits\ContainerAware;

    /**
     * Array of assets to register and optionally
     * enqueue in wordpress.
     *
     * @access protected
     * @var array
     */
    protected $adminAssets = array();

    /**
     * Array of assets to register and optionally
     * enqueue in wordpress.
     *
     * @access protected
     * @var array
     */
    protected $loginAssets = array();

    /**
     * Array of asset to register and optionally
     * enqueue in wordpress.
     *
     * @access protected
     * @var array
     */
    protected $frontAssets = array();

    /**
     * Add in the actions to register and
     * enqueue assets.
     *
     * @access public
     * @return $this
     */
    public function register()
    {
        $this->getProvider('hooks')
            ->addAction(
                'init',
                array( &$this, 'registerFrontAssets' )
            )
            ->addAction(
                'init',
                array( &$this, 'registerLoginAssets' )
            )
            ->addAction(
                'admin_init',
                array( &$this, 'registerAdminAssets' )
            )
            ->addAction(
                'wp_enqueue_scripts',
                array( &$this, 'enqueueFrontAssets' )
            )
            ->addAction(
                'login_enqueue_scripts',
                array( &$this, 'enqueueLoginAssets' )
            )
            ->addAction(
                'admin_enqueue_scripts',
                array( &$this, 'enqueueAdminAssets' )
            );

        return $this;
    }

    /**
     * Iterate through array of assets and prepare
     * each for the register method.
     *
     * @access public
     * @param  array  $assets Array of asset definitions
     * @return $this
     */
    public function load(array $assets)
    {
        foreach ($assets as $key => $asset)
        {
            $asset['enqueue'] = (bool) array_get($asset, 'enqueue', true);
            $asset['context'] = array_get($asset, 'context', 'front');

            $defaultType = null;

            if ($pos = strrpos($asset['uri'], '.'))
            {
                $defaultType = strtolower(substr($asset['uri'], $pos + 1));
            }

            $asset['type'] = array_get($asset, 'type', $defaultType);

            switch($asset['context'])
            {
                case 'login':
                    $this->loginAssets[] = $asset;

                    break;
                case 'admin':
                    $this->adminAssets[] = $asset;
                    
                    break;
                case 'front':
                default:
                    $this->frontAssets[] = $asset;
                    
                    break;
            }
        }

        return $this;
    }

    /**
     * Register front end assets.
     *
     * @access public
     * @return void
     */
    public function registerFrontAssets()
    {
        foreach ($this->frontAssets as $asset)
        {
            if (isset($asset['replaces']))
            {
                foreach ( (array) $asset['replaces'] as $replace)
                {
                    $this->deregisterAsset($replace, $asset['type']);
                }
            }

            $this->registerAsset($asset, $asset['type']);
        }
    }

    /**
     * Register login assets.
     *
     * @access public
     * @return void
     */
    public function registerLoginAssets()
    {
        foreach ($this->loginAssets as $asset)
        {
            if (isset($asset['replaces']))
            {
                foreach ( (array) $asset['replaces'] as $replace)
                {
                    $this->deregisterAsset($replace, $asset['type']);
                }
            }

            $this->registerAsset($asset, $asset['type']);
        }
    }

    /**
     * Register admin assets.
     *
     * @access public
     * @return void
     */
    public function registerAdminAssets()
    {
        foreach ($this->adminAssets as $asset)
        {
            if (isset($asset['replaces']))
            {
                foreach ( (array) $asset['replaces'] as $replace)
                {
                    $this->deregisterAsset($replace, $asset['type']);
                }
            }

            $this->registerAsset($asset, $asset['type']);
        }
    }

    /**
     * Enqueue front end assets.
     *
     * @access public
     * @return void
     */
    public function enqueueFrontAssets()
    {
        foreach ($this->frontAssets as $asset)
        {
            if (array_get($asset, 'enqueue') == true)
            {
                $this->enqueueAsset($asset['handle'], $asset['type']);
            }
        }
    }

    /**
     * Enqueue login assets.
     *
     * @access public
     * @return void
     */
    public function enqueueLoginAssets()
    {
        foreach ($this->loginAssets as $asset)
        {
            if (array_get($asset, 'enqueue') == true)
            {
                $this->enqueueAsset($asset['handle'], $asset['type']);
            }
        }
    }

    /**
     * Enqueue admin assets.
     *
     * @access public
     * @return void
     */
    public function enqueueAdminAssets()
    {
        foreach ($this->adminAssets as $asset)
        {
            if (array_get($asset, 'enqueue') == true)
            {
                $this->enqueueAsset($asset['handle'], $asset['type']);
            }
        }
    }

    /**
     * Enqueue asset.
     *
     * @access protected
     * @param  string $name Wordpress handle
     * @param  string $type Style or javascript.
     * @return void
     */
    protected function enqueueAsset($name, $type)
    {
        if ($type == 'css')
        {
            wp_enqueue_style($name);
        }
        elseif ($type == 'js')
        {
            wp_enqueue_script($name);
        }
    }

    /**
     * De-register a registered wordpress asset.
     *
     * @access protected
     * @param  string $name Handle of the registered asset.
     * @param  string $type Style or Javascript.
     * @return void
     */
    protected function deregisterAsset($name, $type)
    {
        if ($type == 'css')
        {
            wp_deregister_style($name);
        }
        elseif ($type == 'js')
        {
            wp_deregister_script($name);
        }
    }

    /**
     * Register an asset in wordpress.
     *
     * @access protected
     * @param  string $asset Asset handle.
     * @param  string $type  Style or Javascript.
     * @return void
     */
    protected function registerAsset($asset, $type)
    {
        if ($type == 'css')
        {
            wp_register_style(
                $asset['handle'],
                $this->getProvider('url')->asset($asset['uri']),
                array_get($asset, 'depends', array()),
                array_get($asset, 'version'),
                array_get($asset, 'in_footer', false)
            );
        }
        elseif ($type == 'js')
        {
            wp_register_script(
                $asset['handle'],
                $this->getProvider('url')->asset($asset['uri']),
                array_get($asset, 'depends', array()),
                array_get($asset, 'version'),
                array_get($asset, 'in_footer', false)
            );
        }
    }

}