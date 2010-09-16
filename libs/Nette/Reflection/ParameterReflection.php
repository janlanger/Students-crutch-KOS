<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Reflection
 */



/**
 * Reports information about a method's parameter.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Reflection
 */
class NParameterReflection extends ReflectionParameter
{
	/** @var mixed */
	private $function;


	public function __construct($function, $parameter)
	{
		parent::__construct($this->function = $function, $parameter);
	}



	/**
	 * @return NClassReflection
	 */
	public function getClass()
	{
		return ($ref = parent::getClass()) ? new NClassReflection($ref->getName()) : NULL;
	}



	/**
	 * @return NClassReflection
	 */
	public function getDeclaringClass()
	{
		return ($ref = parent::getDeclaringClass()) ? new NClassReflection($ref->getName()) : NULL;
	}



	/**
	 * @return NMethodReflection | NFunctionReflection
	 */
	public function getDeclaringFunction()
	{
		return is_array($this->function) ? new NMethodReflection($this->function[0], $this->function[1]) : new NFunctionReflection($this->function);
	}



	/********************* NObject behaviour ****************d*g**/



	/**
	 * @return NClassReflection
	 */
	public function getReflection()
	{
		return new NClassReflection($this);
	}



	public function __call($name, $args)
	{
		return NObjectMixin::call($this, $name, $args);
	}



	public function &__get($name)
	{
		return NObjectMixin::get($this, $name);
	}



	public function __set($name, $value)
	{
		return NObjectMixin::set($this, $name, $value);
	}



	public function __isset($name)
	{
		return NObjectMixin::has($this, $name);
	}



	public function __unset($name)
	{
		throw new MemberAccessException("Cannot unset the property {$this->reflection->name}::\$$name.");
	}

}
