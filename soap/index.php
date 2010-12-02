<?php

include "./bootstrap-soap.php";
session_start();

$server=new SoapServer(NULL,array('uri'=>'http://bp.local/soap/'));
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