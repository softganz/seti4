<?php
/**
* Under Construction
* Created 2020-04-28
* Modify  2020-04-28
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function underconstruction($self, $theme = 1) {
	$ret = '';

	$ret .= '<style type="text/css">
body {}
.underconstruction .logo {width:90%; max-width:200px; display: block; margin:20px auto;}
.underconstruction .welcome {font-size: 2em; text-align: center; background: #209BDE; color:#fff;}
@media (min-width:30em){    /* 480/16 */
	.logo {}
	.welcome {}
}
</style>';

	$themeCfg = array(
		1 => array(
			'img' => '//img.softganz.com/img/linux-logo.png',
			'text' => 'อดใจรออีกไม่นาน',
		),
		2 => array(
			'img' => '//img.softganz.com/img/linux-logo.png',
			'text' => 'รอดำเนินการ',
		),
	);

	$currentTheme = $themeCfg[$theme];

	$ret .= '<div class="underconstruction">';
	$ret .= '<img class="logo" src="'.$currentTheme['img'].'" height="200" />';
	$ret .= '<p class="welcome">'.$currentTheme['text'].'</p>';
	$ret .= '</div>';

	return $ret;
}
?>