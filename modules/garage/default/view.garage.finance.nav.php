<?php
function view_garage_finance_nav($rs=NULL,$options='{}') {
	$ret='';

	//$isEdit=user_access('administer projects','edit own project content',$uid) || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid);
	$ui=new ui(NULL,'ui-nav');
	$dboxUi=new Ui(NULL,'ui-nav');

	$ui->add('<a class="btn" href="'.url('garage/qt').'" title="ใบเสนอราคา"><i class="icon -material">mail</i><span>ใบเสนอราคา</span></a>');
	$ui->add('<a class="btn" href="'.url('garage/invoice').'" title="ใบแจ้งหนี้"><i class="icon -material">alarm_on</i><span>ใบแจ้งหนี้</span></a>');
	$ui->add('<a class="btn" href="'.url('garage/billing').'" title="ใบวางบิล"><i class="icon -material">check_box</i><span>ใบวางบิล</span></a>');
	$ui->add('<a class="btn" href="'.url('garage/recieve').'" title="ใบเสร็จรับเงิน"><i class="icon -material">attach_money</i><span>ใบเสร็จรับเงิน</span></a>');
	$ui->add('<a class="btn" href="'.url('garage/rcvmoney').'" title="ใบรับเงิน"><i class="icon -material">money</i><span>ใบรับเงิน</span></a>');
	$ui->add('<a class="btn" href="'.url('garage/appaid').'" title="จ่ายชำระหนี้"><i class="icon -material">monetization_on</i><span>จ่ายชำระหนี้</span></a>');

	// ใบเสร็จรับเงิน
	if ($rs->rcvid) {
		$dboxUi->add('<a class="sg-action" href="'.url('garage/recieve/'.$rs->rcvid.'/form.head').'" data-rel="box" title="แก้ไขใบเสร็จรับเงิน" data-width="640"><i class="icon -edit"></i><span>แก้ไขใบเสร็จรับเงิน</span></a>');
		if (empty($rs->qt) && $rs->rcvstatus!=_CANCEL) {
			$dboxUi->add('<a class="sg-action" href="'.url('garage/info/'.$rs->rcvid.'/recieve.cancel').'" data-title="ยกเลิกใบเสร็จรับเงิน" data-confirm="ต้องการยกเลิกใบเสร็จรับเงิน กรุณายืนยัน?"><i class="icon -cancel"></i><span>ยกเลิกใบเสร็จรับเงิน</span></a>');
		}
	} else if ($rs->billid) {
		if (empty($rs->qt)) {
			if ($rs->billstatus < 0) {
				$dboxUi->add('<a class="sg-action" href="'.url('garage/billing/edit/'.$rs->billid.'/recall').'" data-title="เรียกคืนใบวางบิล" data-confirm="ต้องการเรียกคืนใบวางบิล กรุณายืนยัน?"><i class="icon -undo"></i><span>เรียกคืนใบวางบิล</span></a>');
			} else {
				$dboxUi->add('<a class="sg-action" href="'.url('garage/billing/edit/'.$rs->billid.'/cancel').'" data-title="ยกเลิกใบวางบิล" data-confirm="ต้องการยกเลิกใบวางบิล กรุณายืนยัน?"><i class="icon -cancel"></i><span>ยกเลิกใบวางบิล</span></a>');
			}
		}
	} else if ($rs->invoiceid) {
		$dboxUi->add('<a class="sg-action" href="'.url('garage/invoice/'.$rs->invoiceid.'/form').'" data-rel="box" data-width="640"><i class="icon -material">edit</i><span>แก้ไขใบแจ้งหนี้</span></a>');
		if ($rs->docstatus == _CANCEL) {
			$dboxUi->add('<a class="sg-action" href="'.url('garage/job/*/invoice.recall/'.$rs->invoiceid).'" data-rel="notify" data-done="reload" data-title="เรียกคืนใบแจ้งหนี้" data-confirm="ต้องการเรียกคืนใบแจ้งหนี้ กรุณายืนยัน?"><i class="icon -material">restore</i><span>เรียกคืนใบแจ้งหนี้</span></a>');
		} else if (empty($rs->trans) && $rs->docstatus != _CANCEL) {
			$dboxUi->add('<a class="sg-action" href="'.url('garage/job/*/invoice.cancel/'.$rs->invoiceid).'" data-rel="notify" data-done="reload" data-title="ยกเลิกใบแจ้งหนี้" data-confirm="ต้องการยกเลิกใบแจ้งหนี้ กรุณายืนยัน?"><i class="icon -material">cancel</i><span>ยกเลิกใบแจ้งหนี้</span></a>');
		}
	}

	if ($rs) $ui->add('<a class="btn" href="javascript:window.print()" style="border-radius: 4px;"><i class="icon -print"></i><span class="-hidden">พิมพ์</span></a>');

	//$ret.=$ui->build()._NL;

	//if ($dboxUi->count()) $ret.=sg_dropbox($dboxUi->build(),'{class:"leftside -atright"}');

	//debugMsg($rs,'$rs');
	return Array('main' => $ui, 'more' => $dboxUi);
}
?>