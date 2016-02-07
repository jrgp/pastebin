<?php

// Allow files to check if they're included
define('in_bn6', true);

// Start off session
session_name('bin6');
session_start();

// Store list of pasted id's here
$_SESSION['my_pastes'] = !is_array($_SESSION['my_pastes']) ? array() : $_SESSION['my_pastes'];

// Get paths set up 
define('LOCAL_PATH', dirname(__FILE__) . '/');
define('WEB_PATH', '/');

// Our current action
define('CURRENT_ACTION', array_key_exists('act', $_GET) && preg_match('/^[a-z\-\_]+$/', $_GET['act']) == 1 && is_file(LOCAL_PATH.'lib/act.'.$_GET['act'].'.php') ? $_GET['act'] : 'home');

// Load config
require_once LOCAL_PATH . 'config.php';

// And misc useful stuff
require_once LOCAL_PATH . 'lib/functions.common.php';

// Load classes
$db = new MySQL($dbc);
$layout = new Layout();

// Load current action
require_once LOCAL_PATH . 'lib/act.'.CURRENT_ACTION.'.php';
