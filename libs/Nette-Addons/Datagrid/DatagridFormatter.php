<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of DatagridFormatter
 *
 * @author Honza
 */
class DatagridFormatter {
    const PLAIN=NULL; //default
    const DATE='date';
    const YES_NO='yesNo';
    const CHECKBOX_YES_NO='checkboxYesNo';
    const SUBST='subst';

    private $type,
            $format;

    public function __construct($type=null,$format=null) {
        $this->type=$type;
        $this->format=$format;
    }


    public function format($data) {
        if(is_null($data)) {
            return "";
        }
        

        switch ($this->type) {
            case self::DATE :
                if($this->format==NULL) {
                    $this->format="%d.%m.%Y %H:%M";
                }

                if(!ctype_digit($data))
                    $data=strtotime($data);
                

                return strftime($this->format, $data);
                break;

            case self::PLAIN:
                return $data;
            case self::SUBST:
                if(isset($this->format[$data])) {
                    return $this->format[$data];
                }
                else
                    return $data;

            default:
                throw new NotSupportedException("Formatter ".$format['type'].' doesn\'t exists. ');
                break;
        }
    }
}
?>
