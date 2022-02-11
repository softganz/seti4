<?php
function module_garage_install() {
	$ret='<h3>Garage installation</h3>';

	$ret.='<p><strong>Installation completed.</strong></p>';
	$ret.='<ul><li>'.implode('</li><li>',$queryResult).'</li></ul>';

	return $ret;
}
?>