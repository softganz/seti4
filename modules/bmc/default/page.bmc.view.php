<?php
/**
* BMC :: View
* Created 2020-12-07
* Modify  2020-12-07
*
* @param Object $self
* @param Object $bmcInfo
* @return String
*
* @usage bmv/{id}
*/

$debug = true;

function bmc_view($self, $bmcInfo) {
	$ret = '';

	if (!R()->appAgent) $ret .= '<header class="header"><h3>'.$bmcInfo->title.'</h3></header>';

	$ret .= '<p class="notify" style="padding: 32px; margin-bottom: 32px;">กำลังอยู่ระหว่างดำเนินการ<br />จะสามารถใช้งานได้ในเร็วๆ นี้</p>';
	$ret .= '<img src="//img.softganz.com/img/bmc-template.png" width="100%" />';
	
	//$ret .= print_o($bmcInfo, '$bmcInfo');

	head('<script type="text/javascript">
		function onWebViewComplete() {
			console.log("CALL onWebViewComplete FROM WEBVIEW")
			var options = {title: "'.$bmcInfo->title.'"}
			return options
		}
	</script>'
	);

	return $ret;
}
?>