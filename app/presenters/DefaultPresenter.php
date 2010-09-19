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
            //NDebug::timer();
            //echo round(memory_get_usage()/1024,2)."kB<br />";
            $xmlControl=new XML2SQLParser(WWW_DIR.'/rz.xml');
            $xmlControl->buildDatabase();
                
                
	}

}
