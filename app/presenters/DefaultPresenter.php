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

/*    public function actionImport() {
        $this['header']->addTitle('Import');
    }*/

    public function actionShowLog() {
        $this['header']->addTitle('Log');
    }

    protected function createComponentLogGrid($name) {
        $grid=new Datagrid($this, $name);
        $grid->setDataTable(':main:log');
        $grid->setColumns(array('timestamp'=>'Čas','severity'=>'Z.','component'=>'Komponenta','message'=>'Zpráva'));
        $grid->setDefaultSort('timestamp', 'desc');
        $grid->setColumnFormat('timestamp', DatagridFormatter::DATE);
        $grid->setColumnFormat('severity', DatagridFormatter::CALLBACK,  function ($record) {
            
            if(in_array($record, array("notice",'info','warning','error','critical'))) {
                echo NHtml::el("img")->src("/images/icons/".$record.".png");
            }
            else echo $record;
        });

        $grid->setItemsPerPage(25);
        
    }

/*    public function actionDownload() {
        $this['header']->addTitle('Stažení XML');
    }

    public function createComponentDownloadForm() {
        $form = new NAppForm($this, 'downloadForm');
        $form->addText('url', 'URL souboru')
                        ->setType('url')
                        ->setRequired('URL musí být vyplněno.')
                        ->getControlPrototype()->class[] = 'long';
        $form->addText('login', 'Fakultní login');
        $form->addPassword('password', 'Heslo');

        $form->addCheckbox('check', 'Zkontrolovat nejdříve jestli je k dispozici novější verze.')->setDefaultValue(TRUE);

        $form->addSubmit('download', 'Stáhnout')->onClick[] = callback($this, 'downloadFile');

        $config = NEnvironment::getConfig('xml');

        $form->setDefaults(array(
            'url' => $config['remoteURL']
        ));
        return $form;
    }

    public function downloadFile(NSubmitButton $button) {

        $values = $button->getForm()->getValues();
        try {
            $downloader = $this['downloader'];
            $downloader->setUrl($values['url'])
                    ->setLogin($values['login'])
                    ->setPassword($values['password'])
                    ->setLocalRepository(NEnvironment::getConfig('xml')->localRepository);

            if ($values['check'] == TRUE) {
                if ($downloader->checkForNewer() == IDownloader::NOT_MODIFIED) {
                    $this->flashMessage('V úložišti není k dispozici žádný novější soubor.');
                    return;
                }
            }

            $res = $downloader->download();
            $this->flashMessage('Soubor stažen (' . $res['file'] . ', velikost:' . NTemplateHelpers::bytes($res['size']) . ', celkový čas:' . round($res['time'], 2) . ' sec)', 'success');
            $this->redirect('this');
        } catch (IOException $e) {
            $this->flashMessage($e->getMessage(), 'error');
        }
    }

    protected function createComponentDownloader() {
        return $this->getApplication()->getContext()->getService('IDownloader');
    }*/

}
