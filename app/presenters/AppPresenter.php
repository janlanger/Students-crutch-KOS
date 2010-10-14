<?php

/**
 * Description of AppPresenter
 *
 * @author Honza
 */
class AppPresenter extends BasePresenter {

    public function actionDefault() {
        $this['appGrid'];
    }


    public function createComponentAppGrid($name) {
        $grid = new Datagrid($this, $name);
        $grid->setDataTable("rozvrh_main.application");
        $grid->setColumns(array("app_id" => 'ID#', "name" => 'Aplikace'));
        $grid->addAction(array("action" => 'App:edit', 'param' => 'app_id'), "Upravit", 'edit');
        $grid->addAction(array("action" => 'App:delete', 'param' => 'app_id'), "Smazat", 'delete')->setConfirmQuestion("Smazat", 'Opravdu chcete tento záznam odstranit?');
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
        if(!$new)
            $form->addHidden('app_id');
        $form->addText('name', 'Název')
                ->addRule(NForm::FILLED)
                ->addRule(NForm::MAX_LENGTH, null, 255);
        $form->addText('password', 'Změna hesla:')
                ->addRule(NForm::MAX_LENGTH, null, 255)
                ->setOption('description', 'Pokud nechcete měnit, nevyplňujte.');
        $form->addText('login', 'Login')
                ->addRule(NForm::FILLED)
                ->addRule(NForm::MAX_LENGTH, null, 50);
        /* $form->addText('client_path', 'Client path')
          ->addRule(Form::FILLED)
          ->addRule(Form::MAX_LENGTH, null, 50); */
        $form->addText('admin_email', 'Admin email')
                ->addRule(NForm::FILLED)
                ->addRule(NForm::MAX_LENGTH, null, 255);

        $form->addSubmit('submitButton', 'Odeslat')->onClick[] = callback($this, 'submit' . $name);

        /* $grid = $this["grid"];

          $form->onSubmit[] = function ($form) use ($grid) {
          Color::create($form->values)->save();
          $grid->flashMessage("Barva byla uložena.");
          $grid->redirect("this");
          }; */
    }

    protected function createComponentAddForm($name) {
        $this->formBase($name, true);
    }

    protected function createComponentEditForm($name) {
        $this->formBase($name, false);
    }

}