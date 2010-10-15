<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Application
 *
 * @author Honza
 */
class Application extends Model {

    public static function find($where = NULL, $order = NULL, $offset = NULL, $limit = NULL) {
        $q=dibi::select("*")->from("[rozvrh_main].[application]");
        if($where!=NULL) $q->where ($where);
        if($order!=NULL) $q->orderBy ($order);
        if($limit!=NULL) $q->limit ($limit);
        if($offset!=NULL) $q->offset ($offset);
        
        return $q->execute()->setRowClass(get_called_class())->fetchAssoc('app_id');
    }

    public static function create($values) {
        $_this=new self($values);
        return $_this;
    }

    public function save() {
        if(isset($this->app_id) && $this->app_id > 0) {
            if($this->password!="") {
                $this->password=self::hashPassword($this->password);
            }
            dibi::update('rozvrh_main.application', $this)->execute();
            return TRUE;
        } else {
            $this->app_id=NULL;
            $this->password=self::hashPassword($this->password);
            dibi::insert('rozvrh_main.application', $this)->execute();
            return TRUE;
        }  
    }

    public function delete() {
        dibi::delete("rozvrh_main.application")->where(array("app_id"=>$this->app_id))->execute();
    }

    public static function hashPassword($password) {
        return hash("sha256", $password);
    }

}
?>
