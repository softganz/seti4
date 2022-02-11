<?php
/**
* View product information
* Created 2019-05-30
* Modify  2019-05-30
*
* @param Object $self
* @param Int $tpid | Object $productInfo
* @return String
*/

$debug = true;

function ibuy_view($self, $tpid = NULL) {
	$productInfo = is_object($tpid) ? $tpid : R::Model('ibuy.product.get',$tpid);
	$tpid = $productInfo->tpid;

	if (!$tpid) return message('error', 'ไม่มีข้อมูลสินค้าที่ระบุ');

	$ret = '';

	if (i()->am == '' && cfg('ibuy.showfor.public') == 'PUBLIC' && $productInfo->info->showfor != 'PUBLIC') {
		return message('notify', 'ขออภัย ท่านไม่สามารถดูรายละเอียดสินค้ารายการนี้');
	}

	//$ret .= '<header class="header -ibuy"><h2>'.$productInfo->title.'</h2></header>';

	$ret .= R::Page('paper.view', $self, $tpid);

	//$ret .= print_o($productInfo, '$productInfo');
	return $ret;
}
?>