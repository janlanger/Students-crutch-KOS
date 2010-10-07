<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of X2S_DataRow
 *
 * @author Honza
 */
class X2S_DataRow {
    public $data;

    public function addColumn($name,$data) {
        $this->data[$name]=$data;
    }
}
?>
