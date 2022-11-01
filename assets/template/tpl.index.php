<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "https://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="https://www.w3.org/1999/xhtml" dir="ltr" lang="en-EN">
<head profile="https://gmpg.org/xfn/11">
<?php if (cfg('web.init')) {ob_start();eval ('?>'.cfg('web.init'));echo ob_get_clean()._NL;}?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo cfg('client.characterset');?>" />
<meta http-equiv="Content-Language" content="th" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title><?php echo ($GLOBALS['title']?$GLOBALS['title']:'') .($GLOBALS['title'] && cfg('web.title') ? ' | ':'').cfg('web.title');?></title>
<meta name="generator" content="www.softganz.com" />
<meta name="formatter" content="Little Bear by SoftGanz Group" />
<meta name="author" content="<?php echo cfg('web.title');?>" />
<meta name="viewport" content="viewport-fit=cover, width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.5, user-scalable=yes"/>
<meta name="format-detection" content="telephone=no"/>
<meta name="msapplication-tap-highlight" content="no"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black"/>

<?php if (cfg('gmapkey')) echo '<script>var googleMapKeyApi = "'.cfg('gmapkey').'"</script>';?>

<?php if (cfg('head.include.first')) echo implode(_NL,head());?>

<?php if (cfg('favicon')) echo cfg('favicon');?>
<link rel="stylesheet" type="text/css" href="<?php echo cfg('theme.stylesheet').cfg('theme.stylesheet.para');?>" />
<?php
if (file_exists(cfg('theme.absfolder').'/style.inc.css')) echo '<link rel="stylesheet" type="text/css" href="'.cfg('theme').'style.inc.css" />'._NL;
if (is_home() && file_exists(cfg('theme.absfolder').'/home.css')) echo '<link rel="stylesheet" type="text/css" href="'.cfg('theme').'home.css" />'._NL;
if (cfg('theme.'.cfg('theme.name').'.css')) echo '<link rel="stylesheet" type="text/css" href="'.cfg('theme').'theme.css" />'._NL;
if (isset($_REQUEST['bw']) && $_REQUEST['bw']=='0') {
	;//
} else if (cfg('theme.backandwhite')) {
	echo '<link rel="stylesheet" type="text/css" href="https://softganz.com/themes/bw/bw.css" />'._NL;
}
?>
<script type="text/javascript">var isRunOnHost=<?php echo cfg('server')?'true':'false';?></script>
<script type="text/javascript">var rootUrl = url = "<?php echo _URL;?>"</script>

<?php $jsLocation = cfg('clean_url')?'/js/':cfg('library');?>
<script type="text/javascript" src="<?php echo $jsLocation;?>jquery<?php echo cfg('jquery.version')?'-'.cfg('jquery.version'):'';?>.js"></script>

<script type="text/javascript" src="<?php echo $jsLocation;?>jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="<?php echo $jsLocation;?>jquery.colorbox.js"></script>
<script type="text/javascript" src="<?php echo $jsLocation;?>jquery.jeditable.js"></script>
<script type="text/javascript" src="<?php echo $jsLocation;?>jquery.form.js"></script>


<script type="text/javascript" src="<?php echo $jsLocation;?>jquery.confirm.js"></script>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css"> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script> -->

<!--
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>
-->

<script type="text/javascript" src="<?php echo $jsLocation.'library'.(cfg('library.version') ? '-'.cfg('library.version') : '').($_SESSION['devMode'] ? '.js' : '.min.js').cfg('theme.stylesheet.para');?>"></script>
<script type="text/javascript" src="<?php echo $jsLocation.($_SESSION['devMode'] ? 'sgui.js':'sgui.min.js').cfg('theme.stylesheet.para');?>"></script>


<?php if (!cfg('head.include.first')) echo implode(_NL,head());?>
<?php
if (cfg('tracking') && _ON_HOST) {
	foreach (cfg('tracking') as $tracker=>$track_id) {
		switch ($tracker) {
			case 'google' : echo '<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id='.$track_id.'"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag("js", new Date());
  gtag("config", "'.$track_id.'");
</script>
'._NL._NL;
			break;
		}
	}
}?>
</head>

<body<?php echo
	(cfg('page_id') ? ' id="'.cfg('page_id').'"' : '')
	. ' class="module module-'.cfg('page_id')
	. (q(1) ? (is_numeric(q(1)) ? ' -'.q(0).'-'.q(1) : ' -'.q(1)) : '')
	//. (preg_match('/^softganz\/app/i', $_SERVER['HTTP_USER_AGENT']) ? ' -app' : '')
	. (cfg('page_class') ? ' '.cfg('page_class') : '')
	. (q(2) && !is_numeric(q(2)) ? ' --'.str_replace('.','-',q(2)) : '')
	. (q(3) && !is_numeric(q(3)) ? ' --'.str_replace('.','-',q(3)) : '')
	. ' -'.str_replace('.','-',str_replace('www.','',cfg('domain.short')))
	. '"'
	. (cfg('body_attr') ? ' '.cfg('body_attr'):'')
	;?>>
<?php if (R::option('fullpage') || cfg('web.fullpage')) {echo $GLOBALS['request_result'].'</body>'._NL.'</html>';return;}?>
<?php if (cfg('web.navigator')) echo is_string(cfg('web.navigator')) ? cfg('web.navigator') : '<ul><li><a href="'.url().'">Home</a></li><li><a href="'.url('help').'">Help</a></li><li><a href="'.url('my').'">Login</a></li><li><a href="'.url('user/register').'">Register</a></li></ul>';?>
<div id="page-wrapper" class="page -page">
<?php if (cfg('core.message')) echo '<div id="core-message">'.cfg('core.message').'</div>'._NL;?>
<?php
if (cfg('web.header')===false) {
	;
} else {
	$navigator = SG\getFirst(cfg('navigator.'.q(0).'.'.q(1)),cfg('navigator.'.q(0)),cfg('navigator'));
	echo '<div id="header-wrapper" class="page -header">
	<div class="wrapper">
	<header>
	<h1><a href="'.url(cfg('web.url')).'" title="'.htmlspecialchars(cfg('web.title')).'"><span>'.cfg('web.title').'</span></a></h1>'
	. (cfg('web.slogan') ? '<p>'.cfg('web.slogan').'</p>'._NL : '')
	. '</header>'
	. process_widget(eval_php($navigator,_NL,_NL))
	. '</div><!--wrapper-->'._NL
	. '</div><!--header-wrapper-->'._NL;
}?>

<div id="content-wrapper" class="page -content">
<?php echo user_access('access debugging program') ? debugMsg() : '';?>
<?php if (cfg('web.primary')) echo '<div id="primary" class="page -primary">'._NL;?>
<?php if (cfg('web.message')) echo '<div id="web-message">'.cfg('web.message').'</div>'._NL;?>
<?php echo $GLOBALS['request_result'];?>
<?php if (cfg('web.primary')) echo '</div><!--primary-->'._NL;?>

<?php if (is_string(cfg('web.secondary'))) echo process_widget(eval_php(cfg('web.secondary'),_NL.'<div id="secondary" class="page -secondary">'._NL,_NL.'</div><!--secondary-->'._NL)); ?>
<div id="content-footer" class="page -content-footer"></div>
</div><!--content-wrapper-->

<div id="footer-wrapper" class="page -footer">
<div class="wrapper warpper--footer">
<?php echo process_widget(eval_php(cfg('web.footer'),NULL,_NL));?>
</div>
</div><!--footer-wrapper-->

</div><!--page-wrapper-->
<?php
if (debug('query')) {
	echo '<strong>Query time = '.$GLOBALS['R']->myDb->_query_times.' ms.</strong><br />';
	print_o($GLOBALS['R']->mysql->query_items,'$mysql',1);
	print_o(mydb()->_query_items,'$mydb',1);
}
echo eval_php(cfg('web.complete'),_NL,_NL);
?>
<div id="fb-root"></div>
<?php
if (cfg('firebase')) {
	echo '<script src="https://www.gstatic.com/firebasejs/4.7.0/firebase.js"></script>'._NL;
	echo '<script>
  // Initialize Firebase
  firebaseConfig = '.json_encode(cfg('firebase')).';
  firebase.initializeApp(firebaseConfig);
	</script>'._NL;
}
?>
<script type="text/javascript">
<?php
if (cfg('social.facebook')) echo '// Load the Facebook SDK asynchronously
(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "https://connect.facebook.net/en_US/sdk.js#xfbml=1&appId=137245076319573&version=v3.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, "script", "facebook-jssdk"));'._NL;
?>
</script>
</body>
</html>