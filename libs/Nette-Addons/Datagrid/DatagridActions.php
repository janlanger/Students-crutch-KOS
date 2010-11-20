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
 * Provides action support for datagrid
 *
 * @author Jan Langer
 */
class DatagridActions {
    
    private $actions=array();
    private $grid;

    public function __construct(\Nette\Application\Control $grid) {
        $this->grid=$grid;
    }

    /**
     * Adds action
     * @param array $link Action link definition ("action"=>Nette Action link, "param"=>column name used as row identifier)
     * @param string $label Action link title
     * @param string $type
     * @return DatagridActions fluent
     */
    public function add($link,$label,$type) {
        $action=new DatagridAction($link, $label, $type);
        $this->actions[]=$action;
        return $action;
    }

    

    /**
     * Gets count of actions
     * @return number of actions
     */
    public function getCount() {
        return count($this->actions);
    }

    /**
     * Generates actions links for row
     * @param DatagridRow $row
     * @return string HTML code
     */
    public function generate(DatagridRow $row) {
        if(!count($this->actions)) {
            return;
        }

        $links=array();

        foreach ($this->actions as $action) {
            $link=$action->link;
            if($action->hasValidator() && !$action->validate($row)) {
                continue;
            }

            if(isset($row[$link['param']])) {
                $link=$this->grid->presenter->link($link['action'], array($link['param']=>$row[$link['param']]));
            }
            else {
                $link=$this->grid->presenter->link($link['action']);
            }
            $tag=\Nette\Web\Html::el('a')->href($link)->setText($action->label);
            $tag->class=$action->type;
            if($action->question!="") {
                $tag->onclick('if(confirm(\''.$action->question.'\')) location.href=\''.$link.'\'; return(false);');
                $tag->href("#");
            }
            $links[]=$tag;

        }
        return $links;

    }

}
?>
