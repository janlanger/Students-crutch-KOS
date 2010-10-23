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
        if ($app_id > 0 && $met_id > 0) {
            $app = @reset(Application::find(array("app_id" => $app_id)));
            $met = @reset(Operation::find(array("app_id" => $app_id, "met_id" => $met_id)));
        }
        $this['header']->addTitle("SOAP Tester - aplikace " . $app['name'] . '::' . $met['name']);
    }

    public function createComponentDatagrid($name) {
        $grid = new Datagrid($this, $name);
        $grid->setDataTable(':main:operations_def');
        $grid->setColumns(array("met_id" => 'ID#', 'return' => 'Návratový typ', 'name' => 'Název', 'params' => 'Parametry'));
        $grid->setColumnFormat('params', DatagridFormatter::CALLBACK, function ($record) {
            if($record=="") {
                echo  "žádné";
                return;
            }
                    $data = unserialize($record);
                    foreach ($data as $item) {
                        echo $item['type'] . ' ' . $item['name'] . ' ';
                    }
                });
        $grid->addAction(array("action" => 'test', 'param' => 'met_id'), "Test", 'test');

        $grid->getSql()->where(array("app_id" => $this->app_id));
    }

    public function createComponentForm($name) {
        $form = new NAppForm($this, $name);

        $revisions = array();

        foreach (Revision::find(array("app_id" => $this->app_id)) as $rev) {
            $revisions[$rev->rev_id] = $rev->alias . ' (' . $rev->db_name . ')';
        }
        $form->addHidden('met_id', $this->met_id);
        $form->addSelect('revision', 'Provést nad revizí', $revisions);
        $operation = @reset(Operation::find(array("met_id" => $this->met_id)));
        if ($operation instanceof Operation) {

            $params = unserialize($operation->params);
            foreach ($params as $key => $param) {
                if($param['type']=='array') {
                    $form->addTextArea('param'.$key,  'Parametr ' . $param['name'] . ' (' . $param['type'] . ')')
                            ->setOption('description', 'Jednotlivé hodnoty oddělte čárkou.')
                            ->setRequired("Vyplňte hodnotu parametru.");
                }
                else {
                    $form->addText('param' . $key, 'Parametr ' . $param['name'] . ' (' . $param['type'] . ')')
                            ->setRequired("Vyplňte hodnotu parametru.");
                }
            }
            $form->addSubmit('s', 'Odeslat')->onClick[] = callback($this, 'testSoap');
        }
    }

    public function testSoap(NSubmitButton $btn) {
        $values = $btn->form->values;
        $operation = Operation::getSQL(array("met_id" => $values['met_id'], "rev_id" => $values['revision']));
        if(!$operation instanceof DibiRow) {
            $this->flashMessage('Vybraná operace není pro tuto revizi definována.','error');
            return;
        }
        $params = array();

        foreach (unserialize($operation->params) as $key => $param) {
            if($param['type']=='array') {
                $x=explode(",", $values['param'.$key]);
                array_walk($x, function (&$item,$key) { $item=trim($item); });
                $params[]=$x;
            }
            else {
                $params[] = $values['param' . $key];
            }
        }
        try {
            $handler = new ServiceHandler();
            SoapIdentity::$testCall = TRUE;
            $credintals = @reset(Application::find(array("app_id" => $this->app_id)));
            $handler->authenticate($credintals['login'], NULL);
            $revision=@reset(Revision::find(array("rev_id"=>$values['revision'],'app_id'=>  $this->app_id)));
        
        $handler->useRevision($revision->alias);


        $this->template->soapReturn = NDebug::dump(call_user_func_array(array($handler, $operation['name']), $params), TRUE);
        
        } catch (Exception $e) {
            $this->flashMessage('Chyba při vykonávání požadavku: '.$e->getMessage(),'error');
        }
        $this->template->sql = $handler->getQuery();
    }

}