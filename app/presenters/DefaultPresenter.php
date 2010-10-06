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

    public function actionAnalyze() {
        NDebug::timer();
        //echo round(memory_get_usage()/1024,2)."kB<br />";
        $xmlControl = new XML2SQLParser(WWW_DIR . '/xml/rz-2010-09-20.xml');
        $xmlControl->buildDatabase("rozvrh-01");
        $this->template->result = 'Import dokončen - ' . NDebug::timer() . 'sec';
        //$xmlControl=new XML2SQLParser(WWW_DIR.'/xml/rz-2010-06-17.xml');
        //$xmlControl->buildDatabase("rozvrh-02");
        /* $xmlControl=new XML2SQLParser(WWW_DIR.'/xml/rz-2010-09-20.xml');
          $xmlControl->buildDatabase("rozvrh-01"); */
    }

    public function actionDownload() {
        $this['header']->addTitle('Stažení XML');
    }

    public function createComponentDownloadForm() {
        $form = new NAppForm($this, 'downloadForm');
        $form->addText('url', 'URL souboru')
                ->setType('url')
                ->setRequired('URL musí být vyplněno.');
        $form->addText('login', 'Fakultní login');
        $form->addPassword('password', 'Heslo');
        $form->addText('localPath', 'Lokální úložiště')->setRequired('Lokální úložiště musí být vyplněno.');
        $form->addCheckbox('check', 'Zkontrolovat nejdříve jestli je k dispozici novější verze')->setDefaultValue(TRUE);

        $form->addSubmit('download', 'Stáhnout')->onClick[] = callback($this, 'downloadFile');

        $config = NEnvironment::getConfig('xml');

        $form->setDefaults(array(
            'url' => $config['remoteRepository'],
            'localPath' => $config['localRepository']
        ));
        return $form;
    }

    public function downloadFile(NSubmitButton $button) {

        $values = $button->getForm()->getValues();
        try {
            $down = $this['downloader']->setUrl($values['url'])->setLogin($values['login'])->setPassword($values['password']);
            if ($values['check'] == TRUE) {
                if ($down->checkForNewer($values['localPath']) == $down::NOT_MODIFIED) {
                    $this->flashMessage('V úložišti není k dispozici žádný novější soubor.');
                    return;
                }
            }
            $down = $this['downloader']->setUrl($values['url'])->setLogin($values['login'])->setPassword($values['password']);
            $res = $down->download($values['localPath']);
            $this->flashMessage('Soubor stažen (' . $res['file'] . ', velikost:' . NTemplateHelpers::bytes($res['size']) . ', celkový čas:' . round($res['time'], 2) . ' sec)', 'success');
        } catch (IOException $e) {
            $this->flashMessage($e->getMessage(), 'error');
        }
    }

    protected function createComponentDownloader() {
        return new XMLDownloader($this, 'downloader');
    }

}
