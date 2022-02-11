<?php
/**
* Edit Garage Invoice
* Created 2019-10-01
* Modify  2019-10-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_invoice_edit($self, $invoiceId , $action = NULL, $trid = NULL) {
	$shopInfo=R::Model('garage.get.shop');

	if ($invoiceId) {
		$invoiceInfo=R::Model('garage.invoice.get',$shopInfo->shopid,$invoiceId,'{debug:false}');
	}

	if (empty($invoiceInfo->invoiceid)) return '<p class="notify">ไม่มีรายการที่ระบุ</p>';


	switch ($action) {
		case 'cancel':
			//$ret .= 'CANCEL INVOICE';
			if (SG\confirm()) {
				$stmt='UPDATE %garage_invoice% SET `billstatus` = :status WHERE `invoiceid` = :invoiceid LIMIT 1';
				mydb::query($stmt, ':invoiceid',$invoiceId, ':status',_CANCEL);
				//$ret .= mydb()->_query;
			}
			location('garage/invoice/'.$invoiceId);
			break;

		case 'recall':
			//$ret .= 'CANCEL INVOICE';
			if (SG\confirm()) {
				$stmt='UPDATE %garage_invoice% SET `billstatus` = :status WHERE `invoiceid` = :invoiceid LIMIT 1';
				mydb::query($stmt, ':invoiceid',$invoiceId, ':status',1);
				//$ret .= mydb()->_query;
			}
			location('garage/invoice/'.$invoiceId);
			break;

	}
	return $ret;
}

?>