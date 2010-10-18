<?php

/**
 * Description of Functions
 *
 * @author Honza
 */
class OperationPresenter extends BasePresenter {


	public function actionDefault($app_id) {
            
            if($app_id>0) {
                $app=@reset(Application::find(array("app_id"=>$app_id)));
            }
            $this['header']->addTitle("Správa WS rozhraní aplikace ".$app['name']);

            $this->template->operations=Operation::getWithSQLs(array("app_id"=>$app_id));
            $this->template->revisions=Revision::find(array("app_id"=>$app_id));
	}
        
}