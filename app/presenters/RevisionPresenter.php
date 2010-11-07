<?php

/**
 * Description of RevisionPresenter
 *
 * @author Honza
 */
class RevisionPresenter extends BasePresenter {

    /** @persistent */
    public $app_id=0;

	

	public function actionDefault($app_id) {
            $application=@reset(Application::find(array("app_id"=>$app_id)));
            $this['header']->addTitle("Správa revizí pro aplikaci ".$application->name);
	}

        public function actionCreate() {
            $this['header']->addTitle('Vytvoření revize');
        }

        public function actionEdit() {

        }

        public function actionDelete() {

        }


        public function createComponentDatagrid($name) {
            
            $grid=new Datagrid($this, $name);
            $grid->setDataTable(":main:revision");
            $grid->setColumns(array("rev_id"=>'ID#','db_name'=>'Databáze','alias'=>'Alias','created_time'=>'Čas vytvoření','isMain'=>'Výchozí'));
            $grid->getSql()->where(array("app_id"=>$this->app_id));
            $grid->setColumnFormat('created_time', DatagridFormatter::DATE);
            $grid->setColumnFormat('isMain', DatagridFormatter::CHECKBOX_YES_NO);
            $grid->addAction(array('action'=>'Revision:edit','param'=>'rev_id'), 'Upravit','edit');
            $grid->addAction(array('action'=>'Revision:delete','param'=>'rev_id'), 'Smazat','delete')->setConfirmQuestion('Smazat', 'Opravdu chcete smazat tuto revizi? Bude odstraněna celá svázaná databáze.');
        }

        protected function createComponentCreateForm($name) {
            $form=new NAppForm($this, $name);
            $group=$form->addGroup('Základní nastavení');
            $form->addText('name','Název revize')->setRequired('Vyplňte prosím název revize');
            //$form->addText('db_name','Jméno databáze')->setRequired('Vyplňte prosím jméno databáze'); //automat
            $form->addCheckbox('isMain','Používat jako výchozí');

            $group=$form->addGroup('Obsah revize')->setOption('description', 'Vyberte tabulky, které chcete zkopírovat do revize.');

            $tables=Revision::getAvaiableTables();
            foreach($tables as $table) {
                $form->addCheckbox($table, $table);
            }
            $form->addGroup();
            $form->addHidden("app_id",  $this->app_id);
            $form->addSubmit('s','Odeslat')->onClick[]=callback($this,'createRevision');

            return $form;

        }

        public function createRevision(NSubmitButton $bnt) {
            $values=$bnt->getForm()->getValues();

            $tables=Revision::getAvaiableTables();
            $tbl=array();
            foreach($values as $key=>$value) {
                if(in_array($key, $tables) && $value == TRUE) {
                    $tbl[]=$key;
                }
            }
            $database_name="rozvrh_".NString::webalize(@reset(Application::find(array("app_id"=>$values['app_id'])))->name)."_".$values['name'].'_'.date("Ymd");
            try {
                Revision::create($values['name'],$values['app_id'],$values['isMain'],$database_name,$tbl);
                $this->flashMessage('Revize byla úspěšně vytvořena.', 'success');
                $this->redirect('default');
            }
            catch (ModelException $e) {
                $this->flashMessage('Revizi se nepodařilo vytvořit. Chyba: '.$e->getMessage(), 'error');
                NDebug::log($e);
            }
        }
}