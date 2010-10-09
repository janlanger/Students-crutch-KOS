<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of XML2SQL
 *
 * @author Honza
 * @property-read array $tables
 */
class XMLImporter extends NControl {
    private $rootNode=null;
    private $dom=NULL;
    private $tables;
    private $file;

    
    public function setFile($filename) {
        $this->file=realpath(NEnvironment::getConfig('xml')->localRepository.'/'.$filename);
        if(!file_exists($this->file)) {
            throw new FileNotFoundException('File "'.$this->file.'" was not found.');
        }
    }

    private function loadFile() {
        if($this->dom==NULL && $this->file!="") {
            $this->dom=new DOMDocument();
            $this->dom->preserveWhiteSpace=FALSE;
            $this->dom->load($this->file,LIBXML_NOEMPTYTAG | LIBXML_COMPACT);
        }
    }

    public function analyzeStructure() {
        $this->loadFile();
        
        foreach($this->dom->documentElement->childNodes as $node) {

            if($node->nodeType==XML_ELEMENT_NODE) {
                $this->tables[]=XMLi_Entity::parseNode($node);
            }
        }
        $this->presenter->template->tables=$this->tables;
    }
    
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
                
                $table=X2S_DataTable::parseNode($node);
                
                $table->createTable();
                
            }           
        }

        echo 'Total:'.$total;   
    }

    public function getTables() {
        if($this->tables==null) {
            $this->analyzeStructure();
        }
        return $this->tables;
    }


}
?>
