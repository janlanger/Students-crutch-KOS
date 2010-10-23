<?php

/**
 * Description of Functions
 *
 * @author Honza
 */
class OperationPresenter extends BasePresenter {

    /** @persistent */
    public $app_id;

    public function actionDefault($app_id) {

        if ($app_id > 0) {
            $app = @reset(Application::find(array("app_id" => $app_id)));
        }
        $this['header']->addTitle("Správa WS rozhraní aplikace " . $app['name']);

        $this->template->operations = Operation::getWithSQLs(array("app_id" => $app_id));
        $this->template->revisions = Revision::find(array("app_id" => $app_id));
    }

    /*     * **************************** OPERATION ********************************* */

    public function actionAddOperation() {

    }

    public function actionEditOperation($met_id) {
        $operation = Operation::find(array("met_id" => $met_id));
        if (count($operation) == 1) {
            $operation = @reset($operation);
            $this['editOperationForm']->setDefaults($operation);
        }
    }

    public function actionDeleteOperation($met_id) {
        $operation = Operation::find(array("met_id" => $met_id));
        if (count($operation) == 1) {
            $operation = @reset($operation);
            $operation->delete();
            $this->flashMessage('Operace byla smazána.', 'success');
        } else {
            $this->flashMessage('Záznam ' . $met_id . ' nenalezen.', 'error');
        }
        $this->redirect("default");
    }

    protected function createComponentAddOperationForm($name) {
        $this->baseOperationForm($name, TRUE);
    }

    protected function createComponentEditOperationForm($name) {
        $this->baseOperationForm($name, false);
    }

    private function baseOperationForm($name, $new) {
        $form = new NAppForm($this, $name);
        if (!$new) {
            $form->addHidden('met_id');
        } else {
            $form->addHidden('app_id');
        }
        $form->addText('name', 'Název operace')
                ->setRequired('Vyplňte název operace.')
                ->addRule(NForm::MAX_LENGTH, "Hodnota je příliš dlouhá. Maximální počet znaků je %d.", 200);

        $form->addText('params', 'Parametry')
                ->addRule(NForm::MAX_LENGTH, "Hodnota je příliš dlouhá. Maximální počet znaků je %d.", 255);
        //TODO!!!

        $form->addSelect('return', 'Návratový typ', array('----------', 'array' => 'array', "string" => "string", 'integer' => 'integer'))
                ->skipFirst()
                ->setRequired('Vyberte návratový typ.');

        $form->addSelect('fetchType', 'Způsob získání výsledků', array('----------', 'simple' => 'Jednoduchý', 'assoc' => 'Podle asociativního klíče', 'single' => 'Jednu hodnotu'))
                ->skipFirst()
                ->addRule(NForm::FILLED, 'Vyberte zpúsob získání');

        $form->addSubmit('submitButton', 'Odeslat')->onClick[] = callback($this, 'processOperationForm');


        return $form;
    }

    public function processOperationForm(NSubmitButton $btn) {
        $values = $btn->getForm()->getValues();

        try {
            Operation::create($values)->save();
            $this->flashMessage('Operace byla uložena.', 'success');
            $this->redirect('default');
        } catch (DibiException $e) {
            $this->flashMessage('Došlo k chybě. ' . $e->getMessage(), 'error');
            NDebug::log($e);
        }
    }

    /*     * ******************************* SQL *********************************** */


    public function actionEditSql($sql_id) {
        $operation=OperationSQL::find(array('sql_id'=>$sql_id));

        $this->template->setFile(NEnvironment::expand("%appDir%/templates/Operation/defineSql.phtml"));
        $this->template->edit=TRUE;
        $this->template->params=unserialize($operation->params);

        $form=$this['editSQLForm'];
        $form->setDefaults(array(
            'sql'=>$operation->sql,
            'sql_id'=>$sql_id,
            'assocKey'=>$operation->assocKey

        ));

        if($operation->fetchType!='assoc') {
            $form['fetchType']->setDisables();
        }
    }

    public function actionDefineSql($met_id,$rev_id) {
        $this->template->edit=FALSE;
        $operation=reset(Operation::find(array('met_id'=>$met_id)));
        $this->template->params=unserialize($operation->params);

        $form=$this['addSQLForm'];
        $form['met_id']->setDefaultValue($met_id);
        $form['rev_id']->setDefaultValue($rev_id);

        if($operation->fetchType!='assoc') {
            $form['fetchType']->setDisables();
        }

    }
    
    protected function createComponentAddSQLForm($name) {
        $this->baseSQLForm($name, TRUE);   
    }

    protected function createComponentEditSQLForm($name) {
        $this->baseSQLForm($name, FALSE);
    }


    protected function baseSQLForm($name,$new) {
        $form = new NAppForm($this, $name);

        if(!$new) {
            $form->addHidden('sql_id');
        }
        else {
            $form->addHidden('met_id');
            $form->addHidden('rev_id');
        }
        
        $form->addTextArea('sql', 'SQL')
                ->setRequired('Vyplňte SQL');
        $form['sql']->getControlPrototype()->style("width:600px;height:150px;");
        $form->addText('assocKey', 'Asociativní klíč')
                ->setRequired('Vyplnte prosím asociativní klíč.')
                ->addRule(NForm::MAX_LENGTH, 'Maximální délka pole je %d', 100);

        $form->addSubmit('submitButton', 'Odeslat')->onClick[] = callback($this, 'processSQLForm');

        return $form;
    }

    public function processSQLForm(NSubmitButton $btn) {
        $values = $btn->getForm()->getValues();

        try {
            OperationSQL::create($values)->save();
            $this->flashMessage('SQL definice byla uložena.', 'success');
            $this->redirect('default');
        } catch (Exception $e) {
            if($e instanceof NAbortException) {
                throw $e;
            }
            $this->flashMessage('Došlo k chybě. ' . $e->getMessage(), 'error');
            NDebug::log($e);
        }
    }
}
