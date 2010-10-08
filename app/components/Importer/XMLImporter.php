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
class XMLImporter extends NControl {
    private $filename;
    private $rootNode=null;
    private $dom;
    public function construct($filename) {
       $this->filename=$filename;
        $this->dom=new DOMDocument();
        $this->dom->load($this->filename);
        $this->dom->preserveWhitespace=false;
        $this->rootNode=$this->dom->documentElement;
    }
    //prilis pametove narocne :-(
    /*public function analyze() {
        $cache=NEnvironment::getCache('xml_structure');
        if(isset($cache['data'])) {
            $this->tables=$cache['data'];
            return;
        }
        foreach($this->rootNode->childNodes as $node) {
            
            if($node->nodeType==XML_ELEMENT_NODE) {
                $this->tables[]=X2S_DataTable::parseNode($node);
            }            
        }
        $cache->save('data', $this->tables,array(
            'expire'=> time() + 10*3600,
            'tags' =>array('xml')
        ));
    }*/

    public function buildDatabase($db_name) {
        //$this->analyze();
        
        //$db_name=$this->rootNode->nodeName/*.'_'.date("Ymd")*/;
        dibi::query("DROP DATABASE IF EXISTS [".$db_name."]");
        dibi::query("CREATE DATABASE IF NOT EXISTS [".$db_name."] COLLATE 'utf8_czech_ci'");
        dibi::query("USE [".$db_name."]");
        //element <rozvrh>
        $table=X2S_DataTable::parseRootNode($this->rootNode);
        $table->createTable();
        $total=0;
        //vsechny vnorene elementy - tabulky
        foreach($this->rootNode->childNodes as $node) {
            
            if($node->nodeType==XML_ELEMENT_NODE) {
                echo 'NODE: '.$node->nodeName.'<br />';
                $time=microtime(true);
                $table=X2S_DataTable::parseNode($node);
                echo 'Parse - '.round(microtime(true)-$time,4).'<br />';
                $total+=microtime(true)-$time;
                $time=microtime(true);
                $table->createTable();
                echo 'Create - '.round(microtime(true)-$time,4).'<br />';
                $total+=microtime(true)-$time;
                echo '<br /><br />';
            }           
        }

        echo 'Total:'.$total;

        
    }
}
?>
