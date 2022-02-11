<?php
/**
* Green : Report
* Created 2020-11-25
* Modify  2020-11-25
*
* @param Object $self
* @return String
*
* @usage green/report
*/

$debug = true;

function green_report($self) {
	$ret = '';


	$mainUi = new Ui();
	$mainUi->addConfig('nav', '{class: "nav -app-menu"}');

	$mainUi->header('<h3>แผนที่</h3>');
	$mainUi->add('<a class="sg-action" href="'.url('green/report/land').'" data-webview="แผนที่แปลงผลิต"><i class="icon -material">room</i><span>แผนที่<br />แปลงผลิต</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('green/report/plant').'" data-webview="ผลผลิต"><i class="icon -material">grass</i><span>ผลผลิต</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('green/report/tree').'" data-webview="แผนที่ธนาคารต้นไม้"><i class="icon -material">room</i><span>แผนที่<br />ธนาคารต้นไม้</span></a>');

	$ret .= $mainUi->build();

	return $ret;
}
?>