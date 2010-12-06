<?php
/**
 * Description of RevisonComparator
 *
 * @author Jan Langer, kontakt@janlanger.cz
 */
class RevisonComparator extends \Nette\Application\Control {

    private $revisions;
    /** @var Revision */
    private $first;
    /** @var Revision */
    private $second;

    public function getFirst() {
        return $this->first;
    }

    public function getSecond() {
        return $this->second;
    }

    
    public function getRevisions() {
        return $this->revisions;
    }

    public function setRevisions($revisions) {
        $this->revisions = $revisions;
    }

    
    public function render() {

        if(count($this->revisions)!=2) {
            throw new InvalidStateException('Revisions to comapre are not set.');
        }
        $this->template->setFile(__DIR__.'/template.latte');
        $_this=$this;
        $this->template->registerHelper('dbToRevision',function($data) use ($_this) {
            if($_this->first->db_name==$data) {
                return $_this->first->alias;
            }
            if($_this->second->db_name==$data) {
                return $_this->second->alias;
            }
        });
        $db=$this->getPresenter()->getApplication()->getService('IDatabaseManager');
        $revisons=Revision::find("[rev_id] IN (".implode(",",$this->revisions).")");
        $this->first=$revisons[$this->revisions[0]];
        $this->second=$revisons[$this->revisions[1]];
        
        
        $rev1=$this->first->getTables();
        
        $rev2=$this->second->getTables();

        $this->template->tables=$tables=array_unique(array_merge(array_keys($rev1),  array_keys($rev2)));
        
        $this->template->structure=$structure=($this->compareStructure());

        $data=array();
         $this->template->columns=array();
        foreach($tables as $table) {
            if(isset($rev1[$table]) && isset($rev2[$table])) {
                $this->template->columns[$table]=$columns
                        =array_intersect(array_keys($rev1[$table]['columns']),  array_keys($rev2[$table]['columns']));
                
                $data[$table]=$this->compareData($table,$columns);
            }
        }
        $this->template->rev1=  $this->first;
        $this->template->rev2=  $this->second;

        $this->template->data=$data;
        $this->template->render();
    }

    public function compareStructure() {
        $diff=new DbDiff();
        return $diff->compare($this->first->db_name, $this->second->db_name);
    }

    public function compareData($table,$columns) {
        $data1=dibi::select($columns)->from($this->first->db_name.".".$table);
        $data2=dibi::select($columns)->from($this->second->db_name.".".$table);
        if($this->first->getDefinition()->hasCondition($table)) {
            $data2->where($this->first->getDefinition()->getCondition($table));
        }
        if($this->second->getDefinition()->hasCondition($table)) {
            $data1->where($this->second->getDefinition()->getCondition($table));
        }
        if(in_array('id', $columns)) {
            $data1=$data1->fetchAssoc('id');
            $data2=$data2->fetchAssoc('id');
        }
        else {
            $data1=$data1->fetchAssoc(@reset($columns));
            $data2=$data2->fetchAssoc(@reset($columns));
        }


        
        $result=array();
        array_walk($data1, function($item,$key) use ($data2,&$result) {
            if(!isset($data2[$key]) || serialize($item)!=serialize($data2[$key]))
                $result[$key]=array($item,isset($data2[$key])?$data2[$key]:NULL);
        });
        return $result;

    }


}
?>
