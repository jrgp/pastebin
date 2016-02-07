document.write('<style type="text/css">.js_hide {display: none;}</style>');

function toggle_pw (elem) {
	if (elem.checked) {
		document.getElementById('paste_pw').value = '';
		document.getElementById('priv_pw_box').style.display = 'block';
	}
	else {
		document.getElementById('priv_pw_box').style.display = 'none';
		document.getElementById('paste_pw').value = '';
	}
}
function show_tt_link(id_to_show) {
	document.write('<span class="show_tt"><span onclick="show_tt(\''+id_to_show+'\', this);" class="fake_link" title="Click for info">[?]</span></span>');
}
function show_tt(id_to_show, elem) {
	document.getElementById(id_to_show).style.display = 'block';
	elem.style.display = 'none';
	elem.parentNode.style.display = 'none';
}

