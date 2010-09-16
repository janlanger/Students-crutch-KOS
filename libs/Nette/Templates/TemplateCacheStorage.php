<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Templates
 */



/**
 * NTemplate cache storage.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Templates
 */
class NTemplateCacheStorage extends NFileStorage
{

	/**
	 * Reads cache data from disk.
	 * @param  array
	 * @return mixed
	 */
	protected function readData($meta)
	{
		return array(
			'file' => $meta[self::FILE],
			'handle' => $meta[self::HANDLE],
		);
	}



	/**
	 * Returns file name.
	 * @param  string
	 * @return string
	 */
	protected function getCacheFile($key)
	{
		return parent::getCacheFile($key) . '.php';
	}

}
