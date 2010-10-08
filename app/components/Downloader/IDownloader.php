<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Honza
 */
interface IDownloader {

    const NOT_MODIFIED=304;
    const MODIFIED=200;

    public function checkForNewer();
    public function download();
    public function getUrl();
    public function setUrl($url);
    public function getLogin();
    public function setLogin($login);
    public function getPassword();
    public function setPassword($password);
    public function getLocalRepository();
    public function setLocalRepository($localRepository);
}
?>
