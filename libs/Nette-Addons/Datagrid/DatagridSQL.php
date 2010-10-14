<?php


class DatagridSQL {

    public $dataTable;
    
    public $columns;
    private $queryParams=array();

    private $limit;
    private $offset;


    private $query;


    public function __construct() {
        
    }

    /**
     * Executes SQL query to fetch data for actual page.
     * Limit and offset needs to be setted before calling.
     * @return DibiResult
     */
    public function fetchData() {
        $this->buildQuery();
        
        $query=$this->query;
        $query->limit($this->limit);
        $query->offset($this->offset);
        //$query->test();
        return $query->execute()->setRowClass('DatagridRow');


    }

    /**
     * Returns count of all rows
     * @return int
     */
    public function getItemsCount()  {
        $this->buildQuery();

        return $this->query->count();
    }

    private function buildQuery() {
        if($this->query instanceof DibiFluent) {
            return;
        }
        $this->query=dibi::select($this->columns)->from($this->dataTable);
        foreach ($this->queryParams as $x) {
            if($x['command']=='where' || $x['command']=='limit' || $x['command']=='offset') {
                continue;
            }

            $this->query->$x['command']($x['param']);
        }

    }

    public function limit($x) {
        $this->limit=$x;
    }
    public function offset($x) {
        $this->offset=$x;
    }

    public function __call($name,  $arguments) {
        if($this->query instanceof DibiFluent) {
            $this->query=null;
        }
        $a=func_get_args();
        
        $this->queryParams[]=array("command"=>$name,
            "param"=>$a[1][0]);
        
        return $this;
    }



}

?>
