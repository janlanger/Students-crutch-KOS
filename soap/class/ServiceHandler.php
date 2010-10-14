<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ServiceHandler
 *
 * @author Honza
 */
class ServiceHandler {
    private $user;
    private $latestError;

    public function __call($name, $arguments) {
         $name;
        return dump($this->user,$name);
    }
    
    public function authenticate($client, $password) {
        $this->user=new SoapIdentity();
        try {
            $this->user->authenticate($client,$password);
            return TRUE;
        } catch (NAuthenticationException $e) {
            $this->latestError=$e;
            return FALSE;
        }
    }

    public function getLastError() {
        if(!is_null($this->latestError)) {
            return get_class($this->latestError).': '.$this->latestError->getMessage();
        }
        return NULL;
    }




}
?>
