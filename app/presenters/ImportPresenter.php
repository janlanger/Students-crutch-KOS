<?php

/**
 * Description of ImportPresenter
 *
 * @author Honza
 */
class ImportPresenter extends BasePresenter {

    protected function startup() {
        parent::startup();
        $this['header']->addTitle('Import');
    }

    public function actionDefault() {
        
    }

    

    public function createComponentIndexDefForm($name) {

        $form = new \Nette\Application\AppForm($this, $name);
        $manager = $this->application->getContext()->getService('IDatabaseManager');

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
                        ->addConditionOn($form[$table->name . '__' . $column->name . '_index'], \Nette\Forms\Form::EQUAL, 'foreign')
                        ->addRule(\Nette\Forms\Form::FILLED, 'Vyberte prosím vazbu cizího klíče.');


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

    public function processImport(\Nette\Forms\SubmitButton $btn) {
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
            if (\Nette\String::endsWith($key, 'index') && !is_null($value)) {

                $key = str_replace($table_names, $replace, $key);
                $table = \Nette\String::replace($key, "#([a-zA-Z_]*)\..*$#","$1");
                $column = \Nette\String::replace($key, "#[^\.]*\.(.*)_index#", "$1");
                
                
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