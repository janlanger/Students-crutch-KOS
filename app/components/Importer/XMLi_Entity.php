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

    public $name = NULL;
    private $columns = array();
    private $rows=array();
    private $tableIndexes=array();

    public function __construct() {
    }
    /**
     *
     * @param DOMNode $node
     * @param bool $includeChilds
     * @return X2S_DataTable
     */
    public static function parseNode(DOMNode $node) {
        $cache=  NEnvironment::getCache('xml_structure-'.basename($node->ownerDocument->documentURI));
        if(isset($cache[$node->nodeName])) {
            return $cache[$node->nodeName];
        }
        $_this=new self();
        $_this->name=$node->nodeName;
        $_this->analyzeNode($node);
        $cache->save($node->nodeName, $_this, array(
            'expire' => time() + 5*3600,
            'tags' => array('xml')
        ));
        return $_this;
    }
    
    public static function parseRootNode(DOMNode $node) {
        $cache=  NEnvironment::getCache('xml_structure-'.basename($node->ownerDocument->documentURI));
        if(isset($cache[$node->nodeName])) {
            return $cache[$node->nodeName];
        }
        $_this=new self();
        $_this->name=$node->nodeName;
        if($node->hasAttributes()) {
            for($i=0,$attrs=$node->attributes; $i<$attrs->length; $i++) {
                $_this->columns[$attrs->item($i)->name]=new X2S_DataColumn($attrs->item($i)->name, "varchar(255)");
                $_this->rows[0][$attrs->item($i)->name]=$attrs->item($i)->value;
            }
        }
        $_this->columns['import_time']=new X2S_DataColumn('import_time', "datetime");
        $_this->rows[0]['import_time']=date("Y-m-d H:i:s");

        $cache->save($node->nodeName, $_this, array(
            'expire' => time() + 5*3600,
            'tags' => array('xml')
        ));
        return $_this;
    }

    // TODO - dovymyslet
    /*private static function getCachedNode($cacheKey) {
        $cache=  NEnvironment::getCache('xml_structure');
        if(isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }
    }

    private function saveToCache($cacheKey) {
        $cache=  NEnvironment::getCache('xml_structure');
        $cache->save($cacheKey, $this, array(
            'expire' => time() - 5*3600,
            'tags' => array('xml')
        ));
    }*/

    
    private function analyzeNode(DOMNode $node) {
        $row=0;
        foreach ($node->childNodes as $child) {
            if($child->nodeType!=XML_ELEMENT_NODE)
                continue;
            
            
            if ($child->hasAttributes()) {
                $attrs = $child->attributes;

                for ($i = 0; $i < $attrs->length; $i++) {
                    $attribute = $attrs->item($i);
                    $attributeName=str_replace(".", "_", $attribute->name);
                    $this->rows[$row][$attributeName]=($attribute->value);
                    
                    if (!isset($this->columns[$attributeName])) {
                        $this->columns[$attributeName] = new X2S_DataColumn($attributeName);
                    }
                    $this->columns[$attributeName]->detectType($attribute->value);
                    
                    /*if (ctype_digit($attribute->value) && $this->columns[$attributeName]->type != 'varchar(255)' && $this->columns[$attributeName]->type != 'text') {
                        $this->columns[$attributeName]->type = 'bigint';
                    } else {
                        $this->columns[$attributeName]->type = 'varchar(255)';
                    }*/
                    if(NString::endsWith($attributeName, "_id") && !in_array($attributeName, $this->tableIndexes)) {
                        $this->tableIndexes[]=$attributeName;
                    }
                    

                }
            }
            if($child->hasChildNodes()) {
                foreach($child->childNodes as $node) {
                    if($node->nodeType!=XML_ELEMENT_NODE) {
                        continue;
                    }
                    $this->rows[$row][$node->nodeName]=NString::trim($node->nodeValue);
                    if (!in_array($node->nodeName, $this->columns))
                        $this->columns[$node->nodeName] = new X2S_DataColumn($node->nodeName,'text');
                }
            }
            $row++;
        }
        
    }

    public function createTable() {
        
        if(empty($this->columns)) {
            return;
        }
        $sql="CREATE TABLE [".strtolower($this->name)."] (\n";
        $data=array();
        foreach($this->columns as $column) {
            $data[]="[".$column->name."] ".$column->getType()." NULL";
        }
        $sql.=implode(", \n", $data);
        $pk=$this->getPrimaryKey();
        if($pk!="")
            $sql.=', PRIMARY KEY ('. $pk .")";
        $index=$this->getTableIndexes();
        if($index!="")
            $sql.=','. $index ;
        $sql.=") ENGINE=INNODB";
        //dump($sql);
        dibi::query($sql); //vytvoreni tabulky
        
        foreach($this->rows as $key=>$row) {
            foreach($this->columns as $column) {
                if(!isset($row[$column->name])) {
                    $row[$column->name]=NULL;
                }
            }
            ksort($row);
            $this->rows[$key]=$row;
        }

        
        $maxRowsPerInsert=1000;
        $rows=array_chunk($this->rows, $maxRowsPerInsert);
        
        /*for($i=0;$i<count($rows[0]);$i++) {
            echo $i." ".$rows[0][$i]['garanti'].'<br />';
        }*/
        
        for($i=0;$i<count($rows);$i++) {
            dibi::query("INSERT INTO [".$this->name."] %ex",$rows[$i]); //data
        }
        
    }

    private function getPrimaryKey() {
        if(isset($this->columns['id']) && isset($this->columns['sem_id']) && !isset($this->columns['predmet_id'])) {
            //vyjimka pro tabulku predmety
            return "[id],[sem_id]";
        }
        if(isset($this->columns['id']) && isset($this->columns['stud_id']) ) {
            //vyjimka pro tabulku studenti
            return "[id],[stud_id]";
        }
        elseif(isset($this->columns['id'])) {
            return "[id]";
        }
    }

    private function getTableIndexes() {
        foreach ($this->tableIndexes as $key=>$index) {
            $this->tableIndexes[$key]="INDEX ([".$index."])";
        }
        return implode(", ",  $this->tableIndexes);
    }
}

?>
