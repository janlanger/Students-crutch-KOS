<?php

namespace SAX\Queue;

use SAX\Entity\EntityDefinition;

/**
 * Description of IndexCache
 *
 * @author Jan Langer, kontakt@janlanger.cz
 */
class IndexCache {

    private $cache;
    private $whatToWatch = array();
    private $dependencies = array();

    public function __construct($keys) {
        foreach($keys as $table=>$index) {
            foreach($index as $column => $def) {
                if($def['type']!='foreign') continue;

                $this->whatToWatch[$def['foreign'][0]][$def['foreign'][1]]=TRUE;
                $this->dependencies[$table][$column][$def['foreign'][0].".".$def['foreign'][1]]=0;
            }
        }
    }

    public function hasFulfilledDeps(\SAX\Entity\Entity $entity) {
        $table_deps=(isset($this->dependencies[$entity->getDefinition()->getName()])?$this->dependencies[$entity->getDefinition()->getName()]:array());
        
        $return = TRUE;
        foreach($table_deps as $column => $deps) {
            $value=$entity->get($column);
            if(is_null($value)) continue;
            $flip=\array_flip($deps);
            
            if(!isset($this->cache[$flip[0]][$value])) {
                $return = FALSE;
            }
            if($return == FALSE) {
                break;
            }
        }
        return $return;
    }

    public function add(\SAX\Entity\Entity $entity) {
        if(isset($this->whatToWatch[$entity->getDefinition()->getName()])) {
            $cols=$this->whatToWatch[$entity->getDefinition()->getName()];
            foreach ($cols as $col => $x) {
                $this->cache[$entity->getDefinition()->getName().'.'.$col][$entity->get($col)]=TRUE;
            }
        }
    }

    public function removeDependency($table, $column, $ref_table, $ref_column) {
        unset($this->dependencies[$table][$column]); //TODO better
    }

}

?>
