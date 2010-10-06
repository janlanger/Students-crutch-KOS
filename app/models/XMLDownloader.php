<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of XMLDownloader
 *
 * @author Honza
 */
class XMLDownloader extends NComponent {

    private $url;
    private $login;
    private $password;

    private $localRepository;

    const NOT_MODIFIED=304;
    const MODIFIED=200;

    public function checkForNewer() {
        $last_mod = $this->getLatestChangeInLocalRepo();

        $last_mod-=date("Z");   //GMT time

        try {
            $curl = new Curl($this->url);

            $curl->setHeader('If-Modified-Since', date("D, d M Y H:i:s \G\M\T", $last_mod));
            if ($this->login != NULL && $this->password != NULL) {
                $curl->setOption('HTTPAUTH', CURLAUTH_BASIC);
                $curl->setOption('USERPWD', $this->login . ":" . $this->password);
            }
            $odpoved = $curl->head();

            $this->log($curl->getInfo('request_header'), $odpoved->getHeaders());

            if ($odpoved->getHeader('Status-Code') == 304) {
                return self::NOT_MODIFIED;
            } elseif ($odpoved->getHeader('Status-Code') == 200) { //OK - Modified
                return self::MODIFIED;
            } else {
                throw new IOException('Neočekávaná odpověď serveru. (' . $odpoved->getHeader('Status') . ')');
            }
        } catch (CurlException $e) {
            throw new IOException($e->getMessage(), $e->getCode());
        }
    }

    public function download() {
        
        $last_mod = $this->getLatestChangeInLocalRepo();

        $last_mod-=date("Z");   //GMT time
        $filename = 'rz-' . date("Y-m-d-H-i-s") . '.xml';
        try {
            NDebug::timer('download');
            $curl = new Curl($this->url);

            if ($this->login != NULL && $this->password != NULL) {
                $curl->setOption('HTTPAUTH', CURLAUTH_BASIC);
                $curl->setOption('USERPWD', $this->login . ":" . $this->password);
            }
            $curl->setDownloadFolder($localRepo);

            $odpoved = $curl->download($filename);

            $this->log($curl->getInfo('request_header'), $odpoved->getHeaders());


            if ($odpoved->getHeader('Status-Code') == 200) { //OK - Modified
                return array(
                    'file' => realpath($localRepo . '/' . $filename),
                    'size' => filesize($localRepo . '/' . $filename),
                    'time' => NDebug::timer('download'));
            } else {
                throw new IOException('Neočekávaná odpověď serveru. (' . $odpoved->getHeader('Status') . ')');
            }
        } catch (CurlException $e) {
            throw new IOException($e->getMessage(), $e->getCode());
        }
    }

    private function log($requestHeaders, $responseHeaders) {
        $presenter = NEnvironment::getApplication()->getPresenter();
        $presenter->template->requestHeaders = explode("\n", $requestHeaders);
        $presenter->template->responseHeaders = $responseHeaders;
    }

    private function getLatestChangeInLocalRepo() {
        if($this->localRepository==NULL) {
            throw new InvalidStateException ('Path to local repository is not set.');
        }
        $xmls = array();
        $files = NFinder::findFiles("*.xml")->in($this->localRepository);
        foreach ($files as $file) {
            $xmls[$file->getCTime()] = array(
                'file' => $file->getRealPath()
            );
        }
        return @end(array_keys($xmls)); //intentionally @
    }

    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
        return $this; //fluent
    }

    public function getLogin() {
        return $this->login;
    }

    public function setLogin($login) {
        $this->login = $login;
        return $this; //fluent
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
        return $this; //fluents
    }

    public function getLocalRepository() {
        return $this->localRepository;
    }

    public function setLocalRepository($localRepository) {
        $this->localRepository = $localRepository;
        return $this;
    }



}

?>
