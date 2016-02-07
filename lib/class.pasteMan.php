<?php

defined ('in_bn6') or exit;

class pasteMan {
	
	private $_sql, $_layout;

	// Start us off by getting instances of classes localized
	public function __construct() {
		$this->_sql = MySQL::singleton();
		$this->_layout = Layout::singleton();
	}

	public function showForm($existing = false, $show_layout = true) {

		// If w're going to show the existing form with default values, this will be an array
		$show_existing = is_array($existing);

		// Sometimes we only want the form
		if ($show_layout) {

			// If we're not re-showing the form, check for spam bot attempts 
			if (!$show_existing && array_key_exists('do_paste', $_POST) && $_POST['do_paste'] == 'yes') {
				$this->handleFormSubmission();
				return;
			}
			
			// Show an error message?
			if ($show_existing && array_key_exists('error', $existing)) {
				switch($existing['error']) {
					case 'taken':
						$this->_layout->setError('That identifier is taken; pick a different one or click paste! again to be assigned a random ID.');
					break;

					case 'too_long':
						$this->_layout->setError('That identifier is too long; pick a shorter one (under 25 chars) or click paste! again to be assigned a random ID.');
					break;
				}
			}
		
		}
		
		// Reset anti spam stuff 
		$_SESSION['pk'] = md5(microtime(true).sha1(microtime(true)));
		$_SESSION['pv'] = md5(uniqid().microtime(true).sha1(microtime(true)));

		// Start layout if needed
		if ($show_layout)
			$this->_layout->head($show_existing && array_key_exists('reply', $existing) ? 'Reply to a paste' : 'Paste');
		
		// For real
		echo '
		<form action="'.WEB_PATH.'" method="post" id="sF">
			<div id="paste_form">
				<input type="hidden" name="do_paste" value="yes" />
				<div class="form_row"><label for="paste_type">Type:</label>
				<select id="paste_type" name="paste_type">';
				
				foreach ($this->getTypes(true) as $type)
					echo '
				<option'.($show_existing && $type[0] == $existing['lang'] ? ' selected="selected"' : '').' value="'.$type[0].'">'.$type[1].'</option>';

				echo '
				</select></div>
				<div class="form_row">
					<textarea cols="30" rows="5" id="paste_paste" name="paste_paste">'.($show_existing ? htmlspecialchars($existing['post']) : '').'</textarea>
				</div>
				<div class="form_row">
					<label for="cust_name">Optional custom Identifier:</label>
					<input type="text" id="cust_name" name="cust_name" />
					<script type="text/javascript">show_tt_link("tooltip_name");</script>
					<div id="tooltip_name" class="js_hide tooltip">This, if available, will be chosen for the URL instead of the random alphanumeric id we will choose for you.</div>
				</div>
				<div class="form_row">
					<label for="paste_private">Make this paste private?</label>
					<input onchange="toggle_pw(this);" type="checkbox" id="paste_private" name="paste_private" value="yes" />
					<script type="text/javascript">show_tt_link("tooltip_priv");</script>
					<div id="tooltip_priv" class="js_hide tooltip">Private pastes will not appear in public listings.</div>
				</div>
				<div id="priv_pw_box" class="form_row">
					<label for="paste_pw">Optional private paste password:</label>
					<input type="password" id="paste_pw" name="paste_pw" />
					<script type="text/javascript">show_tt_link("tooltip_pw");</script>
					<div id="tooltip_pw" class="js_hide tooltip">Passworded pastes require the password you submit to be viewed.</div>
				</div>
				<div class="btn_bar form_row">
					<input type="submit" value="Paste!" />
				</div>
			</div>
		</form>
		<script type="text/javascript">
			document.getElementById("priv_pw_box").style.display = "none";
			var f = document.getElementById("sF");
			var k = document.createElement("input");
			k.setAttribute("type", "hidden");
			k.setAttribute("name", "'.$_SESSION['pv'].'");
			k.setAttribute("value", "'. md5(sha1($_SESSION['pk'])).'");
			f.appendChild(k);
		</script>
		';

		if ($show_layout)
			$this->_layout->foot();
	}

	private function handleFormSubmission() {

		// Anti spam stuff
		if ($_POST[$_SESSION['pv']] != md5(sha1($_SESSION['pk'])))
			$this->_layout->error('Please submit again without using the back button.');

		// Reset anti spam vals
		$_SESSION['pv'] = '';
		$_SESSION['pk'] = '';

		// Validate language type, defaulting to plaintext
		if (!is_numeric($_POST['paste_type']) || !array_key_exists($_POST['paste_type'], $this->getTypes()))
			$_POST['paste_type'] = 0;

		// Trim potential worthlessness
		$_POST['paste_paste'] = trim($_POST['paste_paste']);

		// Don't tolerate worthlessness 
		if ($_POST['paste_paste'] == '')
			$this->_layout->error('No paste');
		
		// set default key
		$k = substr(base64_encode(md5(microtime(true).$_POST['paste_paste'])), 0, 6); 
		$cust_key = false;

		// Desiring custom identifier?
		if (array_key_exists('cust_name', $_POST) && trim($_POST['cust_name']) != '') {

			// Escape it
			$des_code = $this->_sql->escape(trim($_POST['cust_name']));

			// Kill custom characters
			$des_code = preg_replace('/([^0-9a-zA-Z\-\_]+)/', '_', $des_code);

			// Can't be un-short
			if (strlen($des_code) > 25) 
				return $this->showForm(array(
					'error' => 'too_long',
					'lang' => $_POST['paste_type'],
					'post' => $_POST['paste_paste']
				));
			
			// Test for uniqueness
			$existing_test = $this->_sql->query("select null from `pastes` where `code` = '$des_code' limit 1");

			// If it isn't, save password and redirect to it when we're done
			if ($this->_sql->num($existing_test) == 0) {
				$k = $des_code;
				$cust_key = true;
			}

			// Otherwise show form again with language and post saved
			else 
				return $this->showForm(array(
					'error' => 'taken',
					'lang' => $_POST['paste_type'],
					'post' => $_POST['paste_paste']
				));

			// Free ram if we're to be continuing
			$this->_sql->free($existing_test);
		}

		$i = 0;
		$failed_hashes = array();
		while (!$cust_key) {
			$check_collide = $this->_sql->query("select null from `pastes` where `code` = '$k' limit 1");
			if ($this->_sql->num($check_collide) == 0) {
				$this->_sql->free($check_collide);
					break;
			}
			else {
				$failed_hashes[] = $k;
				$i++;
				$k = substr(base64_encode(md5(microtime(true).$_POST['paste_paste'])), 0, 6); 
			}

			if ($i > 500) {
				$this->_layout->error('Error deciding random id. Admins have been notifies.');
				break; // redundant
			}
				
		}
		
		// Default private settings to public
		$private = array_key_exists('paste_private', $_POST) && $_POST['paste_private'] == 'yes' ? 1 : 0;
		$pw = '';

		// IP address, used for salting passwords and reference later, maybe
		$ip = $this->_sql->escape($_SERVER['REMOTE_ADDR']);

		// Making it passworded?
		if ($private === 1 && array_key_exists('paste_pw', $_POST) && trim($_POST['paste_pw']) != '') 
			$pw = md5($ip . trim($_POST['paste_pw'])); 
		
		// Escape the paste for db insertion, finally
		$_POST['paste_paste'] = $this->_sql->escape($_POST['paste_paste']);

		// Insert new paste
		$this->_sql->query("insert into `pastes` set 
			`password` = '$pw',
			`private` = '$private',
			`code` = '$k',
			`date` = unix_timestamp(),
			`language` = {$_POST['paste_type']},
			`paste` = '{$_POST['paste_paste']}',
			`ip` = '$ip'
		");

		// Normal numeric id
		$id = $this->_sql->lastid();
	
		// Save that here for future reference
		$_SESSION['my_pastes'][$id] = 1;
		
		// Go to it
		redirect(WEB_PATH. 'p/'.$k);
	}

	public function getTypes($view = false) {
		
		$types = array(
			array(0, 'Plain Text')
		);

		if ($view)
			$rep = array(
				'cpp' => 'C++',
				'csharp' => 'C#',
				'objc' => 'Objective C'
			);
	
		$omit = array(
			'php-brief', 'java5'
		);
		
		$f = glob(LOCAL_PATH.'geshi/geshi/*.php');
		$i = 1;
		foreach ($f as $l) {
			preg_match('/([^\/]+)\.php$/', $l, $la);
			if (in_array($la[1], $omit))
				continue;
			if ($view)
				$la[1] = array_key_exists($la[1], $rep) ? $rep[$la[1]] : $la[1];
			$types[] = array($i, $la[1]);
			$i++;
		}
			

		return $types;
	}

	public function showPaste() {

		// Nothing given? go home
		if (!array_key_exists('id', $_GET) && !array_key_exists('code', $_GET))
			redirect(WEB_PATH);

		// Using code?
		if (array_key_exists('code', $_GET)) {
			$code = $this->_sql->escape(trim($_GET['code']));
			$q = "`code` = '$code'";
		}

		// Using hexdec id?
		elseif (array_key_exists('id', $_GET)) {
			$id = $this->_sql->escape(trim($_GET['id']));
			$id = hexdec($id);

			// deliberately fail for new posts
			if ($id > 1080)
				$id = -1;

			$q = "`id` = '$id'";
		}

		// Try getting it
		$get = $this->_sql->query("
			select
				`date`, `language`, `paste`, `id`, `code`, `password`, `ip`, `private`
			from
				`pastes`
			where
				$q
			limit 1
		");

		// Not existant?
		if ($this->_sql->num($get) == 0)
			$this->_layout->error('Paste not existant');

		// Is; get info then free ram
		$info = $this->_sql->fetch_assoc($get);
		$this->_sql->free($info);
		
		// permalink
		$permalink = 'http://bn6.it/'.($info['code'] != '' ? 'p/' . htmlspecialchars($info['code']) : dechex($info['id']));

		// Is passworded
		if (!array_key_exists($info['id'], $_SESSION['my_pastes']) && strlen($info['password']) == 32) {

			// Show form if not submitted or if password is incorrect
			if (!array_key_exists('paste_pw', $_POST) || trim($info['password']) != md5($info['ip'].$_POST['paste_pw'])) {

				// If password submitted but incorrect say so
				if (array_key_exists('paste_pw', $_POST) && trim($info['password']) != md5($info['ip'].$_POST['paste_pw']))
					$this->_layout->setError('Invalid password');

				$this->_layout->head('Private Passworded Paste');
				echo '
				<form action="'.$permalink.'" method="post">
					<div id="pw_form">
						<div class="form_row"><label for="paste_pw">Password for paste:</label>
						<input type="password" id="paste_pw" name="paste_pw" /></div>
						<div class="form_row">
						<input type="submit" value="View" /></div>
					</div>
				</form>
				';
				$this->_layout->foot();
				return;
			}
		}

		// Update last viewed
		$this->_sql->query("update `pastes` set `last_view` = unix_timestamp() where `id` = '{$info['id']}' limit 1");

		// determine language
		$t = $this->getTypes(true);
		$type = 'plaintext';
		if ($info['language'] > 0) {
			foreach ($t as $ti) {
				if ($ti[0] == $info['language']) {
					$type = $ti[1];
					break;
				}
			}
		}

		// load geshi
		require_once LOCAL_PATH . 'geshi/geshi.php';
		$geshi = new Geshi($info['paste'], $type);
		$geshi->set_header_type(GESHI_HEADER_DIV);
		$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);

		// Auto focus permalink box, if was me who submitted it
		if (array_key_exists($info['id'], $_SESSION['my_pastes'])) {
			$this->_layout->setSelect('perma');
			unset($_SESSION['my_pastes'][$info['id']]);
		}

		// start layout
		$this->_layout->head($type);

		// give it
		echo '<div id="paste">
		<div id="paste_body">
		'.$geshi->parse_code().'
		</div>
		</div>
		<div id="paste_meta">
		<form action="" method="post" onsubmit="return false;" class="fake_form">
			<ul>
				<li><label for="perma">Permalink:</label> <input title="Protip: this field is auto-selected on any paste page for clickless copying" onfocus="this.select();" id="perma" type="text" class="uneditable" readonly="readonly" value="'.$permalink.'" /></li>
				<li>Date posted: '.date('m/d/Y @ h:i A', $info['date']).'</li>
				'.($info['private'] ? '<li>This paste is private.</li>' : '' ).'
			</ul>
		</form>
		</div>';
		
		// Show form with this paste stuff already in it
		echo '<h2>Revise/reply to paste</h2>';
		$this->showForm(array(
			'reply' => true,
			'lang' => $info['language'],
			'post' => stripslashes($info['paste'])
		), false);

		// end layout
		$this->_layout->foot();
	}
}
