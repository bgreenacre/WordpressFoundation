<?php namespace Kummerspeck\Wordpress;
/**
 * Kummerspeck Wordpress Utilities
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use Pimple;
use Kummerspeck\Arr as Arr;

/**
 * Config class handles all interactions between loading and saving options
 * to the [wordpress options api](https://codex.wordpress.org/Options_API).
 * This class can also load option values from a config file which
 * is useful when setting default values in an options form.'
 *
 * @package Kummerspeck/WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class Assets {

    /**
     * Plugin container object.
     *
     * @access protected
     * @var Pimple
     */
    protected $_container;

    /**
     * Array of assets to register and optionally
     * enqueue in wordpress.
     *
     * @access protected
     * @var array
     */
    protected $_adminAssets = array();

    /**
     * Array of assets to register and optionally
     * enqueue in wordpress.
     *
     * @access protected
     * @var array
     */
    protected $_loginAssets = array();

    /**
     * Array of asset to register and optionally
     * enqueue in wordpress.
     *
     * @access protected
     * @var array
     */
    protected $_frontAssets = array();

    /**
     * Constructor.
     *
     * @access public
     * @param Pimple $container [description]
     * @return void
     */
    public function __construct(Pimple $container)
    {
        $this->setContainer($container);
    }

    /**
     * Add in the actions to register and
     * enqueue assets.
     *
     * @access public
     * @return $this
     */
    public function register()
    {
        $this->_container['hooks']
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
            $asset['enqueue'] = (bool) Arr\get_key('enqueue', $asset, true);
            $asset['context'] = Arr\get_key('context', $asset, 'front');
            $asset['type']    = Arr\get_key(
                'type',
                $asset,
                ( ($pos = strrpos($asset['uri'], '.')) > 0)
                    ? strtolower(substr($asset['uri'], $pos + 1))
                    : null
            );

            switch($asset['context'])
            {
                case 'login':
                    $this->_loginAssets[] = $asset;

                    break;
                case 'admin':
                    $this->_adminAssets[] = $asset;
                    
                    break;
                case 'front':
                default:
                    $this->_frontAssets[] = $asset;
                    
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
        foreach ($this->_frontAssets as $asset)
        {
            if (isset($asset['replaces']))
            {
                foreach ( (array) $asset['replaces'] as $replace)
                {
                    $this->_deregisterAsset($replace, $asset['type']);
                }
            }

            $this->_registerAsset($asset, $asset['type']);
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
        foreach ($this->_loginAssets as $asset)
        {
            if (isset($asset['replaces']))
            {
                foreach ( (array) $asset['replaces'] as $replace)
                {
                    $this->_deregisterAsset($replace, $asset['type']);
                }
            }

            $this->_registerAsset($asset, $asset['type']);
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
        foreach ($this->_adminAssets as $asset)
        {
            if (isset($asset['replaces']))
            {
                foreach ( (array) $asset['replaces'] as $replace)
                {
                    $this->_deregisterAsset($replace, $asset['type']);
                }
            }

            $this->_registerAsset($asset, $asset['type']);
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
        foreach ($this->_frontAssets as $asset)
        {
            if (Arr\get_key('enqueue', $asset) == true)
            {
                $this->_enqueueAsset($asset['handle'], $asset['type']);
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
        foreach ($this->_loginAssets as $asset)
        {
            if (Arr\get_key('enqueue', $asset) == true)
            {
                $this->_enqueueAsset($asset['handle'], $asset['type']);
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
        foreach ($this->_adminAssets as $asset)
        {
            if (Arr\get_key('enqueue', $asset) == true)
            {
                $this->_enqueueAsset($asset['handle'], $asset['type']);
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
    protected function _enqueueAsset($name, $type)
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
    protected function _deregisterAsset($name, $type)
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
    protected function _registerAsset($asset, $type)
    {
        $c = $this->getContainer();

        if ($type == 'css')
        {
            wp_register_style(
                $asset['handle'],
                $c['url']->asset($asset['uri']),
                Arr\get_key('depends', $asset, array()),
                Arr\get_key('version', $asset),
                Arr\get_key('in_footer', $asset, false)
            );
        }
        elseif ($type == 'js')
        {
            wp_register_script(
                $asset['handle'],
                $c['url']->asset($asset['uri']),
                Arr\get_key('depends', $asset, array()),
                Arr\get_key('version', $asset),
                Arr\get_key('in_footer', $asset, false)
            );
        }
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
        $this->_container = $container;

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
        return $this->_container;
    }

}