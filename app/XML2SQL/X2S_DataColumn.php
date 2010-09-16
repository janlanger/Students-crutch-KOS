<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of X2S_DataColumn
 *
 * @author Honza
 */
class X2S_DataColumn {
    public $name;
    public $type;
    

    public function __construct($name,$type=NULL) {

        $this->name=str_replace(".", "_", $name);
        $this->type=$type;
    }

    public function isPrimary() {
        return ($this->name=='id');
    }

    
}
?>
