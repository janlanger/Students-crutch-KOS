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
class XMLi_Column extends \Nette\Object {
    private $name;
    private $type=null;
    private $length=0;
    

    public function __construct($name,$type=NULL) {

        $this->name=str_replace(".", "_", $name);
        $this->type=$type;
    }
    
    public function detectType($value) {
        if($this->type=="varchar(255)") {
            //TODO> detekce delky do $this->length
            if(strlen(trim($value))>255) //varchar limit
                $this->type='text';
            return;
        }
        if(ctype_digit($value)) {
            if($this->type!='bigint') {
                if($value < 2147483647) { //mysql int limit
                    $this->type='bigint';
                }
                else {
                    $this->type='bigint';
                }
            }
            return;
        }

        if(strlen(trim($value))>255) {
            $this->type='text';
        }
        $this->type='varchar(255)';
    }

    public function getType() {
        return $this->type;
    }

    public function getName() {
        return $this->name;
    }





    
}
?>
