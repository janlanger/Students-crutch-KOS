<?php

include "./bootstrap-soap.php";
session_start();

$client=\Nette\Environment::getHttpRequest()->getQuery('client');
if($client=="") {
    return; //exit
}

$manager=new ServiceManager();
$operations=$manager->getOperationsFor($client);


$server=new SoapServer(NULL,array('uri'=>'http://bp.local/soap/berlicka'));
$server->setClass('ServiceHandler');

//$server->addFunction(array('ServiceHandler::echoX'));
//$server->addFunction(array('ServiceHandler::echoX'));
$server->setPersistence(SOAP_PERSISTENCE_SESSION);
$server->handle();

/*$service=new WSService(array(
   'classes' => array('ServiceHandler'=>array("operations"=>$operations)),

));
$service->reply();*/
?>