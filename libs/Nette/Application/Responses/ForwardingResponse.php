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
 * Forwards to new request.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
class NForwardingResponse extends NObject implements IPresenterResponse
{
	/** @var NPresenterRequest */
	private $request;



	/**
	 * @param  NPresenterRequest  new request
	 */
	public function __construct(NPresenterRequest $request)
	{
		$this->request = $request;
	}



	/**
	 * @return NPresenterRequest
	 */
	final public function getRequest()
	{
		return $this->request;
	}



	/**
	 * Sends response to output.
	 * @return void
	 */
	public function send()
	{
	}

}
