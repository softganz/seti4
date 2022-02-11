<?php
/**
* Proceed Checkout
* Created 2019-06-09
* Modify  2019-06-09
*
* @param 
* @return String
*/

$debug = true;

function r_ibuy_checkout_proceed($cartInfo, $checkoutInfo, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$result->title = 'PROCEED CHECKOUT';
	$result->uid = $cartInfo->uid;
	$result->error = NULL;

	if ($cartInfo->shipping && !$checkoutInfo->shipping) $result->error = message('error','กรุณายืนยันว่าท่านจะต้องชำระค่าขนส่งต้นทางด้วยตนเอง');

	if ($result->error) return $result;

	$order->uid = $result->uid;
	$order->orderdate = date('U');
	$order->subtotal = $cartInfo->subtotal;
	$order->shipping = $cartInfo->shipping;
	$order->remark = strip_tags($checkoutInfo->remark);

	// Calculate next order no
	$stmt = 'SELECT `value` `lastNo` FROM %variable% WHERE `name`="ibuy.lastorderno" LIMIT 1';
	$lastNo = mydb::select($stmt)->lastNo;
	$digit = cfg('ibuy.orderdigit');
	$orderSep = cfg('ibuy.ordersep');

	if ($orderSep) {
		list($orderYear,$orderMonth,$orderNo) = explode($orderSep,$lastNo);
	} else {
		$orderYear = substr($lastNo,0,2);
		$orderMonth = substr($lastNo,2,2);
		$orderNo = substr($lastNo,4);
	}
	$currentYear = substr(date('Y')+543,2);
	if ($orderYear != $currentYear) {
		$orderYear = substr(date('Y')+543,2);
		$orderNo = 0;
	}
	if ($orderMonth != date('m')) {
		$orderMonth = date('m');
		$orderNo = 0;
	}
	//$ret.='lastNo='.$lastNo.'<br />';

	$nextNo=$orderNo+1;

	// Increment digit if next order no greater than current digit
	if ($nextNo >= pow(10,$digit)) {
		$digit++;
		cfg_db('ibuy.orderdigit',(string) $digit);
	}

	// Generate next order no
	if ($orderSep) {
		$nextOrderNo = $orderYear.$orderSep.$orderMonth.$orderSep.sprintf('%0'.$digit.'d',$nextNo);
	} else {
		$nextOrderNo = $orderYear.$orderMonth.sprintf('%0'.$digit.'d',$nextNo);
	}

	$result->orderno = $nextOrderNo;
	//$ret.='nextOrderNo='.$nextOrderNo.'<br />';

	// Update last order no
	mydb::query('INSERT INTO %variable% (`name`, `value`) VALUES ("ibuy.lastorderno", :value) ON DUPLICATE KEY UPDATE `value` = :value', ':value', $nextOrderNo);
	$result->query[] = mydb()->_query;



	$order->orderno = $nextOrderNo;


	// Calculate discount
	$order->discount = 0;
	if ($cartInfo->discount_summary > 0 && $checkoutInfo->usediscount == 'yes') {
	//$order->discount=$cartInfo->discount_summary<$order->subtotal?$cartInfo->discount_summary:$order->subtotal;
		$order->discount = $cartInfo->discount_summary < $cartInfo->discount_yes ? $cartInfo->discount_summary : $cartInfo->discount_yes;
		if (!$simulate) {
			mydb::query('UPDATE %ibuy_customer% SET `discount` = `discount` - :discount WHERE `uid` = :uid LIMIT 1', ':discount', $order->discount, ':uid', $order->uid);
			$result->query[] = mydb()->_query;
		}
	}


	$order->total = $order->balance = $cartInfo->total - $order->discount + $cartInfo->shipping;
	$order->leveldiscount = $cartInfo->leveldiscount - $order->discount;
	$order->marketvalue = $cartInfo->marketvalue - $order->discount;
	$order->franchisorvalue = $cartInfo->franchisorvalue;

	$order->shipcode = $checkoutInfo->shipcode;
	$order->shipto = strip_tags($checkoutInfo->shipto);
	if (empty($order->shipto) && $order->shipcode == 14) $order->shipto = 'EMS ด่วนพิเศษ';
	else if (empty($order->shipto) && $order->shipcode == 13) $order->shipto = 'ไปรษณีย์ลงทะเบียน';
				
	// Add order information to order
	$stmt = 'INSERT INTO %ibuy_order%
						(`uid`, `orderno`, `orderdate`, `subtotal`, `discount`, `shipping`, `total`, `leveldiscount`, `marketvalue`, `franchisorvalue`, `balance`, `shipcode`, `shipto`, `remark`)
					VALUES
						(:uid, :orderno, :orderdate, :subtotal, :discount, :shipping, :total, :leveldiscount, :marketvalue, :franchisorvalue, :balance, :shipcode, :shipto, :remark)';
	
	if ($simulate) {
		$ret .= '<p>'.$stmt.'</p>';
		$ret .= print_o($order,'$order');
	} else {
		mydb::query($stmt,$order);
		$result->query[] = mydb()->_query;
	}
	//$ret.=mydb()->_query;
	if (mydb()->_error) {
		$result->error = 'เกิดความผิดพลาดในกระบวนการบันทึกข้อมูลการซื้อสินค้า กรุณาติดต่อผู้ดูแลระบบ';
		return $result;
	}
	

	// Add order transaction to ordertr
	$order->oid = $ordertr->oid = mydb()->insert_id;


	foreach ($cartInfo->items as $rs) {
		$ordertr->tpid = is_numeric($rs->tpid)?$rs->tpid:0;
		$ordertr->amt = $rs->amt;
		$ordertr->price = $rs->price;
		$ordertr->subtotal = $rs->subtotal;
		$ordertr->discount = $rs->discount;
		$ordertr->total = $rs->total;
		$ordertr->leveldiscount = $rs->leveldiscount;
		$ordertr->marketvalue = $rs->marketvalue;

		$stmt = 'INSERT INTO %ibuy_ordertr%
				(`oid` , `tpid` , `amt` , `price` , `subtotal` , `discount` , `total` , `leveldiscount` , `marketvalue`)
			VALUES
				(:oid , :tpid , :amt , :price , :subtotal , :discount , :total , :leveldiscount , :marketvalue)';

		if ($simulate) {
			$ret .= '<p>'.$stmt.'</p>';
		} else {
			mydb::query($stmt,$ordertr);
			$result->query[] = mydb()->_query;

			if (cfg('ibuy.stock.use')) {
				mydb::query('UPDATE %ibuy_product% SET `balance`=`balance`-:amt WHERE `tpid`=:tpid LIMIT 1',':tpid',$rs->tpid,':amt',$rs->amt);
				$result->query[] = mydb()->_query;
			}
			if (i()->ok && $ordertr->tpid) {
				R::Model('reaction.add',$ordertr->tpid,'IBUY.BUY');
			}
		}
	}
	
	if (!$simulate) {
		// Add discount to user discount 
		if ($cartInfo->resalerdiscount) {
			mydb::query('UPDATE %ibuy_customer% SET `discount` = `discount` + :discount WHERE `uid` = :uid LIMIT 1',':discount',$cartInfo->resalerdiscount,':uid',$order->uid);
			$result->query[] = mydb()->_query;
		}

		$stmt = 'INSERT INTO %bigdata% (`keyname`, `keyid`, `fldname`, `fldtype`, `flddata`, `created`, `ucreated`) VALUES ("ibuy", :keyid, "order.checkout", "json", :flddata, :created, :ucreated)';
		mydb::query($stmt, ':keyid', $order->oid, ':flddata', sg_json_encode($checkoutInfo), ':created', date('U'), ':ucreated', $result->uid);
		$result->query[] = mydb()->_query;




		// Update default bill address to customer information
		$setShopName = mydb::select('SELECT `custname` FROM %ibuy_customer% WHERE `uid` = :uid LIMIT 1', ':uid', $order->uid)->custname;
		if ($checkoutInfo->useoldaddr == 'defalut' && !$setShopName) {
			$franchiseInfo->uid = $order->uid;
			$franchiseInfo->custattn = $checkoutInfo->bill->firstname.' '.$checkoutInfo->bill->lastname;
			$franchiseInfo->custname = $checkoutInfo->bill->company;
			$franchiseInfo->custaddress = $checkoutInfo->bill->address1.' '
				. $checkoutInfo->bill->address2.' '
				. 'อ.'.$checkoutInfo->bill->city.' '
				. 'จ.'.$checkoutInfo->bill->province;
			$franchiseInfo->custzip = $checkoutInfo->bill->zipcode;
			$franchiseInfo->custphone = $checkoutInfo->bill->phone;
			$franchiseInfo->custlicense = $checkoutInfo->bill->license;

			$stmt = 'UPDATE %ibuy_customer% SET
				`custname` = :custname
				, `custattn` = :custattn
				, `custaddress` = :custaddress
				, `custzip` = :custzip
				, `custphone` = :custphone
				, `custlicense` = :custlicense
				WHERE `uid` = :uid
				LIMIT 1
				';
			mydb::query($stmt, $franchiseInfo);
			$result->query[] = mydb()->_query;
		}

		// Add to log file
		ibuy_model::log('keyword=order','kid='.$order->oid,'status=0','created='.$order->orderdate,'detail=บันทึกการสั่งซื้อสินค้า','amt='.$order->total,'process=1');

		$_SESSION['message'] = message('status','บันทึกรายการสั่งซื้อสินค้าเรียบร้อย');

		// Send mail to buyer and admin

		//$ret.=print_o($_POST,'$_POST').print_o($order,'$order');
		ibuy_model::empty_cart($result->uid);
	}

	return $result;
}
?>