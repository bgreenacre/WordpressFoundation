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
class Urls {

    use WordpressFoundation\Traits\ContainerAware;

    /**
     * Generate a url for an asset.
     *
     * @access public
     * @param  string $uri A uri or an absolute url.
     * @return string Normalized absolute url to an asset.
     */
    public function asset($uri)
    {
        if (strpos($uri, '://') !== false)
        {
            return $uri;
        }

        return rtrim($this->getContainer()['urls.assets'], '/')
            . '/'
            . ltrim($uri, '/');
    }

}