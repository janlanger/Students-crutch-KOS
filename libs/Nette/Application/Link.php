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
 * Lazy encapsulation of NPresenterComponent::link().
 * Do not instantiate directly, use NPresenterComponent::lazyLink()
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Application
 */
class NLink extends NObject
{
	/** @var NPresenterComponent */
	private $component;

	/** @var string */
	private $destination;

	/** @var array */
	private $params;


	/**
	 * NLink specification.
	 * @param  NPresenterComponent
	 * @param  string
	 * @param  array
	 */
	public function __construct(NPresenterComponent $component, $destination, array $params)
	{
		$this->component = $component;
		$this->destination = $destination;
		$this->params = $params;
	}



	/**
	 * Returns link destination.
	 * @return string
	 */
	public function getDestination()
	{
		return $this->destination;
	}



	/**
	 * Changes link parameter.
	 * @param  string
	 * @param  mixed
	 * @return NLink  provides a fluent interface
	 */
	public function setParam($key, $value)
	{
		$this->params[$key] = $value;
		return $this;
	}



	/**
	 * Returns link parameter.
	 * @param  string
	 * @return mixed
	 */
	public function getParam($key)
	{
		return isset($this->params[$key]) ? $this->params[$key] : NULL;
	}



	/**
	 * Returns link parameters.
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}



	/**
	 * Converts link to URL.
	 * @return string
	 */
	public function __toString()
	{
		try {
			return $this->component->link($this->destination, $this->params);

		} catch (Exception $e) {
			NDebug::toStringException($e);
		}
	}

}
