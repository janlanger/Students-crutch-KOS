<?php

include "./bootstrap-soap.php";		//init prostredi (hlavne kvuli vecem jako Nette\Debug apod. - neni vubec nutne pouzivat

header('Content-type: text/plain'); //kvuli srozumitelnejsimu zobrazeni vystupu var_dump

$soap = new SoapClient(NULL, array(
    "location" => 'http://kos.janlanger.cz/soap/',
    "uri" => 'http://kos.janlanger.cz/soap/'));	//pripojeni k servise
try {
    $soap->authenticate('berlicka', 'test');	//identifikace a prihlaseni klienta
    $soap->useRevision('live');					//vyber revize (nepovinne, pokud se neuvede, pouzije se vychozi revize)

    $return = ($soap->getAllClasses());			//ziskani odpovedi metody s nazev getAllClases
    var_dump($return);

} catch (SoapFault $e) {

    echo $soap->getLastError();
	
}

?>