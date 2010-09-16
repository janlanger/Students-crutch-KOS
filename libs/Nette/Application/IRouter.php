<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Application
 */



/**
 * The bi-directional router.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
interface IRouter
{
	/**#@+ flag */
	const ONE_WAY = 1;
	const SECURED = 2;
	/**#@-*/

	/**
	 * Maps HTTP request to a NPresenterRequest object.
	 * @param  IHttpRequest
	 * @return NPresenterRequest|NULL
	 */
	function match(IHttpRequest $httpRequest);

	/**
	 * Constructs absolute URL from NPresenterRequest object.
	 * @param  IHttpRequest
	 * @param  NPresenterRequest
	 * @return string|NULL
	 */
	function constructUrl(NPresenterRequest $appRequest, IHttpRequest $httpRequest);

}
