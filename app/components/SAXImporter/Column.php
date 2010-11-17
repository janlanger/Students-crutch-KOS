<?php

namespace SAX\Entity;

/**
 * Description of Column
 *
 * @author Jan Langer, kontakt@janlanger.cz
 */
class Column {

    public $name;
    private $table;
    private $type;

    public function __construct($name, EntityDefinition $table) {
        $this->name = $name;
        $this->table = $table;
    }

    public function getType() {
        return $this->type;
    }

  

    public function checkType($value) {
        $type = $this->type;

        if ($type == "string") {
            if (strlen($value) > 255) {
                $type = "text";
            }
        } elseif (is_numeric ($value)) {
            $type = "int";
        } elseif (strlen($value) < 255) {
            $type = "string";
        } else {
            $type = "text";
        }
        if($this->type!=$type && $this->table->tableCreated && !isset($this->table->alterTable['add'][$this->name])) {
            $this->table->alterTable['change'][$this->name]=TRUE;
        }
        $this->type = $type;
    }

}

?>
