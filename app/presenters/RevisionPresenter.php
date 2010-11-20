<?php

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
        $this['header']->addTitle('Vytvoření revize');
    }

    public function actionEdit($rev_id) {
        $this['header']->addTitle('Úprava revize');
        $rev = Revision::find(array("app_id" => $this->app_id, "rev_id" => $rev_id));
        if (count($rev) != 1) {
            $this->flashMessage('Neznámá revize.', 'error');
            $this->redirect('default');
        }
        $rev = @reset($rev);
        $this['editForm']->setDefaults(array(
            "name"=>$rev->alias,
            "rev_id"=>$rev_id,
            "isMain"=>$rev->isMain
        ));
        $this->template->revision=$rev;
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
        $grid->setColumns(array("rev_id" => 'ID#', 'db_name' => 'Databáze', 'alias' => 'Alias', 'created_time' => 'Čas vytvoření', 'isMain' => 'Výchozí'));
        $grid->getSql()->where(array("app_id" => $this->app_id));
        $grid->setColumnFormat('created_time', DatagridFormatter::DATE);
        $grid->setColumnFormat('isMain', DatagridFormatter::CHECKBOX_YES_NO);
        $grid->addAction(array('action' => 'Revision:edit', 'param' => 'rev_id'), 'Upravit', 'edit');
        $grid->addAction(array('action' => 'Revision:delete', 'param' => 'rev_id'), 'Smazat', 'delete')
                ->setConfirmQuestion('Smazat', 'Opravdu chcete smazat tuto revizi? Bude odstraněna celá svázaná databáze.')
            ->setValidator(function($row,$action) {
                if($row['db_name']=='rozvrh_live') return false;
                return true;
            });
    }

    protected function createComponentCreateForm($name) {
        $form = new \Nette\Application\AppForm($this, $name);
        $group = $form->addGroup('Základní nastavení');
        $form->addText('name', 'Název revize')->setRequired('Vyplňte prosím název revize');
        //$form->addText('db_name','Jméno databáze')->setRequired('Vyplňte prosím jméno databáze'); //automat
        $form->addCheckbox('isMain', 'Používat jako výchozí');

        $group = $form->addGroup('Obsah revize')->setOption('description', 'Vyberte tabulky, které chcete zkopírovat do revize.');

        $tables = Revision::getAvaiableTables();
        foreach ($tables as $table) {
            $form->addCheckbox($table, $table);
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
        foreach ($values as $key => $value) {
            if (in_array($key, $tables) && $value == TRUE) {
                $tbl[] = $key;
            }
        }
        $database_name = "rozvrh_" . \Nette\String::webalize(@reset(Application::find(array("app_id" => $values['app_id'])))->name) . "_" . $values['name'] . '_' . date("Ymd");
        try {
            Revision::create($values['name'], $values['app_id'], $values['isMain'], $database_name, $tbl);
            $this->flashMessage('Revize byla úspěšně vytvořena.', 'success');
            $this->redirect('default');
        } catch (ModelException $e) {
            $this->flashMessage('Revizi se nepodařilo vytvořit. Chyba: ' . $e->getMessage(), 'error');
            \Nette\Debug::log($e);
        }
    }

    protected function createComponentEditForm($name) {
        $form=new \Nette\Application\AppForm($this, $name);
        $form->addText('name', 'Název revize')->setRequired('Vyplňte prosím název revize');
        //$form->addText('db_name','Jméno databáze')->setRequired('Vyplňte prosím jméno databáze'); //automat
        $form->addCheckbox('isMain', 'Používat jako výchozí');
        $form->addHidden('rev_id');
        $form->addSubmit('s','Odeslat')->onClick[]=callback($this,'editRevision');
    }

    public function editRevision(\Nette\Forms\SubmitButton $bnt) {
        $values = $bnt->getForm()->getValues();
        $revision = Revision::find(array("rev_id"=>$values['rev_id']));
        if(count($revision)!=1) {
            $this->flashMessage('Neznámá revize.', 'error');
        } else {
            $revision=@reset($revision);
            try {
                $revision->setValues($values)->save();
                $this->flashMessage('Revize byla upravena.','success');
                $this->redirect('default');
            } catch (ModelException $e) {
                $this->flashMessage($e->getMessage(),'error');
                \Nette\Debug::log($e);
            }
        }

    }

}