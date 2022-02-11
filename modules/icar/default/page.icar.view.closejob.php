<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function icar_view_closejob($self, $carId) {
	$carInfo = is_object($carId) ? $carId : R::Model('icar.get',$carId, '{initTemplate: true}');
	$carId = $carInfo->tpid;

	$isAdmin = user_access('administer icars');
	$isShopOfficer = $isAdmin || $carInfo->iam;
	$isEdit = ($isAdmin || in_array($carInfo->iam, array('OWNER','MANAGER','OFFICER')));

	if (!$isEdit) return message('error','access denied');

	if ($_REQUEST['lock']=='yes') {
		mydb::query('UPDATE %icar% SET `sold`="Yes" WHERE `tpid`=:tpid LIMIT 1',':tpid',$carInfo->tpid);
	} else if ($_REQUEST['lock']=='no') {
		mydb::query('UPDATE %icar% SET `sold`=NULL WHERE `tpid`=:tpid LIMIT 1',':tpid',$carInfo->tpid);
	}
	//$ret.=mydb()->_query;
	//return $ret;
	location('icar/'.$carId);
}
?>