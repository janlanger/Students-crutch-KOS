<?php

class Datagrid extends NControl {

    private $data;
    /**
     *
     * @var NPaginator
     */
    private $paginator;
    private $columns;
    /**
     *
     * @var DatagridSQL
     */
    private $sql;


    private $sortBy;
    private $sortOrder='ASC';

    private $showHeader=true;
    private $enableSort=true;

    private $actions=NULL;

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

    public function render() {
        echo $this->__toString();
    }

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

    public function setDefaultSort($by,$order='ASC') {

        if(!empty($this->sortBy) && !is_null($this->sortBy)) return;
        $this->sortBy=$by;
        $this->sortOrder=strtoupper($order);
    }

    public function setItemsPerPage($x) { $this->paginator->setItemsPerPage($x); }
    

    public function setDataTable($dataTable) {
        $this->sql->dataTable=$dataTable;
    }

    public function setColumns($arr) {
        foreach($arr as $key=>$val) {
            $this->addColumn($key,$val);
        }
    }

    public function addColumn($key,$title) {
        if(!isset($this->columns[$key])) {
            $this->columns[$key]=new DatagridColumn($key,$title);
        }
    }

    public function setColumnFormat($column,$type,$format=null) {
        $this->columns[$column]->setFormatter($type,$format);
    }

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

    public function addColumnFormatter($col,$type,$format=null) {
        $this->columns[$col]->setFormatter($type,$format);
    }

    public function getSql() {
        return $this->sql;
    }

    public function setShowHeader($x) {
        $this->showHeader=$x;
    }

    public function setEnableSort($x) {
        $this->enableSort=$x;
    }

    public function addAction($link,$label,$type=NULL) {
        return $this->actions->add($link,$label,$type);
        //DatagridRow::addAction($type, $this->presenter->link($link), $title, $useJsConfirm);
    }

    public function hasActions() {
        return $this->actions->getCount();
    }

    public function generateActions(DatagridRow $row) {
        return $this->actions->generate($row);
    }


}
?>
