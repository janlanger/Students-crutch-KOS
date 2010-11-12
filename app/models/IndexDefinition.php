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

    public static function saveAll($tables) {
        $data=array();
        foreach($tables as $table) {
            $data[]=iterator_to_array($table);
        }
        try {
            dibi::begin(/*'indexImport'*/);

            dibi::delete(":main:import_keys_definition")->execute();    //cannot use TRUNCATE, cause implicit commit :(
            dibi::query("INSERT INTO [:main:import_keys_definition] %ex",$data);
        
            dibi::commit(/*'indexImport'*/);
            return TRUE;
        } catch (DibiException $e) {
            dibi::rollback(/*'indexImport'*/);
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
?>
