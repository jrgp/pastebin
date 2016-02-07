<?php

defined ('in_bn6') or exit;

function __autoload($class) {
	if (is_file(LOCAL_PATH.'lib/class.'.$class.'.php'))
		require_once LOCAL_PATH.'lib/class.'.$class.'.php';
	else {
		if (class_exists('Layout'))
			Layout::Singleton()->error('Class file not found: '.$class);

		else
			exit ('Class file not found: '.$class);
	}
}


function redirect($p) {
	header('Location: '.$p);
	exit;
}

function seconds_convert($time) {

	// Method here heavily based on freebsd's time source
	$time += $time > 60 ? 30 : 0;
	$days = floor($time / 86400);
	$time %= 86400;
	$hours = floor($time / 3600);
	$time %= 3600;
	$minutes = floor($time / 60);
	$seconds = floor($time % 60);

	// Send out formatted string
	$return = array();
	
	
	if ($days > 0)
		$return[] = $days.'d';

	if ($hours > 0)
		$return[] = $hours.'h';

	if ($minutes > 0)
		$return[] = $minutes.'m';
	
	if ($seconds > 0)
		$return[] = $seconds. 's';

	return implode(' ', $return);
} 
