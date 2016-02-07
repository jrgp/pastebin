<?php

defined ('in_bn6') or exit;

class Layout {

	private $_sql, $error = false, $notif = false, $autoSelect = false, $types = array();

	public function __construct() {
		self::$instance = $this;
		$this->_sql = MySQL::singleton();
		foreach (pasteMan::getTypes(true) as $type)
			$this->types[$type[0]] = $type[1]; 
	}

	private static $instance;

	public static function singleton() {
		return self::$instance;
	}

	public function head($title = false) {
		ob_start('ob_gzhandler');
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link href="'.WEB_PATH.'theme/styles.css" rel="stylesheet" type="text/css" />
	<title>Pastebin'.($title ? ' - '.$title : '').'</title>
	<script type="text/javascript" src="'.WEB_PATH.'theme/misc.js"></script>
</head>
<body id="bin6">
<div id="wrap">
	<div id="head">
		<a href="'.WEB_PATH.'" id="logo"><img src="'.WEB_PATH.'theme/logo.gif" alt="Bin6 It - The IPv6-only Pastebin" /></a>
	</div>
	<div id="content_wrap">
	<div id="div_line"></div>
	<h1>'.$title.'</h1>
	<div id="content">
		<div id="content_inner">
	'.($this->error ? '<div id="error">'.$this->error.'</div>' : '').'
	'.($this->notif ? '<div id="notif">'.$this->notif.'</div>' : '').'
	';
		}

		public function foot() {
			echo '
		</div>
	</div>
		<div id="side">
			<h3>Latest Pastes</h3>
			<ul id="latest_list">
			';
			
			$get_latest = $this->_sql->query("select `code`, `language`, `date` from `pastes` where `private` = '0' and `password` = '' order by `id` desc limit 10");

			while ($paste = $this->_sql->fetch_assoc($get_latest))
				echo '<li><a href="'.WEB_PATH.'p/'.$paste['code'].'">'.$this->types[$paste['language']].'</a><span>'.seconds_convert(time() - $paste['date']).' ago</span></li>';

			$this->_sql->free($get_latest);	

			echo '
			</ul>
		</div>
	</div>
	<div id="foot">
		<a href="'.WEB_PATH.'about">About bin6</a> | 
		<a href="'.WEB_PATH.'contact">Contact Us</a>
		<br /><span style="color : #aaaaaa">&copy; 2011 PuttyNuts Web Services</span>
	</div>
</div>
	'.($this->autoSelect ? '
	<script type="text/javascript">
		document.getElementById(\''.addslashes($this->autoSelect).'\').focus();
	</script>' : '').'
</body>
</html>
		';
		ob_end_flush();
	}

	// Append error text to top of page
	public function setError($msg) {
		$this->error = $msg;
	}
	
	// Append notif text to top of page
	public function setNotif($msg) {
		$this->notif = $msg;
	}

	public function setSelect($select) {
		$this->autoSelect = $select;
	}

	// Fatal error
	public function error($msg, $title = false) {
		$this->head($title ? $title : 'Error');
		echo '<p class="error">'.$msg.'</p>';
		$this->foot();
		exit;
	}

}
