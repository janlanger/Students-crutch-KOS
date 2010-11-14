<?php

use Nette\Environment;
use Nette\Application\Route;

require_once APP_DIR . '/bootstrap.php';



// Step 3: Configure application
// 3a) get and setup a front controller

$application = Environment::getApplication();
$application->errorPresenter = 'Error';
//$application->catchExceptions = TRUE;
\Nella\Panels\CallbackPanel::register(array(
            '--robotloader' => array(
                'name' => "Rebuild RobotLoader Cache",
                'callback' => callback(Environment::getService('Nette\Loaders\RobotLoader'), 'rebuild'),
                'args' => array()
            ),
        ));

// Step 4: Setup application router
$router = $application->getRouter();



$router[] = new Route('index.php', array(
            'presenter' => 'Homepage',
            'action' => 'default',
                ), Route::ONE_WAY);

$router[] = new Route('<presenter>/<action>/<id>', array(
            'presenter' => 'Default',
            'action' => 'default',
            'id' => NULL,
        ));



// Step 5: Run the application!
$application->run();
