<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of X2S_DataTable
 *
 * @author Honza
 */
class X2S_DataTable {

    private $name = NULL;
    private $columns = array();
    private $node = NULL;

    public function __construct(DOMNode $node) {
        $this->name = strtolower($node->nodeName);
        $this->node = $node;
        $this->loadColumns();
    }

    private function loadColumns() {
        foreach ($this->node->childNodes as $child) {
            if ($child->hasAttributes()) {
                $attrs = $child->attributes;

                for ($i = 0; $i < $attrs->length; $i++) {
                    $attribute = $attrs->item($i);
                    $type = 'int';
                    if (!in_array($attribute->name, $this->columns))
                        $this->columns[$attribute->name] = new X2S_DataColumn($attribute->name);
                    if (is_numeric($attribute->value) && ($this->columns[$attribute->name]->type != 'varchar' || $this->columns[$attribute->name]->type != 'text')) {
                        $this->columns[$attribute->name]->type = 'int';
                    } else {
                        $this->columns[$attribute->name]->type = 'varchar(255)';
                    }
                }
            }
            if($child->hasChildNodes()) {
                foreach($child->childNodes as $node) {
                    if (!in_array($node->nodeName, $this->columns) && $node->nodeType==XML_ELEMENT_NODE)
                        $this->columns[$node->nodeName] = new X2S_DataColumn($node->nodeName,'text');
                }
            }
        }
    }

    public function create() {
        if(empty($this->columns)) {
            return;
        }
        $sql="CREATE TABLE [".$this->name."] (\n";
        foreach($this->columns as $column) {
            $sql.="[".$column->name."] ".$column->type." NULL";
            if($column->isPrimary()) {
                $sql.=' PRIMARY KEY';
            }
            $sql.=", \n";
        }
        $sql=substr($sql,0,  strlen($sql)-3);
        $sql.=") ENGINE=INNODB";
        //dump($sql);
        dibi::query($sql);
    }

}

?>
