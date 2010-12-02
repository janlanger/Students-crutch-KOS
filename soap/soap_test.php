<?php

include "./bootstrap-soap.php";

$soap = new SoapClient(NULL, array(
    "location" => 'http://bp.local/soap/',
    "uri" => 'http://bp.local/soap/'));
try {
    $soap->authenticate('berlicka', 'test');
    $soap->useRevision('testing');

    $return = ($soap->getStudentsInfo(array('langeja1')));
    var_dump($return);
} catch (SoapFault $e) {
    echo $soap->getLastError();
}

?>