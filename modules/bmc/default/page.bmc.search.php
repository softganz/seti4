<?php
/**
* BMC :: Search
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

function bmc_search($self) {
	$ret = '';

	if (!R()->appAgent) $ret .= '<header class="header"><h3>ค้นหา</h3></header>';

	
	//$ret .= print_o($bmcInfo, '$bmcInfo');

	return $ret;
}
?>