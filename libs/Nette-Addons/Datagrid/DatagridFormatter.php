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
 * Provides column formatting support for datagrid
 *
 * @author Jan Langer
 */
class DatagridFormatter {
    /** no format is applied (default) */
    const PLAIN=NULL;
    /** format as date ($format should contain strftime format, default %d.%m.%Y %H:%M) */
    const DATE='date';
    /** apply callback (or anonymous function) / $format should format callback definition or instance of Closure */
    const CALLBACK='callback';
    /** show checkboxes (checked if data is true, false otherwise) */
    const CHECKBOX_YES_NO='checkboxYesNo';
    /** applies substitution ($format is array "input data"=>"output") */
    const SUBST='subst';

    private $type,
    $format;

    public function __construct($type=null, $format=null) {
        self::
        $this->type = $type;
        $this->format = $format;
    }

    /**
     * Aplies format definition
     * @param mixed $data data
     * @return mixed
     */
    public function format($data) {
        if (is_null($data)) {
            return "";
        }


        switch ($this->type) {
            case self::DATE :
                if ($this->format == NULL) {
                    $this->format = "%d.%m.%Y %H:%M";
                }

                if (!ctype_digit($data))
                    $data = strtotime($data);


                return strftime($this->format, $data);
                break;
            case self::CHECKBOX_YES_NO :
                $el=\Nette\Web\Html::el('input', array("type"=>'checkbox','disabled'=>'disabled'));
                if($data>0) {
                    $el->checked('checked');
                }
                return $el;
            case self::CALLBACK :
                if(is_callable($this->format)) {
                    call_user_func($this->format,$data);
                    break;
                }
                throw new InvalidArgumentException($this->format.' is not callable!');

            case self::PLAIN:
                return $data;
            case self::SUBST:
                if (isset($this->format[$data])) {
                    return $this->format[$data];
                }
                else
                    return $data;

            default:
                throw new NotImplementedException("Formatter " . $this->type . ' doesn\'t exists. ');
                break;
        }
    }

}

?>
