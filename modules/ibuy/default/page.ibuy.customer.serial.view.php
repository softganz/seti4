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

function ibuy_customer_serial_view($self, $customerInfo, $serialId = NULL) {
	if (!($customerId = $customerInfo->custid)) return message('error', 'PROCESS ERROR');

	$serialInfo = R::Model('ibuy.serial.get', $serialId, '{debug: false}');

	if (!$serialInfo) return message('error', 'PROCESS ERROR');

	$isEdit = $customerInfo->RIGHT & _IS_ADMIN;

	$ret = '<section id="ibuy-customer-serial-view" class="ibuy-customer-serial-view sg-view -co-2" data-url="'.url('ibuy/customer/'.$customerId.'/serial/'.$serialId).'">';


	$headerNav = new Ui();
	$headerNav->addConfig('nav', '{class: "nav"}');
	$headerNav->add('<a class="sg-action btn -link" href="'.url('ibuy/customer/'.$customerId.'/service.form', array('sn' => $serialId, 'ft' => 'open')).'" data-rel="box" data-width="640"><i class="icon -material">report_problem</i><span>รับแจ้งปัญหา</span></a>');
	$headerNav->add('<a class="sg-action btn -link" href="'.url('ibuy/customer/'.$customerId.'/service.form', array('sn' => $serialId, 'ft' => 'service')).'" data-rel="box" data-width="640"><i class="icon -material">check_circle</i><span>บันทึกให้บริการ</span></a>');
	$headerNav->add('<a class="sg-action btn -link" href="'.url('ibuy/customer/'.$customerId.'/serial.form/'.$serialId).'" data-rel="box" title="เพิ่ม Serial No. สินค้า"><i class="icon -material">edit</i></a>');
	$headerNav->add('<a class="sg-action btn -link" href="'.url('ibuy/customer/'.$customerId.'/info/serial.remove/'.$serialId).'" data-rel="notify" title="ลบทะเบียนหมายเลขสินค้า" data-title="ลบทะเบียนหมายเลขสินค้า" data-confirm="ต้องการลบทะเบียนสินค้า กรุณายืนยัน?" data-done="back | load->replace:#ibuy-customer-view"><i class="icon -material">delete</i></a>');

	$ret .= '<header class="header -box  -app-hide-header">'._HEADER_BACK.'<h3>'.$serialInfo->productName.'</h3>'.$headerNav->build().'</header>';

	$ret .= '<div class="-sg-view">';

	$ticketList = R::Model('ibuy.ticket.get', '{customerId: '.$customerId.', serialId: '.$serialId.'}', '{debug: false, limit: "*"}');

	$ticketCard = new Ui('div', 'ui-card ibuy-customer-ticket');

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

	//$ret .= print_o($ticketList, '$ticketList');

	$ret .= '</div>';

	$ret .= '<div class="-sg-view">';
	$ret .= '<p>หมายเลขสินค้า (S/N)<br />'.$serialInfo->info->serial.'</p>';
	$ret .= '<p>หมายเลขเครื่อง<br />'.$serialInfo->info->machineno.'</p>';
	$ret .= '<p>รุ่น (Model)<br />'.$serialInfo->info->modelinfo.'</p>';
	$ret .= '<p>วันที่ขาย<br />'.sg_date($serialInfo->info->saledate).'</p>';
	$ret .= '<p>วันที่ลงทะเบียน<br />'.sg_date($serialInfo->info->registerdate).'</p>';
	$ret .= '<p>วันที่หมดประกัน<br />'.sg_date($serialInfo->info->warrentydate1).'</p>';
	$ret .= '<p>ค่าบำรุงรักษา/ปี<br />'.number_format($serialInfo->info->maintfee,2).' บาท</p>';
	//$ret .= print_o($serialInfo, '$serialInfo');

	$ret .= '</div>';

	$ret .= '</section>';

	return $ret;
}
?>