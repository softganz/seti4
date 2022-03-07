<?php
function view_site_maintenance() {
	$ret='
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Website temporary offline</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Language" content="th">
<meta name="robots" content="noindex,nofollow">
</head>
<body>
<table width="100%" height="100%">
<tr>
	<td></td>
	<td width="80%">
		<div style="border:1px solid rgb(210, 210, 210);background-color: rgb(241, 241, 241);padding:30px;-moz-border-radius:10px;">
		<h1>Website temporary offline</h1>
		<p>'.cfg('web.offline_message').'</p>
		<p>'.(isset($GLOBALS['site_message'])?$GLOBALS['site_message']:'').'</p>
		<hr>
		<address>copyright <a href="http://'.$_SERVER['HTTP_HOST'].'">http://'.$_SERVER['HTTP_HOST'].'</a> Allright reserved.</address>
		</div>
	</td>
	<td></td>
</tr></table>
</body></html>';
	return $ret;
}
?>