<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ForeignKeyForm
 *
 * @author Honza
 */
class ForeignKeyForm extends \Nette\Application\AppForm {
    //put your code here
    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);
        $tables=$this->getPresenter()->getComponent('importer');

        dump($tables);

    }

}
?>
