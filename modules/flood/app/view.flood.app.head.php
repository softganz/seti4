<?php
function view_flood_app_head($showToolbar=true) {
	set_theme('app');
	$ret.='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-EN">
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset='.cfg('client.characterset').'" />
<meta http-equiv="Content-Language" content="th" />
<title>'.cfg('web.title').'</title>
<meta name="generator" content="www.softganz.com" />
<meta name="formatter" content="Little Bear by SoftGanz Group" />
<meta name="viewport" content="initial-scale=1.0, maximum-scale=3, user-scalable=yes" />
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<link rel="stylesheet" type="text/css" href="'.cfg('theme.stylesheet').cfg('theme.stylesheet.para').'" />
<script type="text/javascript">var url="'._url.'";</script>
<script type="text/javascript" src="/js/jquery-3.3.js?v=0.01"></script>
<script type="text/javascript" src="/js/jquery.colorbox.js"></script>
<script type="text/javascript" src="/flood/js.flood.js?v=0.02"></script>
<script type="text/javascript" src="/flood/js.flood.event.js?v=0.02"></script>
<script type="text/javascript" src="/js/jquery.form.js"></script>
<script type="text/javascript" src="/flood/js.flood.app.js?v=0.01"></script>
<script type="text/javascript" src="/js/jquery.ui.map.js"></script>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key='.cfg('gmapkey').'&amp;language=th"></script>
<script type="text/javascript" src="/js/gmaps.js"></script>
<script type="text/javascript" src="/flood/js.flood.markerwithlabel.js"></script>
<script type="text/javascript" src="/js/jquery.jeditable.js"></script>
<script type="text/javascript" src="/js/library-3.00.js?v=0.10"></script>

<link rel="icon" href="https://hatyaicityclimate.org/ACCCRN.png" sizes="16x16" type="image/png" />
</head>
<body>
<div class="debug">'.(user_access('access debugging program')?debugMsg():'').'</div>
<div id="primary">
<div id="main">'._NL;
	if ($showToolbar) $ret.=R::View('flood.app.toolbar')._NL;
	return $ret;
}
?>