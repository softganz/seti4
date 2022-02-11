<?php
/**
* Org :: Create New Organization
* Created 2021-08-13
* Modify  2021-08-13
*
* @return Widget
*
* @usage org/create
*/

$debug = true;

class OrgCreate extends Page {
	function build() {
		$data = (Object) post('data');

		$isCreatable = user_access('create org content');
		if (!$isCreatable) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);

		$allowDuplicate = post('allowdup') != '';
		$callbackOn = post('callback');

		$ret = '';

		// debugMsg($data, '$data');
		// If select name from list, check if no officer then add user to be officer, if not return error
		if ($data->name) {
			$orgDupName = mydb::select('SELECT o.`orgid`, o.`name`, f.`uid` FROM %db_org% o LEFT JOIN %org_officer% f USING(`orgid`) WHERE o.`name` = :name LIMIT 1', ':name', $data->name);

			//$ret .= print_o($orgDupName,'$orgDupName').mydb()->_query.'<br />';

			if ($allowDuplicate) {
				unset($data->orgid);
			} else if ($data->name == $orgDupName->name && $orgDupName->uid) {
				unset($data->orgid);
				// $ret .= '********* CASE 1';
				return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' =>'องค์กร/หน่วยงานนี้มีเจ้าหน้าที่ในการจัดการข้อมูลอยู่แล้ว!!!!!<br />หากท่านต้องการเข้าร่วมในการจัดการข้อมูลขององค์กร/หน่วยงานนี้<br />กรุณาติดต่อเจ้าหน้าที่ตามรายละเอียดองค์กรเพื่อดำเนินการต่อไป<br />หรือ เปลี่ยนชื่อองค์กร/หน่วยงานที่ต้องการสร้างเป็นชื่ออื่น']);
				// if (post('srcpage')) {
				// 	$ret .= R::Page(post('srcpage'), NULL, $data);
				// }
			} else if ($data->name == $orgDupName->name && !$orgDupName->uid) {
				// Org name already exist but no officer.
				// So add owner into org by org create model
				$data->orgid = $orgDupName->orgid;
				// $ret .= '********* CASE 2';
			} else {
				// If not duplicate name. Unset data orgid and save as new org
				unset($data->orgid);
				// $ret .= '*********  CASE 3';
			}

			// Start create org
			$result = R::Model('org.create',$data,'{debug:false}');
			// debugMsg($result, '$result');
			if ($callbackOn) $ret .= R::On($callbackOn,$data,$result);
		} else {
			return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'ข้อมูลไม่สมบูรณ์']);
		}
		return $ret;
	}
}
?>