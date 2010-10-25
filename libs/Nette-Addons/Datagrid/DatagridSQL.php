<?php
/* The MIT Licence
 *
 * Copyright (c) 2010 Jan langer <kontakt@janlanger.cz>
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * Provides SQL support for datagrid
 * @author Jan Langer
 */
class DatagridSQL {

    /** @var string table */
    public $dataTable;

    /** @var array comulns definition */
    public $columns;
    private $queryParams=array();

    private $limit;
    private $offset;


    private $query;

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
            if(/*$x['command']=='where' ||*/ $x['command']=='limit' || $x['command']=='offset') {
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
