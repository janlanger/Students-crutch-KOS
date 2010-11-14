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

    public function __construct($name, Entity $table) {
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
        if($this->type!=$type && $this->table->tableCreated) {
            $this->table->alterTable[]=$this->name;
        }
        $this->type = $type;
    }

}

?>
