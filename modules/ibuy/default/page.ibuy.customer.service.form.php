<?php
/**
* iBuy :: Customer Service Form
* Created 2019-11-15
* Modify  2020-02-07
*
* @param Object $self
* @return String
*/

$debug = true;

function ibuy_customer_service_form($self, $customerInfo, $ticketId = NULL) {
	if (!($customerId = $customerInfo->custid)) return message('error', 'PROCESS ERROR');

	$getFormType = post('ft');
	$getSerialId = post('sn');

	if ($ticketId) {
		$ticketInfo = R::Model('ibuy.ticket.get', $ticketId, '{includeThread: true, debug: false}');
		//$stmt = 'SELECT tk.*, th.`problem` `threadProblem` FROM %ticket% tk LEFT JOIN %ticket% th ON th.`tickid` = tk.`thread` WHERE tk.`custid` = :custid AND tk.`tickid` = :tickid LIMIT 1';
		//$ticketInfo = mydb::select($stmt, ':custid', $customerId, ':tickid', $ticketId);
	} else if ($getSerialId) {
		$serialInfo = R::Model('ibuy.serial.get', $getSerialId);
	}

	if ($ticketInfo->info->problem || $ticketInfo->info->threadProblem) $headerText = SG\getFirst($ticketInfo->info->problem, $ticketInfo->info->threadProblem);
	else if ($getFormType == 'open') $headerText = 'บันทึกรับแจ้งปัญหา';
	else if ($getFormType == 'service') $headerText = 'บันทึกให้บริการลูกค้า';

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>'.$headerText.'</h3></header>';

	// Select serial no from customer product list

	$form = new Form('ticket', url('ibuy/customer/'.$customerId.'/info/ticket.save'.($ticketId ? '/'.$ticketId : ''), array('ft' => $getFormType)), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'back | load->replace:#ibuy-customer-info-ticket | load->replace:#ibuy-customer-serial-view | load->replace:#ibuy-customer-view');


	$serialList = R::Model('ibuy.serial.get', array('customerId' => $customerId), '{limit: "*", order: "`issnid` DESC", debug: false}');

	$serialOptions = array();
	foreach ($serialList as $item) $serialOptions[$item->serialId] = 'S/N : '.$item->serial. ' - '.$item->productName;

	$form->addField(
		'issnid',
		array(
			'type' => 'select',
			'label' => 'หมายเลขสินค้า/ชื่อสินค้า:',
			'class' => '-fill',
			'readonly' => $ticketId || $serialInfo->serialId ? true : false,
			'options' => $serialOptions,
			'value' => $serialInfo->serialId,
		)
	);


	/*
	if (!$ticketId) {

		$stmt = 'SELECT s.`tpid`, s.`serial`, IFNULL(s.`stkdesc`, t.`title`) `productName`
			FROM %ibuy_serial% s
				LEFT JOIN %topic% t USING(`tpid`)
			WHERE `custid` = :custid';
		$dbs = mydb::select($stmt, ':custid', $customerId);
		//$ret .= print_o($dbs,'$dbs');

		$serialUi = new Ui(NULL, 'ui-menu');
		foreach ($dbs->items as $rs) {
			$serialUi->add('<a href="javascript:void(0)">'.$rs->productName.'<br />S/N. '.$rs->serial.'</a>');
		}

		if ($serialInfo) {
			$form->addField(
				'productname',
				array(
					'label' => 'ชื่อสินค้า',
					'type' => 'text',
					'class' => '-fill',
					'readonly' => true,
					'value' => htmlspecialchars($serialInfo->productName),
				)
			);
		} else {
			$form->addField(
				'productname',
				array(
					'label' => 'ชื่อสินค้า',
					'type' => 'text',
					'class' => '-fill',
					'require' => true,
					'value' => htmlspecialchars($ticketInfo->productname),
					'placeholder' => 'ระบุชื่อชื่อสินค้าที่ให้บริการ',
					'posttext' => '<div class="input-append">'.sg_dropbox($serialUi->build(), '{icon: "material", iconText: "keyboard_arrow_down"}').'<!-- <span class="sg-dropbox click leftside"><a class="sg-dropbox btn -link" href=""><i class="icon -material">keyboard_arrow_down</i></a></span>--></div>',
					'container' => '{class: "-group"}'
				)
			);
		}

	}
	*/

	if (!$ticketInfo->info->thread) {
		$form->addField(
			'problem',
			array(
				'label' => 'สภาพปัญหา',
				'type' => 'text',
				'class' => '-fill',
				'require' => true,
				'value' => htmlspecialchars($ticketInfo->problem),
				'placeholder' => 'ระบุสภาพปัญหาที่เกิดขึ้นโดยย่อ',
			)
		);
	}

	$form->addField(
		'detail',
		array(
			'label' => 'รายละเอียด/การดำเนินการ',
			'type' => 'textarea',
			'class' => '-fill',
			'rows' => 3,
			'value' => $ticketInfo->info->detail,
			'placeholder' => 'ระบุรายละเอียดปัญหา/วิธีดำเนินการ/ผลลัพธ์',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" data-rel="back"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	//$ret .= print_o($ticketInfo,'$ticketInfo');

	return $ret;
}
?>