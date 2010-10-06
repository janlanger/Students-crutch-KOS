<?php

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
abstract class BasePresenter extends NPresenter
{
    public function createComponentHeader() {
        $header=new HeaderControl($this, 'header');
        $header->setDocType(HeaderControl::HTML_4_TRANSITIONAL);
        $header->setLanguage(HeaderControl::CZECH);
        $header->setTitle('Studentova berlička - KOS')
                ->setTitleSeparator(' | ');

        $header->addCss('/css/screen.css');
        $header->addJs('http://code.jquery.com/jquery-1.4.2.min.js');
        $header->addJs('/js/netteForms.js');
        return $header;
    }

    public function createComponentNavigation() {
        $nav=new Navigation();
        $nav->setupHomepage('Domů', $this->link('Default:'));
        $nav->add('Stažení XML', $this->link('Default:download'));
        $nav->add('Import', $this->link('Default:import'));
        return $nav;
    }
}
