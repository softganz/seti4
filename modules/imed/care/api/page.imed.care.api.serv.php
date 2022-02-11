<?php
/**
* iMed :: Care Serv API
* Created 2021-07-30
* Modify  2021-08-02
*
* @return Widget
*
* @usage imed/care/api/serv
*/

$debug = true;

import('package:imed/care/models/model.service.php');

class ImedCareApiServ extends Page {
	var $keyId;
	var $action;
	var $tranId;

	function __construct($keyId = NULL, $action = NULL, $tranId = NULL) {
		$this->keyId = $keyId;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		$serviceInfo = ServiceModel::get($this->keyId);
		$seqId = $serviceInfo->seqId;

		// debugMsg($serviceInfo, '$serviceInfo');
		if (!$serviceInfo->is->access) return message('error', 'Access Denied');


		switch ($this->action) {
			// case 'giver.add':
			// 	$ret .= 'บันทึกผู้ให้บริการเรียบร้อย';
			// 	if (post('giver')) {
			// 		mydb::query('UPDATE %imed_service% SET `doctorid` = :giver WHERE `seq` = :seqId LIMIT 1', ':seqId', $seqId, ':giver', post('giver'));
			// 		// $ret .= mydb()->_query;
			// 	}
			// 	break;

			// case 'visit.save':
			// 	$ret .= 'บันทึกการให้บริการเรียบร้อย';
			// 	mydb::query('UPDATE %imed_service% SET `rx` = :msg WHERE `seq` = :seqId LIMIT 1', ':seqId', $seqId, ':msg', post('msg'));
			// 	// $ret .= mydb()->_query;
			// 	// $ret .= print_o(post(),'post()');
			// 	break;
			
			default:
				$ret = 'ขออภัย!!! ไม่เจอหน้าที่ต้องการอยู่ระบบ';
				break;
		}

		return $ret;
	}
}
?>