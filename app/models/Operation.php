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
        $q = dibi::select("*")->from("[rozvrh_main].[operations_def]");
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
        $sqls = dibi::select(array("met_id", 'rev_id', 'sql'))->from("[rozvrh_main].[operations_sql]")->where("met_id")->in($keys)->execute()->fetchAssoc("met_id,rev_id");
        foreach ($sqls as $key => $sql) {
            $met[$key]['sql'] = $sql;
        }
        return $met;
    }

    public static function getSQL($where) {
        NDebug::$showLocation=TRUE;
        $q = dibi::select("[name],[sql],[params],[return]")
                ->from("[rozvrh_main].[operations_def]")
                ->innerJoin("[rozvrh_main].[operations_sql]")
                ->using("(met_id)")
                ->where($where);
        return $q->execute()->fetch();

    }

}

?>
