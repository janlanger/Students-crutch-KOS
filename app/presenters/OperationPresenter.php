<?php

use Nette\Application\AppForm;
use Nette\Forms\Form;

/**
 * Description of Functions
 *
 * @author Honza
 */
class OperationPresenter extends BasePresenter {

    /** @persistent */
    public $app_id;

    protected function startup() {
        parent::startup();
        Addons\Forms\FormMacros::register(); // register form macros
        Addons\Forms\DynamicContainer::register(); // register $form->addDynamicContainer($name);
    }

    public function actionDefault($app_id) {

        if ($app_id > 0) {
            $app = @reset(Application::find(array("app_id" => $app_id)));
        
        $this['header']->addTitle("Správa WS rozhraní aplikace " . $app['name']);

        $this->template->operations = Operation::getWithSQLs(array("app_id" => $app_id));
        $this->template->revisions = Revision::find(array("app_id" => $app_id));
        }
    }

    /*     * **************************** OPERATION ********************************* */

    public function actionAddOperation($app_id) {
        $this['header']->addTitle('Přidání operace');
        $this->template->setFile(\Nette\Environment::expand("%appDir%/templates/Operation/defineOperation.latte"));
        $this->template->edit = FALSE;
        $this['addOperationForm']['app_id']->setValue($app_id);
    }

    public function actionEditOperation($met_id) {
        $this['header']->addTitle('Úprava operace');
        $this->template->setFile(\Nette\Environment::expand("%appDir%/templates/Operation/defineOperation.latte"));
        $this->template->edit = TRUE;
        $operation = Operation::find(array("met_id" => $met_id));
        if (count($operation) == 1) {
            $operation = @reset($operation);
            $params=unserialize($operation->params);
            
            $form=$this->createComponentEditOperationForm(NULL, count($params));
            $this["editOperationForm"]["dynamicContainer"]->beforeRender(); // needed :(
            if(!$form->isSubmitted()) {
            $form->setDefaults($operation);
            foreach($params as $key=>$param) {
                $form['dynamicContainer'][$key]['type']->setDefaultValue($param['type']);
                $form['dynamicContainer'][$key]['param']->setDefaultValue(trim($param['name'],"$"));
            }
            }


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

    public function renderEditOperation($met_id) {
        
    }

    public function renderAddOperation($met_id) {
        $this["addOperationForm"]["dynamicContainer"]->beforeRender(); // needed :(
    }

    protected function createComponentAddOperationForm($name) {
        $this->baseOperationForm($name, TRUE);
    }

    protected function createComponentEditOperationForm($name, $params) {
        return $this->baseOperationForm('editOperationForm', false , $params);
    }

    private function baseOperationForm($name, $new, $params=0) {
        $form = new AppForm($this, $name);
        if (!$new) {
            $form->addHidden('met_id');
        } else {
            $form->addHidden('app_id');
        }
        $form->addText('name', 'Název operace')
                ->setRequired('Vyplňte název operace.')
                ->addRule(Form::MAX_LENGTH, "Hodnota je příliš dlouhá. Maximální počet znaků je %d.", 200);

        /* $form->addText('params', 'Parametry')
          ->addRule(Form::MAX_LENGTH, "Hodnota je příliš dlouhá. Maximální počet znaků je %d.", 255); */
        $dynamicContainer = $form->addDynamicContainer("dynamicContainer");
        // set button options not neccessery / have to be set befor form attached & factory set
        $dynamicContainer->setAddButton(true, "Přidat další", "addButtonOfDynamicContainer");

        // set button options not neccessery / have to be set befor form attached & factory set
        $dynamicContainer->setDeleteButton(true, "Odebrat parametr", "removeButtonOfDynamicContainer");
        $dynamicContainer->setMinCount(0); // minimum count of rows
        //$dynamicContainer->setMaxCount(5); // maximum count of rows
        $dynamicContainer->setDefaultCount($params); // default count of rows
        $dynamicContainer->setFactory(function(Nette\Forms\FormContainer $container) {
                    $container->addSelect('type','Typ',array("array"=>"array",'int'=>'int','string'=>'string'));
                    $container->addText('param','Proměnná: $');
                });
                
        $form->addSelect('return', 'Návratový typ', array('----------', 'array' => 'array', "string" => "string", 'integer' => 'integer'))
                ->skipFirst()
                ->setRequired('Vyberte návratový typ.');


        $form->addSelect('fetchType', 'Způsob získání výsledků', array('----------', 'simple' => 'Jednoduchý', 'assoc' => 'Podle asociativního klíče', 'single' => 'Jednu hodnotu'))
                ->skipFirst()
                ->addRule(Form::FILLED, 'Vyberte zpúsob získání')
                ->addConditionOn($form['return'], Form::EQUAL, array("string", 'integer'))
                ->addRule(Form::EQUAL, "Pokud operace nemá vracet pole, musíte vybrat způsob získání 'Jednu hodnotu'. (a samozřejmě tomu uzpůsobit SQL dotaz)", "single");
        $form['fetchType']->addConditionOn($form['return'], Form::EQUAL, "array")
                ->addRule(Form::EQUAL, 'Pokud chcete vracet pole, musíte vybrat asociativní nebo jednoduché získání výsledků', array("simple", 'assoc'));


        $form->addSubmit('submitButton', 'Odeslat')->onClick[] = callback($this, 'processOperationForm');


        return $form;
    }

    public function processOperationForm(\Nette\Forms\SubmitButton $btn) {
        $values = $btn->getForm()->getValues();
        
        try {
            Operation::create($values)->save();
            $this->flashMessage('Operace byla uložena.', 'success');
            $this->redirect('default');
        } catch (DibiException $e) {
            $this->flashMessage('Došlo k chybě. ' . $e->getMessage(), 'error');
            \Nette\Debug::log($e);
        }
    }

    /*     * ******************************* SQL *********************************** */

    public function actionEditSql($sql_id) {
        $operation = (OperationSQL::find(array('sql_id' => $sql_id)));
        $this->template->setFile(\Nette\Environment::expand("%appDir%/templates/Operation/defineSql.latte"));
        
        
        $this->template->edit = TRUE;
        

        $form = $this['editSQLForm'];
        if(is_object($operation)) {
        $this->template->params = unserialize($operation->params);
        $form->setDefaults(array(
            'sql' => $operation->sql,
            'sql_id' => $sql_id,
            'assocKey' => $operation->assocKey
        ));

        if ($operation->fetchType != 'assoc') {
            $form['assocKey']->setDisabled();
        }
        }
    }

    public function actionDefineSql($met_id, $rev_id) {
        $this->template->edit = FALSE;
        $operation = @reset(Operation::find(array('met_id' => $met_id)));
        
        $form = $this['addSQLForm'];
        $form['met_id']->setDefaultValue($met_id);
        $form['rev_id']->setDefaultValue($rev_id);
if(is_object($operation)) {
            $this->template->params = unserialize($operation->params);

        if ($operation->fetchType != 'assoc') {
            $form['assocKey']->setDisabled();
        }}
    }

    protected function createComponentAddSQLForm($name) {
        $this->baseSQLForm($name, TRUE);
    }

    protected function createComponentEditSQLForm($name) {
        $this->baseSQLForm($name, FALSE);
    }

    protected function baseSQLForm($name, $new) {
        $form = new AppForm($this, $name);

        if (!$new) {
            $form->addHidden('sql_id');
        } else {
            $form->addHidden('met_id');
            $form->addHidden('rev_id');
        }

        $form->addTextArea('sql', 'SQL')
                ->setRequired('Vyplňte SQL');
        $form['sql']->getControlPrototype()->style("width:600px;height:150px;");
        $form->addText('assocKey', 'Asociativní klíč')
                ->setRequired('Vyplnte prosím asociativní klíč.')
                ->addRule(\Nette\Forms\Form::MAX_LENGTH, 'Maximální délka pole je %d', 100);

        $form->addSubmit('submitButton', 'Odeslat')->onClick[] = callback($this, 'processSQLForm');

        return $form;
    }

    public function processSQLForm(\Nette\Forms\SubmitButton $btn) {
        $values = $btn->getForm()->getValues();

        try {
            OperationSQL::create($values)->save();
            $this->flashMessage('SQL definice byla uložena.', 'success');
            $this->redirect('default');
        } catch (Exception $e) {
            if ($e instanceof \Nette\Application\AbortException) {
                throw $e;
            }
            $this->flashMessage('Došlo k chybě. ' . $e->getMessage(), 'error');
            \Nette\Debug::log($e);
        }
    }

}
