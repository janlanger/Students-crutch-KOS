<?php

/**
 * Description of ImportPresenter
 *
 * @author Honza
 */
class ImportPresenter extends BasePresenter {

	/**
	 * (non-phpDoc)
	 *
	 * @see Nette\Application\Presenter#startup()
	 */
	protected function startup() {
		parent::startup();
                $this['header']->addTitle('Import');
	}

        public function actionDefault() {
            /*$this->template->form=*/$this->createComponentFileChooseForm();
        }

        public function actionAnalyze() {

        }

        public function createComponentFileChooseForm() {
            $form=new NAppForm($this, 'fileChooseForm');
            $files=File::getImportableFiles();
            
            $items=array();
            foreach($files as $file) {
                $items[$file['filename']]='';
            }
            $this->template->files=$files;
            $form->addRadioList('choice', 'Vyberte soubor k importu.', $items)->setRequired('Musíte vybrat soubor k importu.');
            $form->addSubmit('send', 'Odeslat')->onClick[]=  callback($this, 'analyze');
        }
        
}