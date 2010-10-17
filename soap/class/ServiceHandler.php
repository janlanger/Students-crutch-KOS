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
    private $revision;

    public function __call($name, $arguments) {
        if($this->user == NULL) {
            $this->latestError='Unauthenticated!';
            return FALSE;
        }
        
        $operation=Operation::getSQL(array("name"=>$name, "rev_id"=>$this->getRevision()->rev_id));
        $params=unserialize($operation->params);
        
        
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

    private function getRevision() {
        if($this->revision==NULL) {
            $revision=@reset(Revision::find(array("app_id"=>$this->user->getApp_id(),"isMain"=>TRUE)));
            $this->revision=$revision;
            dibi::query("USE DATABASE [".$revision['db_name']."]");
        }
        return $this->revision;
    }




}
?>
