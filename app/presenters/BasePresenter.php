<?php

use Nette\Application\Presenter;
use Nette\Environment;

/**
 * My NApplication
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */

/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends Presenter {
    const FLASH_SUCCESS = 'success';
    const FLASH_WARNING = 'warning';
    const FLASH_ERROR = 'error';

    protected function startup() {
        parent::startup();
        $user = $this->getUser();
        if (!$user->isLoggedIn()) {
            if ($user->getLogoutReason() === Nette\Web\User::INACTIVITY) {
                $this->flashMessage('Uplynula doba neaktivity! Systém vás z bezpečnostných dôvodov odhlásil.', 'warning');
            }


            $this->redirect('Login:', array('backlink' => Environment::getApplication()->storeRequest()));
        }
    }

    public function createComponentHeader() {
        Environment::getSession()->start();
        $header = new HeaderControl($this, 'header');
        $header->setDocType(HeaderControl::HTML_4_TRANSITIONAL);
        $header->setLanguage(HeaderControl::CZECH);
        $header->setTitle('Studentova berlička - KOS')
                ->setTitleSeparator(' :: ');

        $header->addCss('/css/screen.css');
        $header->addCss('/css/smoothness/jquery-ui-1.8.5.custom.css');
        $header->addJs('/js/jquery-1.4.2.min.js');
        $header->addJs('/js/netteForms.js');
        $header->addJs("/js/jush.js");
        return $header;
    }

    public function createComponentNavigation() {
        $nav = new Navigation();
        $nav->setupHomepage('Domů', $this->link('Default:'));
        $nav->add('Nastavení importu', $this->link('Import:'));
        //$nav->add('Import', $this->link('Import:'));
        $nav->add('Správa aplikací', $this->link("App:"));
        $nav->add('Log', $this->link('Default:showLog'));
        $nav->add("Odhlásit", $this->link("Login:logout"));
        return $nav;
    }

    protected function isCli() {
        return Environment::isConsole();
    }

}
