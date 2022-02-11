<?php
/**
 * Proceed shoping cart to checkout
 *
 * Add product item into database and clear shoping cart
 * @return location order status page
 */
function ibuy_checkout($self) {
	$self->theme->title = tr('Proceed to checkout');
	$post = (object)post();
	$uid = i()->uid;
	$error = array();

	$simulate = debug('simulate');

	$cartInfo = R::Model('ibuy.cart.get');

	if (empty($cartInfo->items)) $error[]='ไม่มีรายการสินค้าในตะกร้า';

	if ($error) return message('error',$error);

	// Show Step

	$stepUi = new Ui(NULL, 'ui-step');
	$stepNo = SG\getFirst(post('st'),1);

	$stepUi->add('<a class="step -s1"><span class="step-num">1</span><span>CHECKOUT</span></a>', $stepNo >= 1 ? '{class: "-done"}' : '');

	$stepUi->add('<a class="step -s1"><span class="step-num">2</span><span>การออกใบเสร็จ</span></a>', $stepNo >= 2 ? '{class: "-done"}' : '');

	$stepUi->add('<a class="step -s1"><span class="step-num">3</span><span>การจัดส่งสินค้า</span></a>', $stepNo >= 3 ? '{class: "-done"}' : '');

	$stepUi->add('<a class="step -s1"><span class="step-num">4</span><span>วิธีการส่งสินค้า</span></a>', $stepNo >= 4 ? '{class: "-done"}' : '');

	$stepUi->add('<a class="step -s1"><span class="step-num">5</span><span>การชำระเงิน</span></a>', $stepNo >= 5 ? '{class: "-done"}' : '');

	$stepUi->add('<a class="step -s1"><span class="step-num">6</span><span>ยืนยันการสั่งซื้อ</span></a>', $stepNo >= 6 ? '{class: "-done"}' : '');


	$ret .= '<nav class="nav -step"><hr />'.$stepUi->build().'</nav>';

	$savedCheckout = mydb::select('SELECT `bigid`, `flddata` FROM %bigdata% WHERE `keyname` = "ibuy" AND `keyid` = :keyid AND `fldname` = "checkout" LIMIT 1', ':keyid', $cartInfo->uid);

	$checkoutInfo = json_decode($savedCheckout->flddata);
	$checkoutInfo->uid = $cartInfo->uid;

	if (post('checkout')) {
		if (empty($savedCheckout->bigid)) $savedCheckout->bigid = NULL;

		$checkoutInfo = object_merge_recursive($checkoutInfo, (Object) post('checkout'));
		$savedCheckout->flddata = sg_json_encode($checkoutInfo);
		$savedCheckout->keyid = i()->uid;
		$savedCheckout->created = date('U');
		$savedCheckout->ucreated = i()->uid;
		$stmt = 'INSERT INTO %bigdata% (`bigid`, `keyname`, `keyid`, `fldname`, `fldtype`, `flddata`, `created`, `ucreated`) VALUES (:bigid, "ibuy", :keyid, "checkout", "json", :flddata, :created, :ucreated) ON DUPLICATE KEY UPDATE `flddata` = :flddata';
		mydb::query($stmt, $savedCheckout);

		//$ret .= mydb()->_query;

		//$ret .= print_o($savedCheckout,'$savedCheckout').'<br />'.$checkoutValue.'<br />';
		//$stmt = 
	}

	$ret .= R::View('ibuy.checkout.step'.$stepNo, $cartInfo, $checkoutInfo);

	if (post('proceed')) {
		$result = R::Model('ibuy.checkout.proceed', $cartInfo, $checkoutInfo);
		if ($result->error) {
			$ret .= message('error',$result->error);
		} else {
			location('ibuy/status');
		}
		//$ret .= print_o($result, '$result');
	}

	//$ret .= print_o($checkoutInfo, '$checkoutInfo');
	//$ret .= print_o($cartInfo, '$cartInfo');

	//$ret .= print_o(post(),'post()');

	return $ret;
}

?>