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
 * Provides Datagrid
 * @author Jan Langer
 */
class Datagrid extends NControl {

    private $data;
    /** @var NPaginator */
    private $paginator;
    /** @var array */
    private $columns;
    /** @var DatagridSQL */
    private $sql;


    private $sortBy;
    private $sortOrder='ASC';

    private $showHeader=true;
    private $enableSort=true;

    private $actions=NULL;

    /**
     *
     * @param IComponentContainer $parent
     * @param string $name
     */
    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);
        $this->paginator=new NPaginator();
        $this->paginator->setItemsPerPage(50);
        $this->sql=new DatagridSQL();
        $this->actions=new DatagridActions($this);

        if(isset($_GET['start'])) {

            $this->paginator->setPage($_GET['start']);
        }
        if(isset($_GET['sort'])) {
            $this->sortBy=$_GET['sort'];
        }
        if(isset($_GET['desc'])) {
            $this->sortOrder='DESC';
        }

    }

    /**
     * Renders Datagrid
     */
    public function render() {
        echo $this->__toString();
    }

    /**
     * Returns HTML code of the Datagrid
     * @return string HTML code
     */
    public function __toString() {
        $uriParams='';
        $a=explode("?",$_SERVER['REQUEST_URI']);
        if(isset($a[1])) {
            $a=explode("&", $a[1]);
            $c=array();
            foreach($a as $d) {
                $b=explode("=", $d);
                if($b[0]=='start' || $b[0]=='sort' || $b[0]=='desc' || $b[0]=='start') {
                    continue;
                }
                $c[]=$d;
            }
            $uriParams=implode("&amp;", $c).'&amp;';
        }

        if(is_null($this->sql->columns)) {
            $this->sql->columns=array_keys($this->columns);
        }
        $this->paginator->setItemCount($this->sql->getItemsCount());
        $this->sql->limit($this->paginator->getItemsPerPage());
        $this->sql->offset($this->paginator->getOffset());

        $this->setDefaultSort(@reset(array_keys($this->columns)), 'ASC');

        $this->sql->orderBy(array($this->sortBy => $this->sortOrder));

        
        
        $this->data=$this->sql->fetchData()->fetchAll();
        $this->template->registerHelper('plural', function($n, $one, $four, $more) {
                    if (!is_int($n))
                        throw new InvalidArgumentException("Argument \$n must be integer, " . (is_object($n) ? get_class($n) : gettype($n)) . " given.");
                    if ($n == 0 || $n > 4)
                        return $more;
                    if ($n > 1 && $n < 5)
                        return $four;
                    if ($n == 1)
                        return $one;
                }
        );

        
        $this->template->setFile(dirname(__FILE__).'/template/datagrid.phtml');

        $this->template->showHeader=$this->showHeader;
        $this->template->uriParams=$uriParams;
        $this->template->paginator=$this->paginator;
        $this->template->sortBy=$this->sortBy;
        $this->template->sortOrder=$this->sortOrder;
        $this->template->columns=$this->columns;
        $this->template->enableSort=$this->enableSort;
        $this->template->data=$this->data;
        $this->template->grid=$this;
        
        return $this->template->__toString(TRUE);
    }

    /**
     * Sets default sort column if no sort is selected in GUI
     * @param string $by column
     * @param string $order ASC/DESC [optional]
     * @return Datagrid fluent interface
     */
    public function setDefaultSort($by,$order='ASC') {

        if($this->sortBy=="" || is_null($this->sortBy)) {
        $this->sortBy=$by;
        $this->sortOrder=strtoupper($order);
        }
        return $this;
    }

    /**
     * Sets maximum items per page
     * @param int $x
     * @return Datagrid fluent interface
     */
    public function setItemsPerPage($x) {
        $this->paginator->setItemsPerPage($x);
        return $this;
    }

    /**
     * Sets data table for datagrid data
     * @param string $dataTable
     * @return Datagrid fluent interface
     */
    public function setDataTable($dataTable) {
        $this->sql->dataTable=$dataTable;
        return $this;
    }

    /**
     * Sets columns which will be rendered.
     * Only these columns will be selected from database, use getSQL->columns if you want some others.
     * @param array $arr column list - "column name from DB" => "alias showed in title"
     * @return Datagrid fluent interface
     */
    public function setColumns($arr) {
        foreach($arr as $key=>$val) {
            $this->addColumn($key,$val);
        }
        return $this;
    }

    /**
     * Add column to datagrid.
     * @param string $key Column name from database.
     * @param string $title Column title showed in grid.
     * @return Datagrid fluent interface
     */
    public function addColumn($key,$title) {
        if(!isset($this->columns[$key])) {
            $this->columns[$key]=new DatagridColumn($key,$title);
        }
        return $this;
    }

    /**
     * Set output formater for data.
     * @param string $column column name (database name).
     * @param string $type Type of formatter - use DatagridFormatter consts.
     * @param mixed $format Data for formatter
     * @see DatagridFormatter
     * @return Datagrid fluent interface
     */
    public function setColumnFormat($column,$type,$format=null) {
        $this->columns[$column]->setFormatter($type,$format);
        return $this;
    }

    /**
     * Template helper (i dont known hot this works)
     * @param NPaginator $pages
     * @param <type> $steps
     * @param <type> $surround
     * @return <type>
     */
    public static function getSteps(NPaginator $pages, $steps = 4, $surround = 4) {
        $lastPage = $pages->getPageCount() - 1;
        $page = min(max(0, $pages->getPage() - $pages->getBase()), max(0, $pages->getPageCount() - 1));
        if ($lastPage < 1) return array($page + $pages->base);

        $surround = max(0, $surround);
        $arr = range(max(0, $page - $surround) + $pages->base, min($lastPage, $page + $surround) + $pages->base);

        $steps = max(1, $steps - 1);
        if($lastPage == $steps + 1) $steps++;
        for ($i = 0; $i <= $steps; $i++) $arr[] = (int) round($lastPage / $steps * $i) + $pages->base;
        sort($arr);
        $arr = array_values(array_unique($arr));
        // test jestli je pocet tlacitek o jedno mensi nez pocet stranek
        // Pokud ano, tak se vygeneruji vsechny strankovaci tlacitka
        if(count($arr) == $lastPage) {
            $arr = array();
            for ($i = 0; $i <= $lastPage; $i++) $arr[] = $i + $pages->base;
        }
        return $arr;
    }

    /**
     * Returns SQL Datagrid provider
     * @return DatagridSQL
     */
    public function getSql() {
        return $this->sql;
    }

    /**
     * Should datagrid header be rendered?
     * @param boolean $x
     * @return Datagrid fluent
     */
    public function setShowHeader($x) {
        $this->showHeader=$x;
        return $this;
    }

    /**
     * Should user sorting be used?
     * @param boolen $x
     * @return Datagrid fluent
     */
    public function setEnableSort($x) {
        $this->enableSort=$x;
        return $this;
    }

    /**
     * Adds action
     * @param array $link Action link definition ("action"=>Nette Action link, "param"=>column name used as row identifier)
     * @param string $label Action link title
     * @param string $type
     * @return DatagridActions fluent
     */
    public function addAction($link,$label,$type=NULL) {
        return $this->actions->add($link,$label,$type);
    }

    /**
     * Has datagrid any defined actions (eg. should action column be rendered?)
     * @return int number of actions defined
     */
    public function hasActions() {
        return $this->actions->getCount();
    }

    /**
     * Generates actions links for row
     * @param DatagridRow $row
     * @return string HTML code
     */
    public function generateActions(DatagridRow $row) {
        return $this->actions->generate($row);
    }


}
?>
