<?php
/**
 * Description of DatagridAction
 *
 * @author Jan Langer, kontakt@janlanger.cz
 */
class DatagridAction extends Nette\Object {

    private $label,$link,$type,$question,$validator;

    public function __construct($link,$label,$type) {
        $this->label=$label;
        $this->link=$link;
        $this->type=$type;
    }


    /**
     * Set javascript confirmation when link is clicked
     * @param string $label action label
     * @param string $question question
     */
    public function setConfirmQuestion($question) {
        $this->question=$question;
        return $this;
    }

    public function setValidator($fnc) {
        if(!is_callable($fnc)) {
            throw new \InvalidStateException("Callback '$fnc' is not callable.");
        }

        $this->validator=$fnc;

    }

    public function getLabel() {
        return $this->label;
    }

    public function getLink() {
        return $this->link;
    }

    public function getType() {
        return $this->type;
    }

    public function getQuestion() {
        return $this->question;
    }

    public function hasValidator() {
        return (bool) count($this->validator);
    }

    public function validate($row) {
        $call=$this->validator;
        return $call($row,$this);
    }



}
?>
