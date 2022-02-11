<?php
/**
* iBuy :: View Customer Information
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Object $customerInfo
* @return String
*/

$debug = true;

function ibuy_customer_view($self, $customerInfo) {
	if (!($customerId = $customerInfo->custid)) return message('error', 'PROCESS ERROR');

	$ret = '<section id="ibuy-customer-view" class="sg-view -co-2" data-url="'.url('ibuy/customer/'.$customerId).'">';

	$headerNav = new Ui();
	$headerNav->addConfig('nav', '{class: "nav"}');
	//$headerNav->add('<a class="sg-action btn -link" href="'.url('ibuy/customer/'.$customerId.'/serial').'" data-rel="box"><i class="icon -material">local_florist"></i><span>S/N</span></a>');
	$headerNav->add('<a class="sg-action btn -link" href="'.url('ibuy/customer/'.$customerId.'/info.map').'" data-rel="box" data-refresh="no" data-webview="'.$customerInfo->name.'"><i class="icon -material -customer-pin'.($customerInfo->info->location ? ' -active' : '').'">person_pin</i></a>');
	$headerNav->add('<a class="sg-action btn -link" href="'.url('ibuy/customer/'.$customerId.'/form').'" data-rel="box"><i class="icon -material">edit"></i></a>');

	//$ret = '<section id="ibuy-customer-info-ticket" class="ibuy-customer-info-ticket sg-view -co-2" data-url="'.url('ibuy/customer/'.$customerId.'/info.ticket/'.$ticketId).'">';

	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>'.$customerInfo->name.'</h3>'.$headerNav->build().'</header>';

	$ret .= '<div id="buy-customer-view-tran" class="-sg-view">';

	$cardUi = new Ui('div','ui-card');
	$cardStr = '<p>ชื่อลูกค้า : '.$customerInfo->name.'<br />'
		. 'ที่อยู่ : '.$customerInfo->info->custaddress.'<br />'
		. 'โทรศัพท์ : '.$customerInfo->info->custphone.'<br />'
		. '</p>';


	$ui = new Ui();

	$ui->add('<a class="sg-action btn -link" href="'.url('ibuy/customer/'.$customerId.'/service.form', array('ft' => 'open')).'" data-rel="box" data-width="640"><i class="icon -material">report_problem</i><span>รับแจ้งปัญหา</span></a>');
	$ui->add('<a class="sg-action btn -link" href="'.url('ibuy/customer/'.$customerId.'/service.form', array('ft' => 'service')).'" data-rel="box" data-width="640"><i class="icon -material">check_circle</i><span>บันทึกให้บริการ</span></a>');
	$ui->add('<a class="sg-action btn -link" href="'.url('ibuy/customer/'.$customerId.'/info.map').'" data-rel="box" data-width="640" data-height="640" data-refresh="no" data-webview="'.$customerInfo->name.'"><i class="icon -material -customer-pin'.($customerInfo->info->location ? ' -active' : '').'">person_pin</i><span>แผนที่</span></a>');

	$cardStr .= '<nav class="nav -card">'.$ui->build().'</nav>';

	$cardUi->add($cardStr);

	$ret .= $cardUi->build();


	$ticketList = R::Model('ibuy.ticket.get', '{customerId: '.$customerId.'}', '{debug: false, limit: "*"}');

	$ticketCard = new Ui('div a', 'ui-card ibuy-customer-ticket');

	foreach ($ticketList as $rs) {
		$ui = new Ui();
		$ui->add('<a class="sg-action" href="'.url('ibuy/customer/'.$customerId.'/info/ticket.remove/'.$rs->tickid).'" data-rel="notify" data-done="remove:parent tr" data-title="ลบบันทึก" data-confirm="ต้องการลบบันทึกนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>');
		$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
		$ticketCard->add(
			'<div class="header"><b>'.$rs->problem.'</b>'
			. ' @'.sg_date($rs->created, _DATE_FORMAT)
			. '<nav class="nav -header"><span class="btn"><i class="icon -material">check_circle_outline</i><span>'.$rs->status.'</span></span></nav>'
			. '</div>'
			. '<div class="detail">'.nl2br($rs->detail).'</div>'
			. $menu,
			array(
				'class' => 'sg-action -'.($rs->status),
				'href' => url('ibuy/customer/'.$customerId.'/info.ticket/'.$rs->tickid),
				'data-rel' => 'box',
				'data-width' => 640,
				'data-webview' => true,
				'data-webview-title' => $rs->problem,
				'onclick' => '',
			)
		);
	}

	$ret .= $ticketCard->build();

	$ret .= '</div>';

	$ret .= '<div class="-sg-view">';

	$ret .= '<div class="-sg-text-center"><a class="sg-action btn -link" href="'.url('ibuy/customer/'.$customerId.'/serial.form').'" data-rel="box" title="เพิ่ม Serial No. สินค้า"><i class="icon -material">add_circle</i><span>เพิ่มทะเบียนหมายเลขสินค้า</span></a></div>';

	//$ret .= R::Page('ibuy.customer.serial', NULL, $customerInfo);
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

	$ret .= '</div>';
	//$ret .= print_o($ticketList,'$ticketList');
	//$ret .= print_o($customerInfo, '$customerInfo');
	$ret .= '</section>';
	return $ret;
}
?>