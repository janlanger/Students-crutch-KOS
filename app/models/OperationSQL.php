<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OperationSQL
 *
 * @author Honza
 */
class OperationSQL extends Model {
    public static function find($where) {
        NDebug::$showLocation=TRUE;
        $q = dibi::select("[name],[sql],[params],[return],[fetchType],[assocKey]")
                ->from("[:main:operations_def]")
                ->innerJoin("[:main:operations_sql]")
                ->using("(met_id)")
                ->where($where);
        return $q->execute()->fetch();

    }

    public static function create($values) {
        $_this=new self($values);
        $_this->validate();
        return $_this;
    }

    private function validate() {
        if(isset($this->sql)) {
            if(!NString::startsWith(strtolower($this->sql), 'select')) {
                throw new InvalidArgumentException("Only SELECT command is allowed.");
            }
            if(strpos($this->sql, dibi::$substs->main)!== FALSE || strpos($this->sql, ":main:")!== FALSE) {
                throw new InvalidArgumentException("Access to the main configuration database is prohibited.");
            }
        }
    }

    public function save() {
        if(isset($this->sql_id) && $this->sql_id > 0) {
            //$this->app_id=NULL;
            $sql_id=$this->sql_id;
            unset($this->sql_id);
            dibi::update(':main:operations_sql', $this)->where(array("sql_id"=>$sql_id))->execute();
            return TRUE;
        } else {
            dibi::insert(':main:operations_sql', $this)->execute();
            return TRUE;
        }
    }

}
?>
