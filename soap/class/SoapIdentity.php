<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SoapIdentity
 *
 * @author Honza
 */
class SoapIdentity {

    private $app_id;
    private $name;
    private $login;
    public static $testCall = FALSE;

    public function authenticate($login, $password) {
        if (self::$testCall) {
            $where = array(
                "login" => $login
            );
        } else {
            $where = array(
                "login" => $login,
                "password" => hash("sha256", $password)
            );
        }
        $ret = dibi::select("*")->from(":main:application")
                        ->where($where)->execute();
        if ($ret->getRowCount() == 1) {
            $row = $ret->fetch();
            $this->app_id = $row['app_id'];
            $this->name = $row['name'];
            $this->login = $row['login'];
        } else {
            throw new NAuthenticationException("Application id wasn't found");
        }
    }

    public function getApp_id() {
        return $this->app_id;
    }



}

?>
