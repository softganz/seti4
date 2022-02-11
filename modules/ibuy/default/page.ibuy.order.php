<?php
/**
* iBuy Oeder
* Created 2019-08-17
* Modify  2019-08-17
*
* @param Object $self
* @param Int $orderId
* @return String
*/

$debug = true;

function ibuy_order($self, $orderId = NULL, $action = NULL, $tranId = NULL) {

	if (!is_numeric($orderId)) {$action = $orderId; unset($orderId);} // Action as orderId and clear

	$orderInfo = $orderId ? R::Model('ibuy.order.get', $orderId, '{debug: false}') : NULL;

	$isEdit = user_access('administer ibuys');

	switch ($action) {

		case 'status.update':
			if ($isEdit) {
				$getStatus = post('status');
				$getMessage = post('message');
				$status_text = ibuy_define::status_text($getStatus);
				if ($getMessage) $status_text.=' : '.$getMessage;

				mydb::query('UPDATE %ibuy_order% SET status = :status WHERE oid = :orderId LIMIT 1',':status',$getStatus,':orderId',$orderId);

				// Order cancel : If post status = cancel ( -1 ) , reverse product balance
				// Not complete

				ibuy_model::log('keyword=order','kid='.$orderId,'status='.$getStatus,'detail='.$status_text);
			}
			break;

		case 'transport.save':
			if ($isEdit) {
				$shippingCost = sg_strip_money(post('amount'));
				$stmt = 'UPDATE %ibuy_order% SET `shipping` = :shipping, `total` = `subtotal` - `discount` + `shipping` WHERE `oid` = :orderId LIMIT 1';
				mydb::query($stmt, ':orderId', $orderId, ':shipping', $shippingCost);
				$ret .= mydb()->_query;
			}
			break;


		default:
			if (empty($action) && empty($orderId)) $action = 'home';
			else if (empty($action) && $orderId) $action = 'view';

			if (empty($orderInfo)) $orderInfo = $orderId;
			$argIndex = 3; // Start argument

			//debugMsg('PAGE IBUY/ORDER orderId = '.$orderId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//debugMsg(func_get_args(), '$args');

			$ret = R::Page(
								'ibuy.order.'.$action,
								$self,
								$orderInfo,
								func_get_arg($argIndex),
								func_get_arg($argIndex+1),
								func_get_arg($argIndex+2),
								func_get_arg($argIndex+3),
								func_get_arg($argIndex+4)
							);

			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

			//$ret .= print_o($orderInfo,'$orderInfo');
			break;
	}

	return $ret;
}
?>