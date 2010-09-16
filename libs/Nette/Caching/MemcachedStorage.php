<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Caching
 */



/**
 * Memcached storage.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Caching
 */
class NMemcachedStorage extends NObject implements ICacheStorage
{
	/**#@+ @internal cache structure */
	const META_CALLBACKS = 'callbacks';
	const META_DATA = 'data';
	const META_DELTA = 'delta';
	/**#@-*/

	/** @var Memcache */
	private $memcache;

	/** @var string */
	private $prefix;

	/** @var ICacheJournal */
	private $journal;



	/**
	 * Checks if Memcached extension is available.
	 * @return bool
	 */
	public static function isAvailable()
	{
		return extension_loaded('memcache');
	}



	public function __construct($host = 'localhost', $port = 11211, $prefix = '')
	{
		if (!self::isAvailable()) {
			throw new NotSupportedException("PHP extension 'memcache' is not loaded.");
		}

		$this->prefix = $prefix;
		$this->memcache = new Memcache;
		NDebug::tryError();
		$this->memcache->connect($host, $port);
		if (NDebug::catchError($msg)) {
			throw new InvalidStateException($msg);
		}
	}



	/**
	 * Read from cache.
	 * @param  string key
	 * @return mixed|NULL
	 */
	public function read($key)
	{
		$key = $this->prefix . $key;
		$meta = $this->memcache->get($key);
		if (!$meta) return NULL;

		// meta structure:
		// array(
		//     data => stored data
		//     delta => relative (sliding) expiration
		//     callbacks => array of callbacks (function, args)
		// )

		// verify dependencies
		if (!empty($meta[self::META_CALLBACKS]) && !NCache::checkCallbacks($meta[self::META_CALLBACKS])) {
			$this->memcache->delete($key, 0);
			return NULL;
		}

		if (!empty($meta[self::META_DELTA])) {
			$this->memcache->replace($key, $meta, 0, $meta[self::META_DELTA] + time());
		}

		return $meta[self::META_DATA];
	}



	/**
	 * Writes item into the cache.
	 * @param  string key
	 * @param  mixed  data
	 * @param  array  dependencies
	 * @return void
	 */
	public function write($key, $data, array $dp)
	{
		if (!empty($dp[NCache::ITEMS])) {
			throw new NotSupportedException('Dependent items are not supported by MemcachedStorage.');
		}

		$meta = array(
			self::META_DATA => $data,
		);

		$expire = 0;
		if (!empty($dp[NCache::EXPIRE])) {
			$expire = (int) $dp[NCache::EXPIRE];
			if (!empty($dp[NCache::SLIDING])) {
				$meta[self::META_DELTA] = $expire; // sliding time
			}
		}

		if (!empty($dp[NCache::CALLBACKS])) {
			$meta[self::META_CALLBACKS] = $dp[NCache::CALLBACKS];
		}

		if (!empty($dp[NCache::TAGS]) || isset($dp[NCache::PRIORITY])) {
			$this->getJournal()->write($this->prefix . $key, $dp);
		}

		$this->memcache->set($this->prefix . $key, $meta, 0, $expire);
	}



	/**
	 * Removes item from the cache.
	 * @param  string key
	 * @return void
	 */
	public function remove($key)
	{
		$this->memcache->delete($this->prefix . $key, 0);
	}



	/**
	 * Removes items from the cache by conditions & garbage collector.
	 * @param  array  conditions
	 * @return void
	 */
	public function clean(array $conds)
	{
		if (!empty($conds[NCache::ALL])) {
			$this->memcache->flush();

		} else {
			foreach ($this->getJournal()->clean($conds) as $entry) {
				$this->memcache->delete($entry, 0);
			}
		}
	}



	/**
	 * Returns the ICacheJournal
	 * @return ICacheJournal
	 */
	protected function getJournal()
	{
		if ($this->journal === NULL) {
			$this->journal = NEnvironment::getService('Nette\\Caching\\ICacheJournal');
		}
		return $this->journal;
	}

}
