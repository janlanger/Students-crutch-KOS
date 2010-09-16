<?php

/**
 * My NApplication
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */



/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class DefaultPresenter extends BasePresenter
{

	public function actionAnalyze()
	{
            NDebug::timer();
            $xmlControl=new XML2SQL(WWW_DIR.'/rz.xml');
            $xmlControl->buildDatabase();
                
                
	}

}
