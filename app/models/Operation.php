<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Functions
 *
 * @author Honza
 * @property-read string $param
 */
class Operation extends Model {

    public static function find($where = NULL, $order = NULL, $offset = NULL, $limit = NULL) {
        $q = dibi::select("*")->from("[:main:operations_def]");
        if ($where != NULL)
            $q->where($where);
        if ($order != NULL)
            $q->orderBy($order);
        if ($limit != NULL)
            $q->limit($limit);
        if ($offset != NULL)
            $q->offset($offset);

        return $q->execute()->setRowClass(get_called_class())->fetchAssoc("met_id");
    }

    public static function getWithSQLs($where = NULL, $order = NULL, $offset = NULL, $limit = NULL) {
        $met=self::find($where, $order, $offset, $limit);
        $keys = array_keys($met);
        $sqls = dibi::select("met_id,rev_id, sql_id,[sql],assocKey")->from("[:main:operations_sql]")->where("met_id")->in($keys)->execute()->fetchAssoc("met_id,rev_id");
        foreach ($sqls as $key => $sql) {
            $met[$key]['sql'] = $sql;
        }
        return $met;
    }

    public static function getSQL($where) {
        \Nette\Debug::$showLocation=TRUE;
        $q = dibi::select("[name],[sql],[params],[return],[fetchType],[assocKey]")
                ->from("[:main:operations_def]")
                ->innerJoin("[:main:operations_sql]")
                ->using("(met_id)")
                ->where($where);
        return $q->execute()->fetch();

    }

    public static function create($values) {
        $val=array();
        foreach($values as $k=>$v) {
            if($k=='dynamicContainer') {
                $params=array();
                foreach($v as $k1=>$v1) {
                    $params[]=array(
                        'type'=>$v1['type'],
                        'name'=>'$'.$v1['param']
                    );
                }
                $val['params']=serialize($params);
            } else {
                $val[$k]=$v;
            }
        }
        $_this=new self($val);
        return $_this;
    }

    public function save() {
        if(isset($this->met_id) && $this->met_id > 0) {
            //$this->app_id=NULL;
            $met_id=$this->met_id;
            unset($this->met_id);
            dibi::update(':main:operations_def', $this)->where(array("met_id"=>$met_id))->execute();
            return TRUE;
        } else {
            $this->met_id=NULL;

            dibi::insert(':main:operations_def', $this)->execute();
            return TRUE;
        }
    }
    public function delete() {
        dibi::delete(":main:operations_def")->where(array("met_id"=>$this->met_id))->execute();
    }

    


}

?>
