<?php
/**
 * Add product item into shoping cart
 *
 * @param Integer $prid
 * @param Integer $_REQUEST[amt] default is 1
 * @return location to product information
 */
function ibuy_cart_add($self,$prid) {
	if (empty($prid)) return 'ไม่มีรายการสินค้า';
	$rs=ibuy_model::get_product($prid);
	$amt=SG\getFirst(post('amt'),1);
	if (i()->uid) {
		$isCart=mydb::select('SELECT `crtid` FROM %ibuy_cart% WHERE `uid`=:uid AND `tpid`=:tpid LIMIT 1',':uid',i()->uid,':tpid',$prid)->crtid;
		$cart['crtid']=$isCart;
		$cart['tpid']=$prid;
		$cart['amt']=$amt;
		$cart['uid']=i()->uid;
		$cart['date_added']=date('Y-m-d H:i:s');
		$cart['session']=$_SESSION['user']->session;
		$stmt='INSERT INTO %ibuy_cart%
						(`crtid`, `session`, `tpid`, `amt`, `uid`, `date_added`)
					VALUES
						(:crtid, :session, :tpid, :amt, :uid, :date_added)
					ON DUPLICATE KEY UPDATE `amt`=`amt`+:amt';
		mydb::query($stmt,$cart);
		$ret.='เพิ่มรายการสินค้า "'.$rs->title.'" จำนวน '.$amt.' ชื้น ลงในตะกร้าเรียบร้อย';
	} else {
		$ret.='กรุณาเข้าสู่ระบบสมาชิกก่อนทำรายการซื้อสินค้า';
	}

	/*
	$ret.=print_o($rs,'$rs');
	$ret.='session_name='.session_name();
	$ret.=print_o($_SESSION,'$_SESSION').print_o($_COOKIE,'$_COOKIE');
	*/

	if (_AJAX) {
		unset($_SESSION['message']);
		$ret = array(
						'msg' => $ret,
						'cartamt' => R::Page('ibuy.cart.items')
					);
		return $ret;
	} else {
		$_SESSION['message']=$ret;
		location($_SERVER['HTTP_REFERER'],NULL,NULL);
	}
	return $ret;
}
?>