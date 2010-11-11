<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ServiceManager
 *
 * @author Honza
 */
class ServiceManager extends \Nette\Object {


    public function getOperationsFor($client) {
        return array("echoX");
    }

}
?>
