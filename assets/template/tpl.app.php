<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml" dir="ltr" lang="en-EN">
<head profile="http://gmpg.org/xfn/11">
<?php if (cfg('web.init')) {ob_start();eval ('?>'.cfg('web.init'));echo ob_get_clean()._NL;}?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo cfg('client.characterset');?>">
<meta http-equiv="Content-Language" content="th">
<title><?php echo ($GLOBALS['title']?$GLOBALS['title']:'') .($GLOBALS['title'] && cfg('web.title') ? ' | ':'').cfg('web.title');?></title>
<meta name="generator" content="www.softganz.com">
<meta name="formatter" content="Little Bear by SoftGanz Group">
<meta name="author" content="<?php echo cfg('web.title');?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">

<link rel="stylesheet" type="text/css" href="<?php echo cfg('theme.stylesheet').cfg('theme.stylesheet.para');?>">
<?php
if (file_exists(cfg('theme.absfolder').'/style.inc.css')) echo '<link rel="stylesheet" type="text/css" href="'.cfg('theme').'style.inc.css">'._NL;
if (is_home() && file_exists(cfg('theme.absfolder').'/home.css')) echo '<link rel="stylesheet" type="text/css" href="'.cfg('theme').'home.css">'._NL;
if (cfg('theme.'.cfg('theme.name').'.css')) echo '<link rel="stylesheet" type="text/css" href="'.cfg('theme').'theme.css">'._NL;
if (isset($_REQUEST['bw']) && $_REQUEST['bw']=='0') {
	;//
} else if (cfg('theme.backandwhite')) {
	echo '<link rel="stylesheet" type="text/css" href="http://softganz.com/themes/bw/bw.css">'._NL;
}
?>
<script type="text/javascript">var isRunOnHost=<?php echo cfg('server')?'true':'false';?></script>
<script type="text/javascript">var url="<?php echo _URL;?>";</script>
<script type="text/javascript" src="<?php echo cfg('library');?>jquery<?php echo cfg('jquery.version')?'-'.cfg('jquery.version'):'';?>.js"></script>
<script type="text/javascript" src="<?php echo cfg('library');?>jquery-ui.min<?php echo cfg('jquery.version')?'-'.cfg('jquery.version'):'';?>.js"></script>
<script type="text/javascript" src="<?php echo cfg('clean_url')?'js/js.':cfg('library');?>library<?php echo cfg('library.version')?'-'.cfg('library.version'):'';?>.js<?php echo cfg('theme.stylesheet.para');?>"></script>
<script type="text/javascript" src="<?php echo cfg('library');?>js/jquery.colorbox.js"></script>

<?php
if (cfg('web.iefix')) {
//	echo '<script src="'.cfg('library').'modernizr.js"></script>';
	echo '<!-- fix css bug on IE6,7 download from http://selectivizr.com/ -->'._NL.'<!--[if lt IE 9]><script src="'.cfg('library').'IE8.js"></script><![endif]-->'._NL._NL;
}

echo implode(_NL,head());
?>
</head>

<body<?php echo
	(cfg('page_id') ? ' id="'.cfg('page_id').'"' : '')
	. ' class="'.page_class().'"'
	. (cfg('body_attr') ? ' '.cfg('body_attr'):'')
	;?>>
<?php if (cfg('web.fullpage'))  {echo $GLOBALS['request_result'].'</body>'._NL.'</html>';return;}?>
<?php if (cfg('web.navigator')) echo is_string(cfg('web.navigator'))?cfg('web.navigator'):'<ul><li><a href="'.url().'">Home</a></li><li><a href="'.url('help').'">Help</a></li><li><a href="'.url('user').'">Login</a></li><li><a href="'.url('user/register').'">Register</a></li></ul>';?>
<div id="page-wrapper" class="page -page">
<?php if (cfg('core.message')) echo '<div id="core-message">'.cfg('core.message').'</div>'._NL;?>
<div id="header-wrapper" class="page -header">
<div class="wrapper">
<header>
<h1><a href="<?php echo url(cfg('web.url'));?>" title="<?php echo htmlspecialchars(cfg('web.title'));?>"><span><?php echo cfg('web.title');?></span></a></h1>
<?php if (cfg('web.slogan')) echo '<p>'.cfg('web.slogan').'</p>'._NL;?>
</header>
<?php echo process_widget(eval_php(cfg('navigator'),_NL,_NL));?>
</div><!--wrapper-->
</div><!--header-wrapper-->
<div id="content-wrapper" class="page -content">
<noscript><p class="notify">ขณะนี้ Browser ไม่ได้เปิด JavaScript กรุณาเปิดใช้งาน JavaScript เพื่อให้เว็บไซต์แสดงผลได้สมบูรณ์แบบ</p></noscript>
<!--[if lte IE 8]>
<div class="notify" style="margin:10px 0;">เว็บไซท์นี้ไม่สนับสนุนการใช้งานบน IE6,IE7,IE8 อีกแล้ว!!!!  คุณควรเปลี่ยนไปใช้ Firefox หรือ Google Chrome หรือ ทำการปรับปรุงรุ่นของเบราเซอร์ให้เป็นรุ่นปัจจุบันโดย<a href="http://browsehappy.com/" target="_blank">คลิกที่นี่</a> หรือ <a href="http://windows.microsoft.com/en-us/internet-explorer/download-ie" target="_blank">ดาวน์โหลด IE รุ่นล่าสุด</a></div>
<![endif]-->
<div class="debug"><?php echo 1||user_access('access debugging program')?debugMsg():'';?></div>
<?php if (cfg('web.primary')) echo '<div id="primary" class="page -primary">'._NL;?>
<?php if (cfg('web.message')) echo '<div id="web-message">'.cfg('web.message').'</div>'._NL;?>
<?php echo $GLOBALS['request_result'];?>
<?php if (cfg('web.primary')) echo '</div><!--primary-->'._NL;?>

<?php if (is_string(cfg('web.secondary'))) echo process_widget(eval_php(cfg('web.secondary'),_NL.'<div id="secondary" class="page -secondary">'._NL,_NL.'</div><!--secondary-->'._NL)); ?>
<div id="content-footer" class="page -contentfooter"></div>
</div><!--content-wrapper-->

<div id="footer-wrapper" class="page -footer">
<div class="wrapper warpper--footer">
<?php echo process_widget(eval_php(cfg('web.footer'),NULL,_NL));?>
</div>
</div><!--footer-wrapper-->

</div><!--page-wrapper-->
<?php
if (debug('query')) {
	echo '<strong>Query time = '.$GLOBALS['mysql']->query_times.' ms.</strong><br />';
	print_o($GLOBALS['mysql']->query_items,'$mysql',1);
	print_o(mydb()->_query_items,'$mydb',1);
}
echo eval_php(cfg('web.complete'),_NL,_NL);
?>
<script type="text/javascript">
<?php
if (cfg('tracking') && _ON_HOST) {
	foreach (cfg('tracking') as $tracker=>$track_id) {
		switch ($tracker) {
			case 'google' : if (is_string($track_id)) echo '// Load Google analytics
(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,"script","//www.google-analytics.com/analytics.js","ga");
ga("create", "'.$track_id.'", "auto");
ga("send", "pageview");
'._NL._NL; else if (is_array($track_id)) echo '// Load Google analytics
(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
	(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,"script","//www.google-analytics.com/analytics.js","ga");
ga("create", "'.$track_id['id'].'", "'.$track_id['site'].'");
ga("send", "pageview");'._NL._NL;break;
		}
	}
}
?>
// Load Google+ Place this tag after the last badgev2 tag.
(function() {
	var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
	po.src = 'https://apis.google.com/js/plusone.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();
</body>
</html>