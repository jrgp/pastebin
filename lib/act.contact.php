<?php

defined ('in_bn6') or exit;

if (array_key_exists('do_contact', $_POST) && $_POST['do_contact'] == 'yes') {
	if ($_POST[$_SESSION['pv']] != md5(sha1($_SESSION['pk'])))
		$layout->error('Unverified');

	$_SESSION['pv'] = '';
	$_SESSION['pk'] = '';
	
	$_POST['contact_msg'] = trim($_POST['contact_msg']);

	if ($_POST['contact_msg'] == '')
		$layout->error('You didn\'t submit a message');

	$pem = trim($_POST['contact_email']) != '' ? $_POST['contact_email'] : '(none given)';

	$email = "Dear Admins, \nPerson's email: {$pem}\nMessage: {$_POST['contact_msg']}\n";
	$email = wordwrap($email, 70);

	$headers = array('Content-type: text/plain', 'From: Bin6 Contact Form <ryan+bin6-noreply@u13.net>');

	@mail('Joe Gillotti <joe@u13.net>, Ryan Rawdon <ryan@u13.net>', 'Bin6 Message', $email, implode("\r\n", $headers));

	$layout->head('Sent');
	echo '<p>Your message has been sent! Thanks for your submission.</p>';
	$layout->foot();

	exit;
}

$layout->head('Contact Us');

$_SESSION['pk'] = md5(microtime(true).sha1(microtime(true)));
$_SESSION['pv'] = md5(uniqid().microtime(true).sha1(microtime(true)));

echo '
<p>Have questions or comments? We\'d love to hear from you!</p>
<form action="'.WEB_PATH.'contact" method="post" id="cF">
	<div id="contact_form">
		<input type="hidden" name="do_contact" value="yes" />
		<div class="form_row" style="position: relative;">
			<label for="contact_email">e-mail:</label>
			<input style="position: absolute; left: 50px;" type="text" id="contact_email" name="contact_email" />
		</div>
		<div class="form_row">
			<textarea id="contact_msg" name="contact_msg"></textarea>
		</div>
		<div class="btn_bar form_row">
			<input type="submit" value="Send" />
		</div>

	</div>
</form>
<script type="text/javascript">
	var f = document.getElementById("cF");
	var k = document.createElement("input");
	k.setAttribute("type", "hidden");
	k.setAttribute("name", "'.$_SESSION['pv'].'");
	k.setAttribute("value", "'. md5(sha1($_SESSION['pk'])).'");
	f.appendChild(k);
</script>
';

$layout->foot();
