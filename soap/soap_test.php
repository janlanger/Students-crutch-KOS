<?php

include "./bootstrap-soap.php";
/*$requestPayloadString = <<<XML
<ns1:echoX xmlns:ns1="http://wso2.org/wsfphp/samples"><text>Hello World!</text></ns1:echoString>
XML;

$client=new WSClient(array("to"=>'http://bp.local/soap/berlicka'));
$responseMessage=$client->request($requestPayloadString);

        printf("Response = %s <br>", htmlspecialchars($responseMessage->str));*/
Ndebug::timer();
$soap=new SoapClient(NULL,array("location"=>'http://bp.local/soap/berlicka',"uri"=>'http://bp.local/soap/berlicka'));

$soap->authenticate('berlicka','test');
dump($soap->useRevision('testing'));
try{
$return=($soap->getStudentsInfo(355981000,'B101'));
} catch(SoapFault $e) {
    dump($e);
    
}
dump($soap->getLastError());
echo NDebug::timer();


?>