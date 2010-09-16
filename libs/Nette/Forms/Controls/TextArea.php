<?php

/**
 * Nette Framework
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @license    http://nette.org/license  Nette license
 * @link       http://nette.org
 * @category   Nette
 * @package    Nette\Forms
 */



/**
 * Multiline text input control.
 *
 * @copyright  Copyright (c) 2004, 2010 David Grudl
 * @package    Nette\Forms
 */
class NTextArea extends NTextBase
{

	/**
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  height of the control in text lines
	 */
	public function __construct($label = NULL, $cols = NULL, $rows = NULL)
	{
		parent::__construct($label);
		$this->control->setName('textarea');
		$this->control->cols = $cols;
		$this->control->rows = $rows;
		$this->value = '';
	}



	/**
	 * Generates control's HTML element.
	 * @return NHtml
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$control->setText($this->getValue() === '' ? $this->translate($this->emptyValue) : $this->value);
		return $control;
	}

}
