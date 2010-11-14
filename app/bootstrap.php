<?php

if (defined(PHP_VERSION_ID) || PHP_VERSION_ID < 50300) {
    echo 'Sorry, but this application requires PHP 5.3 or later.';
    exit;
}

/**
 * My NApplication bootstrap file.
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */
use Nette\Debug;
use Nette\Environment;

// REMOVE THIS LINE
if (!is_file(LIBS_DIR . '/Nette/loader.php'))
    die('Copy Nette Framework to /libs/ directory.');


// Step 1: Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
require LIBS_DIR . '/Nette/loader.php';



// Step 2: Configure environment
// 2a) enable \Nette\Debug for better exception and error visualisation
Debug::enable(NULL, APP_DIR . '/log/');
Debug::$strictMode = true;
Debug::$maxDepth = 5;

// 2b) load configuration from config.ini file
Environment::loadConfig();

dibi::connect(Environment::getConfig('database'));
dibi::addSubst('main', "rozvrh_main.");

\Nella\Panels\VersionPanel::register();

