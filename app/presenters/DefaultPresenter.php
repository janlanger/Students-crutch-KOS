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

        public function actionDownload() {
            
        }

        public function createComponentDownloadForm() {
            $form=new NAppForm($this,'downloadForm');
            $form->addText('url','URL souboru')
                    ->setRequired();
            $form->addText('login','Fakultní login')->setRequired();
            $form->addPassword('password','Heslo pro service.felk.cvut.cz')->setRequired();
            $form->addSubmit('Odeslat');

            $form->setDefaults(array(
                'url'=>'https://service.felk.cvut.cz/kos/data/rz.xml'
            ));
            return $form;

        }

}
