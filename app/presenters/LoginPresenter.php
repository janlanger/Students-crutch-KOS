<?php

/**
 * My NApplication
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */
use Nette\Application\Presenter;

/**
 * Login / logout presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class LoginPresenter extends Presenter {

     /** @persistent */
    public $backlink = '';

    /**
     * Login form component factory.
     * @return mixed
     */
    protected function createComponentLoginForm() {
        $form = new \Nette\Application\AppForm;
        $form->addText('username', 'Uživatel:')
                ->addRule(\Nette\Application\AppForm::FILLED, 'Vložte uživatelské jméno.');

        $form->addPassword('password', 'Heslo:')
                ->addRule(\Nette\Application\AppForm::FILLED, 'Vložte heslo.');

        $form->addSubmit('login', 'Login');

        $form->onSubmit[] = callback($this, 'loginFormSubmitted');
        return $form;
    }

    public function loginFormSubmitted($form) {
        try {
            $values = $form->values;

            $this->getUser()->setExpiration('+ 60 minutes', TRUE);

            $this->getUser()->login($values['username'], $values['password']);
            $this->getApplication()->restoreRequest($this->backlink);
            $this->redirect('Default:');
        } catch (\Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    public function actionLogout() {
        $this->getUser()->logout();
        $this->redirect("default");
    }

}
