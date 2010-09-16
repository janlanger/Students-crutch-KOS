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
    private $dom;
    public function __construct($filename) {
       
        
        $this->filename=$filename;
        $this->dom=new DOMDocument();
        $this->dom->load($this->filename);
        $this->dom->preserveWhitespace=false;
        $this->rootNode=$this->dom->documentElement;
    }
    public function analyze() {
        dump(ctype_digit("1080806,60741000"));
        exit;
        foreach($this->rootNode->childNodes as $node) {
            if($node->nodeType==XML_ELEMENT_NODE)
                $this->tables[$node->nodeName]=new X2S_DataTable($node);
        }
        
    }

    public function buildDatabase() {
        
        $db_name=$this->rootNode->nodeName/*.'_'.date("Ymd")*/;
        dibi::query("DROP DATABASE IF EXISTS [".$db_name."]");
        dibi::query("CREATE DATABASE IF NOT EXISTS [".$db_name."] COLLATE 'utf8_czech_ci'");
        dibi::query("USE [".$db_name."]");
        
        foreach($this->rootNode->childNodes as $node) {
            
            if($node->nodeType==XML_ELEMENT_NODE) {
                $table=new X2S_DataTable($node);
                $table->create();
                
               
                unset ($table);
            }
            
        }

        
    }
}
?>
