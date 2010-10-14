<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DatagridRow
 *
 * @author Honza
 */
class DatagridRow extends DibiRow {

    private static $actions=array();

    

    public function __construct($arr) {
        parent::__construct($arr);
        
    }

    public static function addAction($type,$link,$title=NULL,$useJsConfirm=FALSE) {
        self::$actions[]=array(
            "type"=>strtolower($type),
            "link"=>  $link,
            "title"=>(is_null($title)?'['.strtoupper($type).']':$title),
            "useJsConfirm"=>$useJsConfirm
        );
    }

    public function getActionLinks() {

        

        if(!(count(self::$actions)>0)) {
            return;
        }
        
        $search=array();
        foreach($this as $key=>$item) {
            $search[]='%'.$key.'%';
            $replace[]=$item;
        }
        foreach(self::$actions as $item) {
            $link=NHtml::el('a')->href($item['link'])
                    ->setText($item['title']);
            $link->class[]=$item['type'];


            /*if($item['useJsConfirm'] || $item['type']=='del' || $item['type']=='delete') {
                $return[]=NHtml::el('a')->onclick('if(confirm(\'Opravdu chcete provést tuto akci? ('.$item['type'].')\')) location.href=\''.str_ireplace($search, $replace, $item['link']).'\'; return(false);')
                    ->href("#")
                    ->setHtml(
                    NHtml::el('img')
                    ->src(self::$actionsIconsMap[$ico])->title($item['title'])
                    ->alt($item['title']));
            }
            else {
                $return[]=NHtml::el('a')->href($item['link'])
                    ->setText($item['title'])->class=$item['type'];
            }
            dump($item);*/
            $return[]=$link;
        }
        return $return;
    }

    
    
}
?>
