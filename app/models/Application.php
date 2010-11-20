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
        $q=dibi::select("*")->from("[:main:application]");
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
            else {
                unset($this->password);
            }
            $app_id=$this->app_id;
            unset($this->app_id);
            try{
                dibi::update(':main:application', $this)->where(array("app_id"=>$app_id))->execute();
                return TRUE;
            } catch (DibiException $e) {
                \Nette\Debug::log($e);
                throw new ModelException('Application wasn\'t updated. Try again later.', NULL, $e);
            }
        } else {
            $this->app_id=NULL;
            $this->password=self::hashPassword($this->password);
            try{
                dibi::begin();
                dibi::insert(':main:application', $this)->execute();
                dibi::insert(':main:revision', array(
                    'db_name'=>  \Nette\Environment::getConfig('xml')->liveDatabase,
                    'app_id'=>dibi::getInsertId(),
                    'isMain'=>TRUE,
                    'alias'=>'live'
                ))->execute();
                dibi::commit();
                return TRUE;
            } catch (DibiException $e) {
                dibi::rollback();
                \Nette\Debug::log($e);
                throw new ModelException('Application wasn\'t created. Try again later.', NULL, $e);
            }
        }  
    }

    public function delete() {
        dibi::delete(":main:application")->where(array("app_id"=>$this->app_id))->execute();
    }

    public static function hashPassword($password) {
        return hash("sha256", $password);
    }

}
?>
