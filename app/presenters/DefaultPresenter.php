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
            $this['header']->addTitle('Stažení XML');
        }

        public function createComponentDownloadForm() {
            $form=new NAppForm($this,'downloadForm');
            $form->addText('url','URL souboru')
                    ->setType('url')
                    ->setRequired('URL musí být vyplněno.');
            $form->addText('login','Fakultní login')->setRequired('Login musí být vyplněn.');
            $form->addPassword('password','Heslo pro service.felk.cvut.cz')->setRequired('Heslo musí být vyplněno.');
            $form->addSubmit('check','Zkontrolovat novou verzi');
            $form->addSubmit('download','Stáhnout');
            $form->setDefaults(array(
                'url'=>'https://service.felk.cvut.cz/kos/data/rz.xml'
            ));
            return $form;

        }

}
