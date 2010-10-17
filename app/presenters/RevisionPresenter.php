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


        public function createComponentDatagrid($name) {
            
            $grid=new Datagrid($this, $name);
            $grid->setDataTable("rozvrh_main.revision");
            $grid->setColumns(array("rev_id"=>'ID#','db_name'=>'Databáze','alias'=>'Alias','created_time'=>'Čas vytvoření','isMain'=>'Výchozí'));
            $grid->getSql()->where(array("app_id"=>$this->app_id));
            $grid->setColumnFormat('created_time', DatagridFormatter::DATE);
            $grid->setColumnFormat('isMain', DatagridFormatter::CHECKBOX_YES_NO);
            $grid->addAction(array('action'=>'Revision:edit','param'=>'rev_id'), 'Upravit','edit');
            $grid->addAction(array('action'=>'Revision:delete','param'=>'rev_id'), 'Smazat','delete');
        }
}