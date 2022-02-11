<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_customer_serial($self, $customerInfo, $serialId = NULL) {
	if (!($customerId = $customerInfo->custid)) return message('error', 'PROCESS ERROR');

	if ($serialId) return R::Page('ibuy.customer.serial.view', $self, $customerInfo, $serialId);


	$ret .= '<div id="ibuy-customer-serial">';

	$headerNav = new Ui();
	$headerNav->addConfig('nav', '{class: "nav"}');
	$headerNav->add('<a class="sg-action btn -link" href="'.url('ibuy/customer/'.$customerId.'/serial.form').'" data-rel="box" title="เพิ่ม Serial No. สินค้า"><i class="icon -material">add_circle</i><span>เพิ่มทะเบียนสินค้า</span></a>');
	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>ทะเบียนหมายเลขสินค้า</h3>'.$headerNav->build().'</header>';

	$stmt = 'SELECT  IFNULL(s.`stkdesc`, t.`title`) `productName`, s.*
		FROM %ibuy_serial% s
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE `custid` = :custid
		ORDER BY `issnid` DESC';
	$dbs = mydb::select($stmt, ':custid', $customerId);

	$ticketCard = new Ui('div', 'ui-card ibuy-customer-ticket');

	foreach ($dbs->items as $rs) {
		$ui = new Ui();
		//$ui->add('<a class="sg-action" href="'.url('ibuy/customer/'.$customerId.'/info/ticket.remove/'.$rs->tickid).'" data-rel="notify" data-done="remove:parent tr" data-title="ลบบันทึก" data-confirm="ต้องการลบบันทึกนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>');
		$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
		$ticketCard->add(
			'<div class="header"><span><b>'.$rs->productName.'</b></span>'
			. '<span>@'.sg_date($rs->registerdate, cfg('date.format')).'</span>'
			. '</div>'
			. '<div class="detail">S/N : '.$rs->serial.'</div>'
			. $menu,
			array(
				'class' => 'sg-action btn-card -link',
				'href' => url('ibuy/customer/'.$customerId.'/serial/'.$rs->issnid),
				'data-rel' => 'box',
				'data-width' => 640,
				'data-webview' => true,
				'data-webview-title' => $rs->serial,
				'onclick' => '',
			)
		);
	}

	$ret .= $ticketCard->build();

	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o($customerInfo, '$customerInfo');
	$ret .= '</div>';
	return $ret;
}
?>