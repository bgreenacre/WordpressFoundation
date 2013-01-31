<?php namespace Kummerspeck\Cache;

class Cache {
	
	protected $_defaultTTL;

	public function __construct($defaultTTL = null)
	{
		if ($defaultTTL === null)
		{
			// Default to a half hour length in seconds.
			$defaultTTL = 60 * 30;
		}

		$this->setDefaultTTL($defaultTTL);
	}

	public function get($key)
	{
		return get_transient($key);
	}

	public function set($key, $value, $ttl = null)
	{
		if ($ttl === null)
		{
			$ttl = $this->getDefaultTTL();
		}
		elseif ( ! ctype_digit($ttl))
		{
			throw new \InvalidArgumentException('Invalid TTL value for cache object.');
		}

		set_transient($key, $value, $ttl);

		return $this;
	}

	public function delete($key)
	{
		delete_transient($key);

		return $this;
	}

	public function setDefaultTTL($ttl)
	{
		if ( ! ctype_digit($ttl))
		{
			throw new \InvalidArgumentException('Invalid TTL value for cache object.');
		}

		$this->_defaultTTL = (int) $int;

		return $this;
	}

	public function getDefaultTTL()
	{
		return $this->_defaultTTL;
	}

}