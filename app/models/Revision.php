<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Functions
 *
 * @author Honza
 */
class Revision extends Model {

    public static function find($where = NULL, $order = NULL, $offset = NULL, $limit = NULL) {
        $q=dibi::select("*")->from("[:main:revision]");
        if($where!=NULL) $q->where ($where);
        if($order!=NULL) $q->orderBy ($order);
        if($limit!=NULL) $q->limit ($limit);
        if($offset!=NULL) $q->offset ($offset);

        return $q->execute()->setRowClass(get_called_class())->fetchAssoc('rev_id');
    }

    public static function getAvaiableTables($database=NULL) {
        if($database==NULL) $database=NEnvironment::getConfig('xml')->liveDatabase;

        $databaseManager=NEnvironment::getContext()->getService('IDatabaseManager');
        return $databaseManager->getTables($database);
    }

    public static function create($name,$app_id,$isMain,$database,$tables,$from=NULL) {
        if(dibi::select('count(*)')->from(":main:revision")->where(array("app_id"=>$app_id,'alias'=>$name))->execute()->fetchSingle()) {
            throw new ModelException('Revizi s tímto názvem nelze vytvořit. Pravděpodobně již existuje.');
        }
        if(dibi::select('count(*)')->from(":main:revision")->where(array("app_id"=>$app_id,'isMain'=>$isMain))->execute()->fetchSingle()) {
            throw new ModelException('Nelze vytvořit více revizí označených jako výchozí.');
        }


        if($from==NULL) $from=NEnvironment::getConfig('xml')->liveDatabase;

        $databaseManager=NEnvironment::getContext()->getService('IDatabaseManager');
        try{
            $databaseManager->createRevision($from,$database,$tables);
            dibi::insert(":main:revision", array(
                "app_id"=>$app_id,
                "alias"=>$name,
                "db_name"=>$database,
                "isMain"=>$isMain
            ))->execute();
            return TRUE;
        } catch (DatabaseManagerException $e) {
            throw new ModelException('Unable to create revision '.$name.'. '.$e->getMessage(), NULL, $e);
        } catch (DibiException $e) {
            throw new ModelException($e->getMessage(), $e->getCode(), $e);
        }


    }

}
?>
