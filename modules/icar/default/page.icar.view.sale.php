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


function icar_view_sale($self, $carId) {
	$carInfo = is_object($carId) ? $carId : R::Model('icar.get',$carId, '{initTemplate: true}');
	$carId = $carInfo->tpid;

	$isAdmin = user_access('administer icars');
	$isShopOfficer = $isAdmin || $carInfo->iam;
	$isShopPartner = icar_model::is_partner_of($carInfo);
	$isEdit = $isShopOfficer && empty($carInfo->sold);

	$tables = new Table();
	$tables->addClass('icar-view-sale');
	$tables->attr['url']=url('icar/edit/info');
	$tables->caption='ข้อมูลการซื้อ-ขาย';
	if ($isShopOfficer || $isShopPartner) {
		$tables->rows[]=array('<th colspan="3">ข้อมูลการซื้อ</th>');
		$tables->rows[]=array('วันที่ซื้อ', '<td colspan="2">'.sg_date($carInfo->buydate,'d/m/Y').'</td>');
		$tables->rows[]=array('ผู้ร่วมทุน', '<td colspan="2">'.$carInfo->partnername.'</td>');
		$tables->rows[]=array('ต้นทุนซื้อ', number_format($carInfo->costprice,2),'บาท');
		$tables->rows[]=array('<th colspan="3">ข้อมูลการขาย</th>');
		$tables->rows[]=array('วันที่ขาย', '<td colspan="2">'.($carInfo->saledate?sg_date($carInfo->saledate,'d/m/Y'):'-').'</td>');
		$tables->rows[]=array('ราคาขาย', number_format($carInfo->saleprice,2),'บาท');
		$tables->rows[]=array('จัดไฟแนนส์', number_format($carInfo->financeprice,2),'บาท');
		$tables->rows[]=array('รายรับ', number_format($carInfo->rcvtransfer,2),'บาท');
		$tables->rows[]=array('รายจ่าย', number_format($carInfo->paytransfer,2),'บาท');
		$tables->rows[]=array('เงินดาวน์', number_format($carInfo->saledownprice,2),'บาท');
		$tables->rows[]=array('รับเงินดาวน์', number_format($carInfo->saledownpaid,2),'บาท');
		$tables->rows[]=array('ค้างชำระเงินดาวน์', number_format($carInfo->saledownprice-$carInfo->saledownpaid,2),'บาท');
	}
	$ret .= $tables->build();
	return $ret;
}
?>