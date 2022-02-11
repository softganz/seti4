<?php
function garage_billing_edit($self, $billid , $action = NULL, $trid = NULL) {
	$shopInfo=R::Model('garage.get.shop');

	if ($billid) {
		$billingInfo=R::Model('garage.billing.get',$shopInfo->shopid,$billid,'{debug:false}');
	}

	if (empty($billid)) return '<p class="notify">ไม่มีรายการที่ระบุ</p>';


	switch ($action) {
		case 'cancel':
			//$ret .= 'CANCEL BILLING';
			if (SG\confirm()) {
				$stmt='UPDATE %garage_billing% SET `billstatus` = :status WHERE `billid` = :billid LIMIT 1';
				mydb::query($stmt, ':billid',$billid, ':status',_CANCEL);
				//$ret .= mydb()->_query;
			}
			location('garage/billing/view/'.$billid);
			break;

		case 'recall':
			//$ret .= 'CANCEL BILLING';
			if (SG\confirm()) {
				$stmt='UPDATE %garage_billing% SET `billstatus` = :status WHERE `billid` = :billid LIMIT 1';
				mydb::query($stmt, ':billid',$billid, ':status',1);
				//$ret .= mydb()->_query;
			}
			location('garage/billing/view/'.$billid);
			break;

		case 'delqt':
			if ($trid && SG\confirm()) {
				$stmt='UPDATE %garage_qt% SET `billid`=NULL WHERE `qtid`=:qtid AND `billid`=:billid LIMIT 1';
				mydb::query($stmt,':qtid',$trid, ':billid',$billid);
			}
			break;

		case 'addqt':
			if ($trid) {
				$stmt='UPDATE %garage_qt% SET `billid`=:billid WHERE `qtid`=:qtid LIMIT 1';
				mydb::query($stmt,':billid',$billid, ':qtid',$trid);
			}
			break;
	}
	return $ret;
}

?>