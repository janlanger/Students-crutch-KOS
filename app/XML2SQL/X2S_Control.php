<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of XML2SQL
 *
 * @author Honza
 */
class XML2SQL {
    private $filename;
    private $rootNode=null;
    private $tables=array();
    public function __construct($filename) {
        $this->filename=$filename;
    }
    public function analyze() {
        $doc=new DOMDocument();
        $doc->load($this->filename);
        $doc->preserveWhitespace=false;
        $this->rootNode=$doc->documentElement;
        foreach($this->rootNode->childNodes as $node) {
            if($node->nodeType==XML_ELEMENT_NODE)
                $this->tables[$node->nodeName]=new X2S_DataTable($node);
        }
    }

    public function buildDatabase() {
        if(empty($this->tables)) {
            $this->analyze();
            //throw new InvalidStateException("Call analyze before database build.");
        }
        $db_name=$this->rootNode->nodeName.'_'.date("Ymd");
        dibi::query("DROP DATABASE IF EXISTS [".$db_name."]");
        dibi::query("CREATE DATABASE IF NOT EXISTS [".$db_name."] COLLATE 'utf8_czech_ci'");
        dibi::query("USE [".$db_name."]");
        
        foreach ($this->tables as $table) {
            $table->create();
        }
    }
}
?>
