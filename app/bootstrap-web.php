<?php

require_once APP_DIR.'/bootstrap.php';



// Step 3: Configure application
// 3a) get and setup a front controller
$application = NEnvironment::getApplication();
$application->errorPresenter = 'Error';
//$application->catchExceptions = TRUE;

PresenterTreePanel::register();

// Step 4: Setup application router
$router = $application->getRouter();

$router[] = new NCliRouter(array(
    "presenter"=>'Console',
    "action"=>"default"
));

$router[] = new NRoute('index.php', array(
	'presenter' => 'Homepage',
	'action' => 'default',
), NRoute::ONE_WAY);

$router[] = new NRoute('<presenter>/<action>/<id>', array(
	'presenter' => 'Default',
	'action' => 'default',
	'id' => NULL,
));



// Step 5: Run the application!
$application->run();
