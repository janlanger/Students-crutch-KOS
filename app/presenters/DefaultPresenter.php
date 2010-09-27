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
            //echo round(memory_get_usage()/1024,2)."kB<br />";
            $xmlControl=new XML2SQLParser(WWW_DIR.'/xml/rz-2010-09-20.xml');
            $xmlControl->buildDatabase("rozvrh-01");
            $this->template->result='Import dokončen - '.NDebug::timer().'sec';
            //$xmlControl=new XML2SQLParser(WWW_DIR.'/xml/rz-2010-06-17.xml');
            //$xmlControl->buildDatabase("rozvrh-02");
            /*$xmlControl=new XML2SQLParser(WWW_DIR.'/xml/rz-2010-09-20.xml');
            $xmlControl->buildDatabase("rozvrh-01");*/
                
                
	}

}
