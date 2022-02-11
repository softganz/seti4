<?php
/**
* Cost Controller
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function icar_cost($self, $carId, $action = NULL, $tranId = NULL) {
	$carInfo = icar_model::get_by_id($carId);
	if ($carInfo->_empty) return message('error','ไม่มีข้อมูล');


	//$ret .= print_o($carInfo, '$carInfo');

	$isAdmin = user_access('administer icars');
	$isShopOfficer = $carInfo->iam;
	$isShopPartner = icar_model::is_partner_of($carInfo);
	$isEdit = ($isAdmin || $isShopOfficer);

	if (!$isEdit) return 'Access Denied';

	$ret = '';

	switch ($action) {

		case 'delcost' :
			if ($tranId && SG\confirm()) {
				$costInfo = mydb::select('SELECT c.*, tg.`name` FROM %icarcost% c LEFT JOIN %tag% tg ON tg.`tid`=c.`costcode` WHERE c.`costid` = :costid LIMIT 1',':costid',$tranId);

				mydb::query('DELETE FROM %icarcost% WHERE `costid` = :costid LIMIT 1',':costid',$tranId);
				$ret .= 'ลบรายการเรียบร้อย';
				//$ret .= mydb()->_query;

				// Update cost
				R::Model('icar.get', $carId,'{updatecost: true}');

				R::Model('icar.log', $carId, 'Car Cost Delete', 'Car Cost Delete : code = '.$costInfo->costcode.' , interest = '.$costInfo->interest.' , amt = '.$costInfo->amt.' , name = '.$costInfo->name);
			}
			break;
	}

	return $ret;
}
?>