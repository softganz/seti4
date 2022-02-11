<?php
function docs_samples($self=NULL) {
	$ret .= '<h3>Samples</h3>';
	$lookfolder = array('busi','gov','hsmi','happy','seti','sator4u','photon');
	$dir    = '/Users/httpdocs/';

	$dir = dirname(__FILE__).'/../page/sample';

	//$ret .= $dir;

	$folderList = array_slice(scandir($dir), 2);
	$ui = new Ui();
	foreach ($folderList as $filename) {
		$url = substr($filename, 0, strrpos($filename,'.'));
		$ui->add('<a href="'.url('docs/sample/'.$url).'">'.$url.'</a>');
	}
	$ret.=$ui->build();

	return $ret;
}
?>