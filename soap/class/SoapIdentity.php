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
    

    public function authenticate($login, $password) {
        $ret=dibi::select("*")->from("rozvrh_main.application")
                ->where(array(
                    "login" => $login,
                    "password" => hash("sha256",$password)
                    ))->execute();
        if($ret->getRowCount() == 1) {
            $row=$ret->fetch();
            $this->app_id=$row['app_id'];
            $this->name=$row['name'];
            $this->login=$row['login'];
        }
        else {
            throw new NAuthenticationException("Application id wasn't found");
        }
    }

}

?>
