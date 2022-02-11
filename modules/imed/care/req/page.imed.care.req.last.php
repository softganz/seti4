<?php
/**
* iMed :: Care Taker Menu
* Created 2021-07-22
* Modify  2021-07-22
*
* @return Widget
*
* @usage imed/care/req/last
*/

$debug = true;

import('package:imed/care/models/model.request.php');

class ImedCareReqLast {
	function build() {
		location('imed/care/req/'.RequestModel::last());
	}
}
?>