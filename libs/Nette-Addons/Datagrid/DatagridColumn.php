<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DatagridColumn
 *
 * @author Honza
 */
class DatagridColumn {

    private $key;
    private $title;
    private $formatter;

    


    public function __construct($key,$title) {
        $this->key=$key;
        $this->title=$title;
        $this->formatter=new DatagridFormatter();
    }

    public function setFormatter($type,$format) {
        $this->formatter=new DatagridFormatter($type,$format);
    }

    public function format($data) {
        return $this->formatter->format($data);
    }

    public function getKey() {
        return $this->key;
    }
    public function getTitle() {
        return $this->title;
    }
}
?>
