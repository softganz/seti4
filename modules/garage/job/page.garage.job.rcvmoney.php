<?php
function garage_job_rcvmoney($self, $jobInfo, $qtId = NULL) {
	if (!($jobId = $jobInfo->jobId)) return message('error', 'PROCESS ERROR');

	$shopInfo = $jobInfo->shopInfo;


	if (empty($jobInfo)) return message('error','ไม่มีข้อมูลตามที่ระบุ');

	new Toolbar($self,'บันทึกการรับเงิน - '.$jobInfo->plate,'job',$jobInfo);

	$ret = '<div id="garage-job-rcvmoney">';

	$qtInfo = $jobInfo->qt[$qtId];

	$ret .= '<header class="header"><h3>บันทึกการรับเงินของใบเสนอราคา '.$qtInfo->qtno.'</h3></header>';

	if ($qtInfo) {

		$form = new Form(NULL,url('garage/job/'.$jobInfo->tpid.'/info/qt.rcvmoney/'.$qtId),NULL,'sg-form garage-job-rcvmoney');
		$form->addData('rel', 'notify');
		$form->addData('done', 'reload');

		$form->addField('qtid', array('type' => 'hidden', 'value'=>$qtInfo->qtid));
		$form->addText('<b>ราคาประกันอนุมัติสำหรับใบเสนอราคา '.$qtInfo->qtno.' จำนวน '.number_format($qtInfo->replyprice,2).' บาท</b>');
		$form->addField(
			'date',
			array(
				'type' => 'text',
				'label' => 'วันที่รับเงิน',
				'name' => 'date',
				'readonly' => true,
				'class' => '-date sg-datepicker',
				'value' => sg_date(SG\getFirst($qtInfo->rcvmdate,date('Y-m-d')),'d/m/Y'),
				'placeholder' => '31/12/1999',
			)
		);

		$form->addField(
			'amount',
			array(
				'type' => 'text',
				'label' => 'จำนวนเงินที่รับ (บาท)',
				'name' => 'amount',
				'class' => '-money',
				'placeholder' => '0.00',
				'value' => number_format(SG\getFirst($qtInfo->rcvmoney,$qtInfo->replyprice),2),
			)
		);

		$form->addField(
			'rcvall',
			array(
				'type' => 'checkbox',
				'name' => 'rcvall',
				'options' => array('Yes' => 'รับเงินครบตามจำนวนเรียบร้อยแล้ว'),
				'value' => $jobInfo->isrecieved,
			)
		);

		$form->addField(
			'closejob',
			array(
				'type' => 'checkbox',
				'name' => 'closejob',
				'options' => array('Yes' => 'ปิดใบสั่งซ่อม'),
				'value' => $jobInfo->isjobclosed,
			)
		);

		$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -save -white"></i><span>บันทึกการรับเงิน</span>',
				'container' => '{class: "-sg-text-right"}',
			)
		);

		$ret .= $form->build();
	}



	$stmt = 'SELECT * FROM %garage_qt% WHERE `tpid` = :tpid';
	$dbs = mydb::select($stmt, ':tpid', $jobId);

	$tables = new Table();
	$tables->thead = array('qt -center' => 'ใบเสนอราคา','qtmoney -money' => 'ราคาเสนอ','moneyrcv -money' => 'รับเงิน','rcvdate -date' => 'วันที่รับเงิน','');
	foreach ($dbs->items as $rs) {
		$isRecieved = $rs->rcvmdate != '';
		$ui = new Ui();
		if ($rs->qtid) {
			$ui->add('<a class="sg-action" href="'.url('garage/job/'.$rs->tpid.'/rcvmoney/'.$rs->qtid).'" data-rel="replace:#garage-job-rcvmoney"><i class="icon -material '.($isRecieved ? '-green' : '-gray').'">'.($isRecieved ? 'done_all' : 'done').'</i></a>');
			if ($isRecieved) $ui->add('<a class="sg-action" href="'.url('garage/job/'.$rs->tpid.'/info/rcvmoney.cancel/'.$rs->qtid).'" data-rel="replace:#garage-job-rcvmoney" data-ret="'.url('garage/job/'.$jobId.'/rcvmoney').'" data-width="640" data-title="ยกเลิกการรับเงิน" data-confirm="ต้องการยกเลิกบันทึกการรับเงินนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>');
		}
		$menu = '<nav class="nav -icons">'.$ui->build().'</nav>';
		$tables->rows[] = array(
			$rs->qtno,
			$rs->replyprice ? number_format($rs->replyprice,2) : '',
			$rs->rcvmoney ? number_format($rs->rcvmoney,2) : '',
			$rs->rcvmdate ? sg_date($rs->rcvmdate,'d/m/ปปปป') : '',
			$menu
		);
	}
	$tables->tfoot[] = array(
		'',
		number_format($jobInfo->replyprice,2),
		number_format($jobInfo->rcvmoneyamt,2),
		$jobInfo->rcvmoneydate ? sg_date($jobInfo->rcvmoneydate, 'd/m/ปปปป') : '',
		''
	);
	$ret .= $tables->build();

	$ret .= '<!-- garage-job-rcvmoney --></div>';

	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o($qtInfo,'$qtInfo');
	//$ret .= print_o($jobInfo,'$jobInfo');

	return $ret;
}
?>