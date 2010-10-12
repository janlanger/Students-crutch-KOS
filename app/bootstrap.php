<?php

/**
 * My NApplication bootstrap file.
 *
 * @copyright  Copyright (c) 2010 John Doe
 * @package    MyApplication
 */



// REMOVE THIS LINE
if (!is_file(LIBS_DIR . '/Nette/loader.php')) die('Copy Nette Framework to /libs/ directory.');


// Step 1: Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
require LIBS_DIR . '/Nette/loader.php';



// Step 2: Configure environment
// 2a) enable NDebug for better exception and error visualisation
NDebug::enable(NULL,APP_DIR.'/log/');
NDebug::$strictMode=true;
NDebug::$maxDepth=5;

// 2b) load configuration from config.ini file
NEnvironment::loadConfig();

dibi::connect(NEnvironment::getConfig('database'));

