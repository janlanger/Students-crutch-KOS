<?php
/**
 * Description of IndexDefinition
 *
 * @author Jan Langer, kontakt@janlanger.cz
 */
class IndexDefinition extends Model {
    public static function find($where = NULL, $order = NULL, $offset = NULL, $limit = NULL) {
        $q = dibi::select("*")->from("[:main:import_keys_definition]");
        if ($where != NULL)
            $q->where($where);
        if ($order != NULL)
            $q->orderBy($order);
        if ($limit != NULL)
            $q->limit($limit);
        if ($offset != NULL)
            $q->offset($offset);

        return $q->execute()->setRowClass(get_called_class())->fetchAssoc("key_id");
    }
}
?>
