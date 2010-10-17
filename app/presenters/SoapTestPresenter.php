<?php

/**
 * Description of Soaptester
 *
 * @author Honza
 */
class SoapTestPresenter extends BasePresenter {

    /** @persistent */
    public $app_id;
    /** @persistent */
    public $met_id;

    public function actionDefault($app_id) {
        if ($app_id > 0) {
            $app = @reset(Application::find(array("app_id" => $app_id)));
        }
        $this['header']->addTitle("SOAP Tester - aplikace " . $app['name']);
    }

    public function actionTest($met_id, $app_id) {
        if ($app_id > 0 && $met_id>0) {
            $app = @reset(Application::find(array("app_id" => $app_id)));
            $met = @reset(Operation::find(array("app_id"=>$app_id,"met_id"=>$met_id)));
        }
        $this['header']->addTitle("SOAP Tester - aplikace " . $app['name'] .'::'. $met['name']);
    }

    public function createComponentDatagrid($name) {
        $grid = new Datagrid($this, $name);
        $grid->setDataTable('rozvrh_main.operations_def');
        $grid->getSql()->where(array("app_id" => $this->getHttpRequest()->getQuery('app_id')));
        $grid->setColumns(array("met_id" => 'ID#', 'return' => 'Návratový typ', 'name' => 'Název', 'params' => 'Parametry'));
        $grid->setColumnFormat('params', DatagridFormatter::CALLBACK, function ($record) {
                    $data = unserialize($record);
                    foreach ($data as $item) {
                        echo $item['type'] . ' ' . $item['name'] . ' ';
                    }
                });
        $grid->addAction(array("action" => 'test', 'param' => 'met_id'), "Test", 'test');
    }

    public function createComponentForm($name) {
        $form=new NAppForm($this, $name);

        $revisions=array();

        foreach(Revision::find(array("app_id"=>$this->app_id)) as $rev) {
            $revisions[$rev->rev_id]=$rev->alias.' ('.$rev->db_name.')';
        }
        $form->addHidden('met_id',$this->met_id);
        $form->addSelect('revision', 'Provést nad revizí',$revisions);
        $operation=@reset(Operation::find(array("met_id"=>$this->met_id)));

        $params=unserialize($operation->params);
        foreach($params as $key=>$param) {
            $form->addText('param'.$key,'Parametr '.  $param['name'].' ('.$param['type'].')')->setRequired("Vyplňte hodnotu parametru.");
        }
        $form->addSubmit('s','Odeslat')->onClick[]=  callback($this,'testSoap');
    }

    public function testSoap(NSubmitButton $btn) {
        $values=$btn->form->values;
        $operation=Operation::getSQL(array("met_id"=>$values['met_id'],"rev_id"=>$values['revision']));
        $params=array();

        foreach (unserialize($operation->params) as $key=>$param) {
            $params[]=$values['param'.$key];
        }
        
        $handler=new ServiceHandler();
        SoapIdentity::$testCall=TRUE;
        $credintals=@reset(Application::find(array("app_id"=>  $this->app_id)));
        $handler->authenticate($credintals['login'], NULL);
        //$handler->necoCall("student",123456);
        dump(call_user_func_array(array($handler,$operation['name']), $params));

    }

}