<?php

/**
 * This file is part of the Nette Framework.
 *
 * Copyright (c) 2004, 2010 David Grudl (http://davidgrudl.com)
 *
 * This source file is subject to the "Nette license", and/or
 * GPL license. For more information please see http://nette.org
 * @package Nette\Templates
 */



/**
 * NTemplate stored in file.
 *
 * @author     David Grudl
 */
class NFileTemplate extends NTemplate implements IFileTemplate
{
	/** @var int */
	public static $cacheExpire = NULL;

	/** @var ICacheStorage */
	private static $cacheStorage;

	/** @var string */
	private $file;



	/**
	 * Constructor.
	 * @param  string  template file path
	 */
	public function __construct($file = NULL)
	{
		if ($file !== NULL) {
			$this->setFile($file);
		}
	}



	/**
	 * Sets the path to the template file.
	 * @param  string  template file path
	 * @return NFileTemplate  provides a fluent interface
	 */
	public function setFile($file)
	{
		if (!is_file($file)) {
			throw new FileNotFoundException("Missing template file '$file'.");
		}
		$this->file = $file;
		return $this;
	}



	/**
	 * Returns the path to the template file.
	 * @return string  template file path
	 */
	public function getFile()
	{
		return $this->file;
	}



	/********************* rendering ****************d*g**/



	/**
	 * Renders template to output.
	 * @return void
	 */
	public function render()
	{
		if ($this->file == NULL) { // intentionally ==
			throw new InvalidStateException("Template file name was not specified.");
		}

		$this->__set('template', $this);

		$shortName = str_replace(NEnvironment::getVariable('appDir', ''), '', $this->file);

		$cache = new NCache($this->getCacheStorage(), 'Nette.FileTemplate');
		$key = trim(strtr($shortName, '\\/@', '.._'), '.') . '-' . md5($this->file);
		$cached = $content = $cache[$key];

		if ($content === NULL) {
			if (!$this->getFilters()) {
				$this->onPrepareFilters($this);
			}

			if (!$this->getFilters()) {
				NLimitedScope::load($this->file, $this->getParams());
				return;
			}

			$content = $this->compile(file_get_contents($this->file), "file \xE2\x80\xA6$shortName");
			$cache->save(
				$key,
				$content,
				array(
					NCache::FILES => $this->file,
					NCache::EXPIRE => self::$cacheExpire,
					NCache::CONSTS => 'NFramework::REVISION',
				)
			);
			$cache->release();
			$cached = $cache[$key];
		}

		if ($cached !== NULL && self::$cacheStorage instanceof NTemplateCacheStorage) {
			NLimitedScope::load($cached['file'], $this->getParams());
			fclose($cached['handle']);

		} else {
			NLimitedScope::evaluate($content, $this->getParams());
		}
	}



	/********************* caching ****************d*g**/



	/**
	 * Set cache storage.
	 * @param  NCache
	 * @return void
	 */
	public static function setCacheStorage(ICacheStorage $storage)
	{
		self::$cacheStorage = $storage;
	}



	/**
	 * @return ICacheStorage
	 */
	public static function getCacheStorage()
	{
		if (self::$cacheStorage === NULL) {
			$dir = NEnvironment::getVariable('tempDir') . '/cache';
			umask(0000);
			@mkdir($dir, 0755); // @ - directory may exists
			self::$cacheStorage = new NTemplateCacheStorage($dir);
		}
		return self::$cacheStorage;
	}

}
