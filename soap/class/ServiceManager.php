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
class ServiceManager extends NObject {


    public function getOperationsFor($client) {
        return array("echoX");
    }

}
?>
