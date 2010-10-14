<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DatagridActions
 *
 * @author Honza
 */
class DatagridActions {
    
    private $actions=array();
    private $grid;

    public function __construct(NControl $grid) {
        $this->grid=$grid;
    }


    
    
    public function add($link,$label,$type) {
        $this->actions[$label]=array('link'=>$link, 'label'=>$label,'type'=>$type);
        return $this;
    }
    
    public function setConfirmQuestion($label,$question) {
        $this->actions[$label]['question']=$question;
    }

    public function getCount() {
        return count($this->actions);
    }

    public function generate(DatagridRow $row) {
        if(!count($this->actions)) {
            return;
        }

        $links=array();

        foreach ($this->actions as $action) {
            $link=$action['link'];

            if(isset($row[$link['param']])) {
                $link=$this->grid->presenter->link($link['action'], array($link['param']=>$row[$link['param']]));
            }
            else {
                $link=$this->grid->presenter->link($link['action']);
            }
            $tag=NHtml::el('a')->href($link)->setText($action['label']);
            $tag->class=$action['type'];
            if(isset($action['question'])) {
                $tag->onclick('if(confirm(\''.$action['question'].'\')) location.href=\''.$link.'\'; return(false);');
                $tag->href("#");
            }
            $links[]=$tag;

        }
        return $links;

    }

}
?>
