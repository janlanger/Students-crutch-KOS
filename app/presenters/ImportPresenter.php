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
        /* $this->template->form= */$this->createComponentFileChooseForm();
    }

    public function actionAnalyze($file) {
        try {
            $this['importer']->setFile($file);
            $this->template->tables = $this['importer']->getStructure();
        } catch (Exception $e) {
            $this->flashMessage($e->getMessage(), 'error');
            $this->redirect('Import:'); //redirect back
        }
    }

    public function createComponentFileChooseForm() {
        $form = new NAppForm($this, 'fileChooseForm');
        $files = File::getImportableFiles();

        $items = array();
        foreach ($files as $file) {
            $items[$file['filename']] = '';
        }

        $this->template->files = $files;
        $form->addRadioList('choice', 'Vyberte soubor k importu.', $items)->setRequired('Musíte vybrat soubor k importu.');
        $form->addSubmit('send', 'Odeslat')->onClick[] = callback($this, 'proccessFileChoose');
    }

    public function proccessFileChoose(NSubmitButton $button) {
        $values = $button->getForm()->getValues();

        $this->redirect('Import:analyze', array('file' => $values['choice']));
    }

    public function createComponentImporter() {
        return $this->getApplication()->getContext()->getService('IImporter');
    }

    public function createComponentColumnDefForm($name) {

        $form = new NAppForm($this, $name);
        $importer = $this['importer'];
        /* @var $importer XMLImporter */
        $tables = $importer->tables;

        $items = array();
        /* @var $table XMLi_Entity */
        foreach ($tables as $table) {
            foreach ($table->columns as $column) {
                $name = $table->name . '.' . $column->name;
                if (!array_key_exists($name, $items)) {
                    $items[$name] = $name;
                }
            }
        }
        foreach ($tables as $table) {

            foreach ($table->columns as $column) {
                if($column->name=='hash')                    continue;
                $form->addSelect($table->name . '__' . $column->name . '_index', '',
                                array("none" => "-------", "primary" => 'PRIMARY', "index" => 'INDEX', "foreign" => 'FOREIGN'))
                        ->skipFirst();

                $items2 = $items;
                unset($items2[$table->name . '.' . $column->name]);
                array_unshift($items2, "-------");
                $form->addSelect($table->name . '__' . $column->name . '_foreign', '', $items2)
                        ->skipFirst()
                        ->addConditionOn($form[$table->name . '__' . $column->name . '_index'], NForm::EQUAL, 'foreign')
                        ->addRule(NForm::FILLED, 'Vyberte prosím vazbu cizího klíče.');


                //index guessing

                if (in_array($column->name, $table->getGuessedIndexes())) {
                    $form[$table->name . '__' . $column->name . '_index']->setDefaultValue("index");
                }
                if (in_array($column->name, $table->getGuessedPrimaryKeys())) {
                    $form[$table->name . '__' . $column->name . '_index']->setDefaultValue("primary");
                }

                //TODO: foreign keys guess
            }
        }
        $form->addHidden('file', basename($importer->file));
        $form->addText('db_name', 'Název databáze')->setRequired('Vyplňte prosím název databáze')->setDefaultValue('rozvrh-' . date("Y-m-d-H-i"));
        $form->addSubmit('send', 'Importovat')->onClick[] = callback($this, 'processImport');

        return $form;
    }

    public function processImport(NSubmitButton $btn) {
        $values = $btn->getForm()->getValues();
        $importer = $this['importer'];
        /* @var $importer XMLImporter */
        $importer->setFile($values['file']);
        $tables = $importer->tables;
        /* @var $tables XMLi_Entity */
        $table_names = array_keys($tables);
        $replace = array();
        foreach ($table_names as $key => $table_name) {
            $replace[$key] = $table_name . '.';
            $table_names[$key] = $table_name . '__';
        }
        foreach ($values as $key => $value) {
            if (NString::endsWith($key, 'index') && !is_null($value)) {

                $key = str_replace($table_names, $replace, $key);
                $table = NString::replace($key, "#([a-zA-Z_]*)\..*$#","$1");
                $column = NString::replace($key, "#[^\.]*\.(.*)_index#", "$1");
                
                
                switch ($value) {
                    case 'primary':
                        $tables[$table]->addPrimary($column);
                        break;
                    case 'index':
                        $tables[$table]->addIndex($column);
                        break;
                    case 'foreign':
                        $reference=$values[$table.'__'.$column.'_foreign'];
                        $tables[$table]->addForeign($column,$reference);
                        break;

                    default:
                        throw new InvalidArgumentException();
                        break;
                }
            }
        }
        
        try {
            $importer->buildDatabase($values['db_name']);

            $this->flashMessage('Import proběhl úspěšně.'.$importer->getReport(),'success');
            $this->redirect("Import:");
        } catch (InvalidStateException $e) {
            
            $this->flashMessage($e->getMessage(),'error');
        } catch (DibiException $e) {
            $this->flashMessage($e->getMessage(),'error');
            $this->flashMessage("SQL: ".$e->getSql(),'error');            
        }
    }

}