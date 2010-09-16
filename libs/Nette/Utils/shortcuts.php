<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette
 */

// no namespace



/**
 * NCallback factory.
 * @param  mixed   class, object, function, callback
 * @param  string  method
 * @return NCallback
 */
function callback($callback, $m = NULL)
{
	return ($m === NULL && $callback instanceof NCallback) ? $callback : new NCallback($callback, $m);
}



/**
 * NDebug::dump shortcut.
 */
function dump($var)
{
	foreach (func_get_args() as $arg) NDebug::dump($arg);
	return $var;
}
