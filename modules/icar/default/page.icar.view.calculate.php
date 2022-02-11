<?php
/**
* Show car information
*
* @param Object $self
* @param Record Set $carInfo
* @param Boolean $isEdit
* @return String
*/

$debug = true;

function icar_view_calculate($self, $carId) {
	$carInfo = is_object($carId) ? $carId : R::Model('icar.get',$carId, '{initTemplate: true}');
	$carId = $carInfo->tpid;

	$isAdmin = user_access('administer icars');
	$isShopOfficer = $isAdmin || $carInfo->iam;
	$isShopPartner = icar_model::is_partner_of($carInfo);
	$isEdit = $isShopOfficer && empty($carInfo->sold);

	if ($isAdmin || $isShopOfficer || $isShopPartner) {

		$tables = new Table();
		$tables->addClass('icar-cost-calculate');
		$tables->caption='รายการคำนวณ';
		$saleprice=$carInfo->saleprice;
		$tables->rows[]=array('ราคาขาย',number_format($saleprice,2));
		$tables->rows[]=array('(ต้นทุน)','-'.number_format($carInfo->costprice,2),'-');
		$firstmargin=$saleprice-$carInfo->costprice;
		$tables->rows[]=array('กำไรขั้นต้น',number_format($firstmargin,2),'=');
		$tables->rows[]=array('รายรับ',number_format($carInfo->rcvtransfer,2),'+');
		$tables->rows[]=array('รายจ่าย',number_format($carInfo->paytransfer,2),'-');
		$tables->rows[]=array('(ดอกเบี้ย)','-'.number_format($carInfo->interest,2),'-');
		$margin=$firstmargin+($carInfo->rcvtransfer-$carInfo->paytransfer)-$carInfo->interest;
		$tables->rows[]=array('กำไรสุทธิ',number_format($margin,2),'=');
		if ($carInfo->partner) {
			$share=$margin/2;
			$tables->rows[]=array('ส่วนแบ่ง',number_format($share,2),'<a href="javascript:void(0)" title="กำไรสุทธิ/2='.number_format($margin,2).'/2='.number_format($share,2).'">/2</a>');
			$shopmargin=$share+$carInfo->interest-$carInfo->notcost;
			if ($carInfo->notcost) $tables->rows[]=array('ไม่คำนวณต้นทุน',number_format($carInfo->notcost,2));
			$tables->rows[]=array('ส่วนแบ่งร้าน',number_format($shopmargin,2),'<a href="javascript:void(0)" title="ส่วนแบ่ง+ดอกเบี้ย-ไม่คำนวณต้นทุน='.number_format($share,2).'+'.number_format($carInfo->interest,2).'-'.number_format($carInfo->notcost,2).'='.number_format($shopmargin,2).'">?</a>');
			$tables->rows[]=array('ส่วนแบ่งกำไรผู้ร่วมทุน',number_format($share,2),'<a href="javascript:void(0)" title="ส่วนแบ่ง='.number_format($share,2).'">?</a>');
		}
		$ret .= $tables->build();

		$ret .= R::Page('icar.view.sale',NULL,$carInfo);
	}
	return $ret;
}
?>