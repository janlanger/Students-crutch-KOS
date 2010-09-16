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
 * Signal exception.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
class NBadSignalException extends NBadRequestException
{
	/** @var int */
	protected $defaultCode = 403;

}
