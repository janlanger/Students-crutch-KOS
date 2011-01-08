<?php

use Nette\Forms\Form;

/**
 * Description of RevisionPresenter
 *
 * @author Honza
 */
class RevisionPresenter extends BasePresenter {

    /** @persistent */
    public $app_id = 0;

    public function actionDefault($app_id) {
        $application = @reset(Application::find(array("app_id" => $app_id)));
        $this['header']->addTitle("Správa revizí pro aplikaci " . $application->name);
    }

    public function actionCreate() {
        Addons\Forms\FormMacros::register();
        $this['header']->addTitle('Vytvoření revize');
        $this['createForm']; //inits tamplate
    }

    public function actionDetail($rev_id) {
        $this['header']->addTitle('Detail revize');
        $rev = Revision::find(array("app_id" => $this->app_id, "rev_id" => $rev_id));
        if (count($rev) != 1) {
            $this->flashMessage('Neznámá revize.', 'error');
            $this->redirect('default');
        }
        $rev = @reset($rev);
        $this['editForm']->setDefaults(array(
            "name" => $rev->alias,
            "rev_id" => $rev_id,
            "isMain" => $rev->isMain
        ));
        $this->template->revision = $rev;
    }

    public function actionCompare() {
         Addons\Forms\FormMacros::register(); // register form macros
    }

    protected function createComponentRevisionChoose($name) {
        $form=new Nette\Application\AppForm($this, $name);
        $revisions=Revision::find(array("app_id"=>$this->app_id));
        $data=array();
        foreach ($revisions as $v) {
            $data[$v->rev_id]=$v->alias;
        }
        $form->addSelect('revision1', 'Porovnat ',$data);
        $form->addSelect('revision2', ' s ',$data);
        $form->addSubmit('send','Porovnat');
        $presenter = $this;
        $form->onSubmit[]=function ($form) use ($presenter) {
            //$form=$button->getForm();
            $values=$form->getValues();
            if($values['revision1']==$values['revision2']) {
                $form->addError('Proč chcete porovnávat stejné revize?');
                return;
            }
            $presenter['compare']->revisions=array($values['revision1'], $values['revision2']);
        };

    }

    protected function createComponentCompare($name) {
        return new RevisonComparator($this, $name);
    }

    public function actionDelete($rev_id) {
        $rev = Revision::find(array("app_id" => $this->app_id, "rev_id" => $rev_id));
        if (count($rev) != 1) {
            $this->flashMessage('Neznámá revize.', 'error');
        } else {
            try {
                $rev = @reset($rev);
                $rev->delete();
                $this->flashMessage('Revize byla smazána.', 'success');
            } catch (ModelException $e) {
                $this->flashMessage($e->getMessage(), 'error');
                \Nette\Debug::log($e);
            }
        }
        $this->redirect('default');
    }

    public function createComponentDatagrid($name) {

        $grid = new Datagrid($this, $name);
        $grid->setDataTable(":main:revision");
        $grid->setColumns(array("rev_id" => 'ID#', 'alias' => 'Alias','exist'=>'Vytvořena',/* 'created_time' => 'Čas vytvoření', */'isMain' => 'Výchozí'));
        $grid->getSql()->columns=array("rev_id" , 'db_name', 'alias', 'db_name'=>'exist', 'created_time', 'isMain');
        $grid->getSql()->where(array("app_id" => $this->app_id));
        //$grid->setColumnFormat('created_time', DatagridFormatter::DATE);
        $grid->setColumnFormat('isMain', DatagridFormatter::CHECKBOX_YES_NO);
        $grid->setColumnFormat('exist', DatagridFormatter::CALLBACK, function ($record) {
            $db=\Nette\Environment::getService("IDatabaseManager");
            $img=\Nette\Web\Html::el('img');
            try{
                $db->setDefaultDatabase($record);
                $img->src="/images/icons/accept.png";
                
            } catch(DibiException $e) {
                $img->src="/images/icons/critical.png";
            }
            echo $img;
        });

        $grid->addAction(array('action' => 'Revision:detail', 'param' => 'rev_id'), 'Detail', 'edit');
        $grid->addAction(array('action' => 'Revision:delete', 'param' => 'rev_id'), 'Smazat', 'delete')
                ->setConfirmQuestion('Smazat', 'Opravdu chcete smazat tuto revizi? Bude odstraněna celá svázaná databáze.')
                ->setValidator(function($row, $action) {
                            if ($row['db_name'] == 'rozvrh_live')
                                return false;
                            return true;
                        });
    }

    protected function createComponentCreateForm($name) {
        $form = new \Nette\Application\AppForm($this, $name);

        $render = $form->getRenderer();
        /* @var $render \Nette\Forms\DefaultFormRenderer  */
        /* $render->wrappers['controls']['container']=NULL;
          $render->wrappers['pair']['container']=NULL;
          $render->wrappers['controls']['container'] = 'dl';
          $render->wrappers['control']['container'] = 'dd';
          $render->wrappers['label']['container'] = 'dt'; */



        $group = $form->addGroup('Základní nastavení');
        $form->addText('name', 'Název revize')->setRequired('Vyplňte prosím název revize');
        //$form->addText('db_name','Jméno databáze')->setRequired('Vyplňte prosím jméno databáze'); //automat
        $form->addCheckbox('isMain', 'Používat jako výchozí');

        $main = $form->addGroup('Obsah revize')->setOption('description', 'Vyberte tabulky, které chcete zkopírovat do revize.');

        $tables = Revision::getAvaiableTables();
        $this->template->tables = $tables;
        foreach ($tables as $table_name => $table) {
            $form->addCheckbox($table_name, 'Zahrnout')->addCondition(Form::FILLED, FALSE)->toggle($table_name);
            $group = $form->addGroup($table_name, TRUE)->setOption('container', \Nette\Web\Html::el('div')->id($table_name));

            $columns = $table['columns'];
            foreach ($columns as $column => $v) {
                $key = $table_name . '__' . $column;
                $item = $form->addCheckbox($key, $column.(isset($table['foreign'][$column])?' (-> '.$table['foreign'][$column].')':''));

                if (isset($table['primary'][$column])) {
                    $item->setDefaultValue(TRUE);
                    $item->setDisabled();
                }
                $group->add($item);
            }
            $form->addRadioList($table_name.'_update_schema', NULL, array(
                'none'=>'Neaktualizovat',
                "structure"=>'Udržovat tabulku kompletně aktuální.',
                'data'=>'Udržovat pouze data aktuální.'
                ))->setDefaultValue('none')
                    ->addCondition(Form::EQUAL,'data')->toggle($table_name . 'data-max');
            $form->addText($table_name . '_update_data_max')
                    ->setType('number')
                    ->setDefaultValue(-1)
                    ->setOption('description', 'Maximální počet změn v datech pro provedení automatické aktualizace.');
            $form->addText($table_name . '_condition', 'Omezující podmínka:');

            $form->setCurrentGroup($main);
        }
        $form->addGroup();
        $form->addHidden("app_id", $this->app_id);
        $form->addSubmit('s', 'Odeslat')->onClick[] = callback($this, 'createRevision');

        return $form;
    }

    public function createRevision(\Nette\Forms\SubmitButton $bnt) {
        $values = $bnt->getForm()->getValues();
       
        $tables = Revision::getAvaiableTables();
        $tbl = array();
        
        foreach ($tables as $table => $items) {
            if(isset($values[$table]) && $values[$table]) {
                $tbl[$table]=array();
                if(isset($items['primary'])) {
                    foreach($items['primary'] as $col => $v) {
                        $tbl[$table]['columns'][$col]=1;
                    }
                }
                foreach ($items['columns'] as $column => $value) {
                    if(isset($values[$table.'__'.$column]) && $values[$table.'__'.$column]) {
                        $tbl[$table]['columns'][$column]=1;
                    }
                    
                }
                if(!isset($tbl[$table]['columns']) || count($tbl[$table]['columns'])<1) {
                    $bnt->getForm()->addError('Tabulka '.$table.' nemá vybraný žádný sloupec. Buď nějaký vyberte, nebo tabulku nezařazujte do revize.');
                }
                if(isset($values[$table.'_update_schema']) && $values[$table.'_update_schema']) {
                    $tbl[$table]['schema']=$values[$table.'_update_schema'];
                    if($values[$table.'_update_schema']=='data') {
                        if(isset($values[$table.'_update_data_max'])) {
                            if($values[$table.'_update_data_max'] == 0) {
                                $bnt->getForm()->addError('Nastavili jste, že u tabulky '. $table .' se má aktualizovat maximálně 0 řádků. Takové schéma je stejné jako neaktualizovat vůbec, vyberte prosím toto schéma.');
                            }
                            else {
                                $tbl[$table]['max-changes']=$values[$table.'_update_data_max'];
                            }
                        }
                    }
                }
                if(isset($values[$table.'_condition'])) {
                    $tbl[$table]['condition']=$values[$table.'_condition'];
                }
            }
        }
        if($bnt->getForm()->hasErrors()) {
            return;
        }
        
        $database_name = "rozvrh_" . \Nette\String::webalize(@reset(Application::find(array("app_id" => $values['app_id'])))->name) . "_" . $values['name'] . '_' . date("Ymd");
        try {
            Revision::create($values['name'], $values['app_id'], $values['isMain'], $database_name, $tbl);
            $this->flashMessage('Definice revize byla uložena. Revize jsou vytvářeny automatickým skriptem spuštěným v noci.', 'success');
            $this->redirect('default');
        } catch (ModelException $e) {
            $this->flashMessage('Revizi se nepodařilo vytvořit. Chyba: ' . $e->getMessage(), 'error');
            \Nette\Debug::log($e);
        }
    }

    protected function createComponentEditForm($name) {
        $form = new \Nette\Application\AppForm($this, $name);
        $form->addText('name', 'Název revize')->setRequired('Vyplňte prosím název revize');
        //$form->addText('db_name','Jméno databáze')->setRequired('Vyplňte prosím jméno databáze'); //automat
        $form->addCheckbox('isMain', 'Používat jako výchozí');
        $form->addHidden('rev_id');
        $form->addSubmit('s', 'Odeslat')->onClick[] = callback($this, 'editRevision');
    }

    public function editRevision(\Nette\Forms\SubmitButton $bnt) {
        $values = $bnt->getForm()->getValues();
        $revision = Revision::find(array("rev_id" => $values['rev_id']));
        if (count($revision) != 1) {
            $this->flashMessage('Neznámá revize.', 'error');
        } else {
            $revision = @reset($revision);
            try {
                $revision->setValues($values)->save();
                $this->flashMessage('Revize byla upravena.', 'success');
                $this->redirect('default');
            } catch (ModelException $e) {
                $this->flashMessage($e->getMessage(), 'error');
                \Nette\Debug::log($e);
            }
        }
    }

}