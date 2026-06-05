<?php
function docs_localhost($self=NULL) {
	$lookfolder=array('busi','gov','hsmi','happy','seti','sator4u','photon');
	$dir    = '/Users/httpdocs/';

	$ret .= '<div class="-sg-flex">';

	$ret .= '<div>';
	$ret .= '<h1>Local Host</h1>';
	foreach ($lookfolder as $item) {
		$ret .= '<h2>'.$item.'</h2>';
		$folderList = scandir($dir.$item);
		$ui = new Ui();
		foreach ($folderList as $url) {
			if (in_array($url, ['.','..'])) continue;
			$ui->add('<a href="/'.$item.'/'.$url.'/" target="_blank">'.$url.'</a>');
		}
		$ret .= $ui->build();
	}

	$ret .= '</div>';

	$ui = new Ui();
	$ui->add('<a href="http://handynas.local:5000/" target="_blank">HandyNas.Local</a>');

	$ret .= '<div>'
		. '<h1>Local Tools</h1>'
		. $ui->build().'</div>';

	$ret .= '</div>';
	return $ret;
}
?>