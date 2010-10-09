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

        public function actionAnalyze($file) {
            

            $this['importer']->setFile($file);
            $this['importer']->analyzeStructure();
            $this->template->generateForeignForm=TRUE;
            
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
            $form->addSubmit('send', 'Odeslat')->onClick[]=  callback($this, 'proccessFileChoose');
        }

        public function proccessFileChoose(NSubmitButton $button) {
            $values=$button->getForm()->getValues();
            $this->redirect('Import:analyze',array('file'=>$values['choice']));
            
        }

        public function createComponentImporter() {
            return $this->getApplication()->getContext()->getService('IImporter');
        }

        public function createComponentForeignKeysForm($name) {

            $form = new NAppForm($this, $name);
            $importer=$this['importer'];
            /* @var $importer XMLImporter */
            $tables=$importer->tables;

            $items=array();
            /* @var $table XMLi_Entity */
            foreach($tables as $table) {
                
                $indexes=array_merge($table->primaryKeys,$table->indexes);
                foreach($indexes as $index) {
                    $name=$table->name.'.'.$index;
                    
                    if(!array_key_exists($index, $items)) {
                        $items[]=$name;
                    }

                }

            }
            $items2=$items;
            array_unshift($items2, '--nemá vazbu--');
            
            //TODO: odhadování vazeb
            $form->addHidden('items',1);
            $form->addSelect('owner1', 'Vlastník' , $items);
            $form->addSelect('inverse1', 'Obraz' , $items2);
            if($form->isSubmitted()) {
                $num=$form['items']->getValue();
                for($i=2;$i<=$num;$i++) {
                    $form->addSelect('owner'.$i, 'Vlastník' , $items);
                    $form->addSelect('inverse'.$i, 'Obraz' , $items2);
                }
            }
            
            
            $form->addSubmit('addRow','Přidat další')->setValidationScope(FALSE)->onClick[]=  callback($this,'addRow');
            $form->addSubmit('send','Importovat')->onClick[]=callback($this,'processImport');

            return $form;

        }

        public function addRow(NSubmitButton $btn) {
            $form=$btn->getForm();

            $items=$form['items']->getValue();
            $form['items']->setValue(++$items);
            
            $form->addSelect('owner'.$items, 'Vlastník' , $form['owner1']->getItems());
            $form->addSelect('inverse'.$items, 'Obraz' , $form['inverse1']->getItems());
            $form->addError('Přidáno.');
        }
        
}