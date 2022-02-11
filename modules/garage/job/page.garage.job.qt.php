<?php
/**
* Garage Jon Quotation
* Created 2019-10-14
* Modify  2019-10-14
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_job_qt($self, $jobInfo, $qtId = NULL) {
	if (!($jobId = $jobInfo->jobId)) return message('error', 'PROCESS ERROR');

	$shopInfo = $jobInfo->shopInfo;

	if (!$jobId) return message('error', 'PROCESS ERROR');

	$isEdit = $jobInfo->is->editable;

	if (!$isEdit) return message('error', 'Access Denied');

	$ret = '';

	new Toolbar($self,'ใบสั่งซ่อม - '.$jobInfo->plate.'@'.$jobInfo->shopShortName,'job',$jobInfo);
	page_class('-job-type-'.$jobInfo->shopShortName);

	//$ret .= 'Next QT od shopId '.$jobInfo->shopid.' = '.print_o(R::Model('garage.nextno', $jobInfo->shopid, 'qt', NULL, '{debug: true}'));
	$ui = new Ui();

	if (empty($jobInfo->qt)) {
		if ($isEdit) $ui->add('<a class="sg-action btn" href="'.url('garage/job/'.$jobId.'/qt.form').'" data-rel="box" data-width="640"><i class="icon -material">note_add</i><span>สร้างใบเสนอราคา</span></a>');
	} else {
		if (empty($qtId) && !empty($jobInfo->qt)) $qtId=reset($jobInfo->qt)->qtid;
		foreach ($jobInfo->qt as $rs) {
			$ui->add('<a class="btn" href="'.url('garage/job/'.$jobId.'/qt/'.$rs->qtid).'"><i class="icon -material">find_in_page</i><span>'.$rs->qtno.'</span></a>');
		}
		if ($isEdit) $ui->add('<a class="sg-action btn" href="'.url('garage/job/'.$jobId.'/qt.form').'" data-rel="box" data-width="640"><i class="icon -material">note_add</i><span>แยกใบเสนอราคา</span></a>');

		// Check if can delete
		$isDeletable = false;
		$isQtTran = mydb::select('SELECT `qtid` FROM %garage_jobtr% WHERE `qtid`=:qtid LIMIT 1',':qtid',$qtId)->qtid;
		$isDeletable = $jobInfo->qt[$qtId] && empty($isQtTran) && $jobInfo->qt[$qtId]->replyprice==0 && empty($jobInfo->qt[$qtId]->billid) && empty($jobInfo->qt[$qtId]->rcvid);

		if ($isDeletable) {
			$ui->add('<sep>');
			$ui->add(' <a class="sg-action btn" href="'.url('garage/job/'.$jobId.'/info/qt.delete/'.$qtId).'" data-rel="notify" data-done="reload:'.url('garage/job/'.$jobId.'/qt').'" data-title="ลบใบเสนอราคา" data-confirm="ต้องการลบใบเสนอราคานี้ กรุณายืนยัน?"><i class="icon -material">delete</i><span>ลบ '.$jobInfo->qt[$qtId]->qtno.'</span></a>');
		}
	}

	$ret .= '<nav class="nav -page -no-print">'.$ui->build().'</nav>';

	if ($qtInfo = $jobInfo->qt[$qtId]) {
		$ret .= '<div id="garage-job-qt" class="garage-bill -job-qt -forprint">'._NL;

		$ret .= '<section class="-header">'._NL;
		$ret .= '<address>'.$shopInfo->shopname.'<br />'.$shopInfo->shopaddr.' '.$shopInfo->shopzipcode.' <br />โทร. '.$shopInfo->shopphone.'</address>'._NL;

		$ret .= '<h3 class="-title" '.(empty($qtId)?' notify':'').'">ใบเสนอราคา'.(empty($qtId)?' (ยังไม่ได้สร้าง)':'').'</h3>'._NL;

		$ret .= '<div class="-info">';
		$ret .= '<p>เรื่อง <b>ขอเสนอราคาซ่อมรถยนต์</b></p>'._NL;
		$ret .= '<p>เรียน <b>ผู้จัดการ '.$qtInfo->insurername.'</b></p>'._NL;
		$ret .= '</div><!-- -info -->';

		$ret .= '<div class="-date">';
		$ret .= '<p class="-bill-id">เลขที่ <b>'.$qtInfo->qtno.'</b></p>';
		$ret .= '<p class="-bill-date">วันที่ '.($qtInfo->qtdate ? sg_date($qtInfo->qtdate, 'ว ดดด ปปปป') : '').'</p>'._NL;
		$ret .= '</div><!-- -date -->';

		$ret .= '<div class="-description">';
		$ret .= '<p class="-indent">ข้าพเจ้า <b>'.$shopInfo->shopname.'</b> ขอเสนอราคาซ่อมรถยนต์ มีรายละเอียดดังต่อไปนี้ </p>'._NL;

		$ret .= '<div class="card">'._NL;
		$ret .= '<div class="card-item"><span class="label">ยี่ห้อ</span><span class="value"><b>'.$jobInfo->brandid.'</b></span></div>'._NL;
		$ret .= '<div class="card-item"><span class="label">รุ่น</span><span class="value"><b>'.$jobInfo->modelname.'</b></span></div>'._NL;
		$ret .= '<div class="card-item"><span class="label">ทะเบียน</span><span class="value"><b>'.$jobInfo->plate.'</b></span></div>'._NL;
		if ($jobInfo->enginno) $ret .= '<div class="card-item"><span class="label">หมายเลขเครื่อง</span><span class="value"><b>'.$jobInfo->enginno.'</b></span></div>'._NL;
		if ($jobInfo->bodyno) $ret .= '<div class="card-item"><span class="label">หมายเลขตัวถัง</span><span class="value"><b>'.$jobInfo->bodyno.'</b></span></div>'._NL;
		if ($jobInfo->carwaitno) $ret .= '<div class="card-item"><span class="label">เลขรถรอ</span><span class="value"><b>'.$jobInfo->carwaitno.'</b></span></div>'._NL;
		if ($jobInfo->carinno) $ret .= '<div class="card-item"><span class="label">เลขรถเข้า</span><span class="value"><b>'.$jobInfo->carinno.'</b></span></div>'._NL;
		$ret .= '<div class="card-item"><span class="label">เลขรับแจ้งประกันภัย</span><span class="value"><b>'.$qtInfo->insuclaimcode.'</b></span></span></div>'._NL;
		$ret .= '</div><!-- card -->'._NL;

		if ($qtId) {
			$ret .= '<nav class="nav -page -sg-flex -no-print" style="flex-wrap: wrap; justify-content: flex-end;">';
			$ui = new Ui();
			// $ui->addConfig('nav', '{class: "nav -page"}');
			if ($isEdit) {
				$ui->add('<a class="sg-action btn" href="'.url('garage/job/'.$jobInfo->tpid.'/qt.form/'.$qtId).'" data-rel="box" data-width="640"><i class="icon -material">edit</i><span>แก้ไข</span></a>');
				$ui->add('<a class="sg-action btn -primary" href="'.url('garage/job/'.$jobInfo->tpid.'/qt.addtr/'.$qtId).'" data-rel="box" data-width="640"><i class="icon -material -white">playlist_add</i><span>เพิ่มรายการ</span></a>');
				$ui->add('<a class="sg-action btn'.($qtInfo->replyprice > 0 ? ' -success' : '').'" href="'.url('garage/job/'.$jobInfo->tpid.'/qt.tran/'.$qtId,array('action' => 'reply')).'" data-rel="#garage-job-qt-trans"><i class="icon -material">'.($qtInfo->replyprice > 0 ? 'done_all' : 'done').'</i><span>บันทึกราคาประกันอนุมัติ</span></a>');
			}
			$ret .= $ui->build()._NL;

			$ui = new Ui();
			if ($qtInfo->invoiceid) {
				$ui->add('<a class="btn" href="'.url('garage/invoice/'.$qtInfo->invoiceid).'"><i class="icon -material">alarm_on</i><span>ใบแจ้งหนี้</span></a>');
			}
			if ($qtInfo->billid) {
				$ui->add('<a class="btn" href="'.url('garage/billing/view/'.$qtInfo->billid).'"><i class="icon -material">check_box</i><span>ใบวางบิล</span></a>');
			}
			if ($qtInfo->rcvid) {
				$ui->add('<a class="btn" href="'.url('garage/recieve/'.$qtInfo->rcvid).'"><i class="icon -material">attach_money</i><span>ใบเสร็จรับเงิน</span></a>');
			}
			$ret .= $ui->build()._NL;
			$ret .= '</nav>'._NL;
		}
		$ret .= '</div><!-- -description -->';

		$ret .= '</section><!-- -header -->'._NL;


		$ret .= '<section id="garage-job-qt-trans" class="bill-tran" data-url="'.url('garage/job/'.$jobId.'/qt.tran/'.$qtId).'">'._NL;
		$ret .= R::Page('garage.job.qt.tran',NULL, $jobInfo, $qtId)._NL;
		$ret .= '</section><!-- garage-job-qt-trans -->'._NL;

		$ret .= '</div><!-- garage-job-qt -->';

		// $ret .= print_o($qtInfo, '$qtInfo');
	}
	return $ret;
}
?>
