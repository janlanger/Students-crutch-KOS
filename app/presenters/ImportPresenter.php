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
        /* @var $manager IDatabaseManager */
        $manager = $this->application->getContext()->getService('IDatabaseManager');
        $manager->setDefaultDatabase(Nette\Environment::getConfig('xml')->liveDatabase);
        $tables = dibi::getDatabaseInfo()->getTables();

        $items = array();

        foreach ($tables as $table) {
            foreach ($table->columns as $column) {
                $name = $table->name . '.' . $column->name;
                if (!array_key_exists($name, $items)) {
                    $items[$name] = $name;
                }
            }
        }
        foreach ($tables as $table) {
            $form->addGroup($table->name);
            foreach ($table->columns as $column) {
                if ($column->name == 'hash')
                    continue;
                $form->addSelect($table->name . '__' . $column->name, $column->name,
                                array("none" => "-------", "primary" => 'PRIMARY', "index" => 'INDEX', "foreign" => 'FOREIGN'))
                        ->skipFirst()->getControlPrototype()->class="type";
                $items2 = $items;
                unset($items2[$table->name . '.' . $column->name]);
                array_unshift($items2, "-------");
                $form->addSelect($table->name . '__' . $column->name . '_foreign', '', $items2)
                        ->skipFirst()
                        ->addConditionOn($form[$table->name . '__' . $column->name], \Nette\Forms\Form::EQUAL, 'foreign')
                        ->addRule(\Nette\Forms\Form::FILLED, 'Vyberte prosím vazbu cizího klíče.');
            }
        }

        $form->addSubmit('send', 'Odeslat')->onClick[] = callback($this, 'processImport');

        //load index definition
        $data = IndexDefinition::find();
        $defaults = array();
        foreach ($data as $item) {
            $key = $item->table . '__' . $item->column;
            $defaults[$key] = $item->index_type;
            if ($item->index_type == 'foreign') {
                $defaults[$key . '_foreign'] = $item->foreign;
            }
        }
        $form->setDefaults($defaults);

        return $form;
    }

    public function processImport(\Nette\Forms\SubmitButton $btn) {
        $values = $btn->getForm()->getValues();

        $tables = array();

        foreach ($values as $key => $value) {
            if ($value == NULL || Nette\String::endsWith($key, "foreign")) {
                continue;
            }
            $column = explode("__", $key);

            $tables[$key] = new IndexDefinition(array(
                        "table" => $column[0],
                        "column" => $column[1],
                        "index_type" => $value
                    ));


            if ($value == 'foreign') {
                $tables[$key]['foreign'] = $values[$key . '_foreign'];
            } else {
                $tables[$key]['foreign'] = NULL;
            }
        }
        try {
            IndexDefinition::saveAll($tables);
            $this->flashMessage("Nastavení bylo uloženo.", self::FLASH_SUCCESS);
            $this->redirect("this");
        } catch (ModelException $e) {
            $this->flashMessage('Uložení se nezdařilo.', self::FLASH_ERROR);
            \Nette\Debug::log($e);
        }
    }

}