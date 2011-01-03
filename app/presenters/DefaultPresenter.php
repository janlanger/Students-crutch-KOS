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
class DefaultPresenter extends BasePresenter {

/*    public function actionImport() {
        $this['header']->addTitle('Import');
    }*/

    public function actionShowLog() {
        $this['header']->addTitle('Log');
    }

    public function createComponentLogGrid($name) {
        $grid=new Datagrid($this, $name);
        $grid->setDataTable(':main:log');
        $grid->setColumns(array('timestamp'=>'Čas','severity'=>'Z.','component'=>'Komponenta','message'=>'Zpráva'));
        $grid->setDefaultSort('timestamp', 'desc');
        $grid->setColumnFormat('timestamp', DatagridFormatter::DATE);
        $grid->setColumnFormat('severity', DatagridFormatter::CALLBACK,  function ($record) {
            
            if(in_array($record, array("notice",'info','warning','error','critical'))) {
                echo Nette\Web\Html::el("img")->src("/images/icons/".$record.".png");
            }
            else echo $record;
        });

        $grid->setItemsPerPage(25);
        
    }


}
