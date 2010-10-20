<?php

/**
 * Description of AppPresenter
 *
 * @author Honza
 */
class AppPresenter extends BasePresenter {

    public function actionDefault() {
        $this['header']->addTitle("Správa aplikací");
    }

    public function actionDelete($app_id) {
        $application=Application::find(array("app_id"=>$app_id));
        if(count($application)==1) {
            $application=@reset($application);
            $application->delete();
            $this->flashMessage('Aplikace byla smazána.', 'success');
            
        }
        $this->flashMessage('Záznam '.$app_id.' nenalezen.', 'error');
        $this->redirect("App:");


    }

    public function createComponentAppGrid($name) {
        $grid = new Datagrid($this, $name);
        $grid->setDataTable(":main:application");
        $grid->setColumns(array("app_id" => 'ID#', "name" => 'Aplikace'));
        $grid->addAction(array("action" => 'Operation:','param'=>'app_id'), "Správa funkcí WS");
        $grid->addAction(array("action" => 'Revision:','param'=>'app_id'), "Správa revizí databáze");
        $grid->addAction(array("action" => 'SoapTest:','param'=>'app_id'), 'SOAP Tester');
        $grid->addAction(array("action" => 'App:edit', 'param' => 'app_id'), "Upravit", 'edit');
        $grid->addAction(array("action" => 'App:delete', 'param' => 'app_id'), "Smazat", 'delete')
                ->setConfirmQuestion("Smazat", 'Opravdu chcete tento záznam odstranit? Smažou se také veškeré závislé záznamy (definované funkce WS...)');
    }

    public function actionAdd() {
        $this['header']->addTitle("Přidat novou aplikaci");
    }

    public function actionEdit($app_id) {
        $this['header']->addTitle("Editace aplikace");
        $application=Application::find(array("app_id"=>$app_id));
        if(count($application)==1) {
            $application=@reset($application);
            unset($application['password']);
        }
        $this['editForm']->setDefaults($application);
    }

    private function formBase($name, $new) {

        $form = new NAppForm($this, $name);
        
        $form->addText('name', 'Název')
                ->setRequired('Vyplňte prosím jméno aplikace.')
                ->addRule(NForm::MAX_LENGTH, "null", 255);
        $form->addText('password', 'Heslo')
                ->addRule(NForm::MAX_LENGTH, "null", 255);

        $form->addText('login', 'Login')
                ->addRule(NForm::FILLED,"Vyplňte prosím login.")
                ->addRule(NForm::MAX_LENGTH, "", 50);
        /* $form->addText('client_path', 'Client path')
          ->addRule(Form::FILLED)
          ->addRule(Form::MAX_LENGTH, null, 50); */
        $form->addText('admin_email', 'Admin email')        
                ->addRule(NForm::MAX_LENGTH, "null", 255);
        if(!$new) {
            $form->addHidden('app_id');            
        }
        else {
            $form['password']->setRequired("Vyplňtě heslo.")
                ->setOption('description', 'Pokud nechcete měnit, nevyplňujte.');
        }

        $form->addSubmit('submitButton', 'Odeslat')->onClick[] = callback($this, 'proccessAppForm');

    }

    public function proccessAppForm(NSubmitButton $btn) {
        $values=$btn->getForm()->getValues();
        
        try {
            Application::create($values)->save();
            $this->flashMessage('Aplikace byla uložena.','success');
            $this->redirect('App:');
        } catch (DibiException $e) {
            $this->flashMessage('Došlo k chybě. '.$e->getMessage(),'error');
            NDebug::log($e);
        }

    }

    protected function createComponentAddForm($name) {
        $this->formBase($name, true);
    }

    protected function createComponentEditForm($name) {
        $this->formBase($name, false);
    }

}