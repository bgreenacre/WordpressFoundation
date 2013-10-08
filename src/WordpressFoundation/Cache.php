<?php namespace WordpressFoundation;
/**
 * WordpressFoundation Utilities
 *
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */

use InvalidArgumentException;

/**
 * Wrapper for the [transient api](https://codex.wordpress.org/Transients_API)
 * 
 * @package WordpressFoundation
 * @author Brian Greenacre <bgreenacre42@gmail.com>
 * @version $id$
 */
class Cache {

	/**
	 * Default length of time for the cached values to live.
	 *
	 * @access protected
	 * @var integer
	 */
	protected $defaultTTL;

	/**
	 * Set the default TTL variable in the constructor.
	 *
	 * @access public
	 * @param  integer $defaultTTL TTL to default on.
	 * @return void
	 */
	public function __construct($defaultTTL = null)
	{
		if ($defaultTTL === null)
		{
			// Default to a half hour length in seconds.
			$defaultTTL = 60 * 30;
		}

		$this->setDefaultTTL($defaultTTL);
	}

	/**
	 * Get a cached value from the transient API.
	 *
	 * @access public
	 * @param  string $key Cached variable name.
	 * @return mixed       Cached variable contents.
	 */
	public function get($key)
	{
		return get_transient($key);
	}

	/**
	 * Set a cached value.
	 *
	 * @access public
	 * @param string  $key   Cached variable name.
	 * @param mixed   $value Contents to cache on variable name.
	 * @param integer $ttl   TTL of variable.
	 * @return $this
	 */
	public function set($key, $value, $ttl = null)
	{
		if ($ttl === null)
		{
			// Use the default ttl value.
			$ttl = $this->getDefaultTTL();
		}
		elseif ( ! ctype_digit($ttl))
		{
			// Bad argument value.
			throw new InvalidArgumentException('Invalid TTL value for cache object.');
		}

		set_transient($key, $value, $ttl);

		return $this;
	}

	/**
	 * Deletes value from the cache.
	 *
	 * @access public
	 * @param  string $key Variable name.
	 * @return $this
	 */
	public function delete($key)
	{
		delete_transient($key);

		return $this;
	}

	/**
	 * Set the default ttl value.
	 *
	 * @access public
	 * @param  integer $ttl Number of seconds for cache value to live.
	 * @return void
	 */
	public function setDefaultTTL($ttl)
	{
		if ( ! ctype_digit($ttl))
		{
			throw new InvalidArgumentException('Invalid TTL value for cache object.');
		}

		$this->defaultTTL = (int) $int;

		return $this;
	}

	/**
	 * Get the default TTL.
	 *
	 * @access public
	 * @return integer TTL.
	 */
	public function getDefaultTTL()
	{
		return $this->defaultTTL;
	}

}