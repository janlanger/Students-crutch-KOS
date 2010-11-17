<?php

namespace SAX\Entity;
/**
 * Description of Entity
 *
 * @author Jan Langer, kontakt@janlanger.cz
 */
class Entity {
    private $definition;
    private $data=array();

    public function __construct(EntityDefinition $definition) {
        $this->definition=$definition;
    }

    public function add($column, $data) {
        $column=\str_replace(".", "_", $column);
        $this->data[$column]=$data;
        if(!$this->definition->isColumnExists($column)) {
            $this->definition->addColumn($column);
        } else {
            if($this->definition->getColumn($column)->getType() == NULL && $this->definition->createDelayed) {
                $this->definition->createDelayed=FALSE;
            }
        }
        $this->definition->getColumn($column)->checkType($data);
        $this->data[$column]=$data;
    }

    public function hasData() {
        return (bool) count($this->data);
    }

    /**
     *
     * @return EntityDefinition
     */
    public function getDefinition() {
        return $this->definition;
    }

    public function get($column) {
        return isset($this->data[$column])?$this->data[$column]:NULL;
    }
    public function getData() {
        return $this->data;
    }
}
?>
