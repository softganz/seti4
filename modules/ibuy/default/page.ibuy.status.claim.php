<?php
/**
 * Show claim list
 *
 * @param Integer $cid
 * @return String
 */
function ibuy_status_claim($self,$cid = NULL) {
	$self->theme->title='Claim status process';
	$ret.='<h3>Claim status</h3><p>ขออภัย เมนูนี้อยู่ในระหว่างการพัฒนาระบบ</p>';
	return $ret;
}
?>