<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of File
 * @property-read string $filename
 * @property-read string $database_name
 * @property-read string $time
 * @property-read int $id
 * @author Honza
 */
class File extends Model {
    
    protected  $create_time;
    protected $size;
    

    public static function find($where = NULL, $order = NULL, $offset = NULL, $limit = NULL) {
        $q = dibi::query(
                        'SELECT [file],[id],[time] FROM [:main:import_history]',
                        '%if', isset($where), 'WHERE %and', isset($where) ? $where : array(), '%end',
                        '%if', isset($order), 'ORDER BY %by', $order, '%end',
                        '%if', isset($limit), 'LIMIT %i %end', $limit,
                        '%if', isset($offset), 'OFFSET %i %end', $offset
        );
        return $q->setRowClass(get_called_class())->fetchAssoc('file');
    }

    /**
     *
     * @return File[]
     */
    public static function getImportableFiles() {
        $db_files = dibi::query('SELECT [id], [filename],[import_time], [database_name]
            FROM [:main:import_history]
            ORDER BY [filename] ASC, [import_time] DESC')
        ->setRowClass(get_called_class())
        ->fetchAssoc('filename');
        
        $files=array();
        $present_files=NFinder::findFiles("*.xml")->in(NEnvironment::getConfig('xml')->localRepository);
        foreach ($present_files as $file) {
            $baseName=$file->getBaseName();
            if(isset($files[$baseName])) {
                continue;
            }
            if(!isset($db_files[$baseName])) { //novy, jeste neimportovany
                $files[$baseName]=new File(array('filename'=>$baseName));
            }
            else {
                $files[$baseName]=$db_files[$baseName];
            }

                $files[$baseName]->create_time=$file->getCTime();
                $files[$baseName]->size=$file->getSize();
        }

        return $files;
    }

    public function hasBeenImported() {
        if(isset($this->database_name) && $this->database_name!=NULL) {
            return true;
        }
        else {
            return false;
        }
    }

}

?>
