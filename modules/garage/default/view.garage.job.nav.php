<?php
function view_garage_job_nav($rs = NULL, $options = '{}') {
	$getShop = post('shop');

	$searchTarget = url('garage/search');
	$selectTarget = url('garage/job');
	$searchForm = '<form class="search-box" method="get" action="'.$searchTarget.'" role="search"><input type="hidden" name="jobid" id="jobid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ป้อนทะเบียนรถหรือเลข job" data-query="'.url('garage/api/job', array('shop' => '*')).'" data-callback="'.$selectTarget.'" data-altfld="jobid"><button class="btn" type="submit"><i class="icon -material">search</i><span>ค้นหา</span></button></form>'._NL;

	$ui = new ui(NULL,'ui-nav');
	$dboxUi = new Ui(NULL,'ui-nav');

	//$ui->add('<a class="btn" href="'.url('garage/in').'" title="ใบรับรถ"><i class="icon -list"></i><span>ใบรับรถ</span></a>');
	$ui->add('<a class="btn" href="'.url('garage/job').'" title="ใบสั่งซ่อม"><i class="icon -list"></i><span>ใบสั่งซ่อม</span></a>');

	//$dboxUi->add('<a class="" href="'.url('garage/job').' " title="รายการใบสั่งซ่อม"><i class="icon -list"></i><span>รายการใบสั่งซ่อม</span></a>');

	if ($rs->tpid) {
			$ui->add('<a class="btn" href="'.url('garage/job/'.$rs->tpid).'"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>');
			//$ui->add('<a class="btn" href="'.url('garage/job/photo/'.$rs->tpid).'"><i class="icon -image"></i><span>ภาพถ่าย</span></a>');
			$ui->add('<a class="btn" href="'.url('garage/job/'.$rs->tpid.'/qt').'"><i class="icon -material">mail</i><span>ใบเสนอราคา</span></a>');
			$ui->add('<a class="btn" href="'.url('garage/job/'.$rs->tpid.'/do').'"><i class="icon -material">assignment</i><span>ใบสั่งงาน</span></a>');
			$ui->add('<a class="btn" href="'.url('garage/job/'.$rs->tpid.'/tech').'"><i class="icon -material">photo_album</i><span>ภาพถ่าย</span></a>');
			$ui->add('<a class="btn" href="'.url('garage/job/'.$rs->tpid.'/photo.download').'"><i class="icon -material">cloud_download</i><span>ดาวน์โหลด</span></a>');

		if (q(2) == 'info') {
			$ui->add('<a class="btn" href="'.url('garage/job/'.$rs->tpid).' " title=""><i class="icon -view"></i><span>รายละเอียดใบสั่งซ่อม</span></a>');

		} else {
			if (q(4) == 'edit') {
				$ui->add('<a class="btn" href="'.url('garage/job/'.$rs->tpid).' " title=""><i class="icon -view"></i><span>รายละเอียด</span></a>');
			}
			$dboxUi->add('<sep>');
			$dboxUi->add('<a class="" href="'.url('garage/job/'.$rs->tpid).' " title=""><i class="icon -viewdoc"></i><span>รายละเอียดใบสั่งซ่อม</span></a>');
			$dboxUi->add('<a class="" href="'.url('garage/job/'.$rs->tpid.'/qt').'"><i class="icon -viewdoc"></i><span>ใบเสนอราคา</span></a>');
			$dboxUi->add('<a class="" href="'.url('garage/job/'.$rs->tpid.'/do').'"><i class="icon -viewdoc"></i><span>ใบสั่งงาน</span></a>');
			$dboxUi->add('<sep>');
			$dboxUi->add('<a class="-disabled" href="'.url('garage/job/'.$rs->tpid.'/view/cancle').' " title=""><i class="icon -cancel"></i><span>ยกเลิกใบสั่งซ่อม</span></a>');
			$dboxUi->add('<a class="-disabled" href="'.url('garage/job/'.$rs->tpid.'/view/delete').' " title=""><i class="icon -delete"></i><span>ลบใบสั่งซ่อม</span></a>');
		}
		$ui->add('<a class="btn" href="javascript:window.print()" style="border-radius: 4px;"><i class="icon -print"></i><span>พิมพ์</span></a>');
	} else {
		$shopOptions = array('' => '==ทุกสาขา==');
		if ($rs->shopId) {
			foreach (mydb::select(
					'SELECT `shopid`, `shortname` FROM %garage_shop% WHERE `shopid` = :shopId OR `shopparent` = :shopId ORDER BY CONVERT(`shortname` USING tis620) ASC',
					':shopId', $rs->shopId
				)->items as $shopRs) {
				$shopOptions[$shopRs->shopid] = $shopRs->shortname;
			}
		}
		$form = new Form(NULL,url('garage/job'), NULL, '-sg-flex -flex-nowrap');
		$form->addConfig('method', 'GET');


		$showOptions = array(
			'' => 'ยังไม่ปิดจ็อบ',
			'notin' => 'ยังไม่รับรถ',
			'noretdate' => 'ยังไม่นัดรับรถ',
			'retdate' => 'นัดรับรถแล้ว',
			'notreturned' => 'ยังไม่คืนรถ',
			'returned' => 'คืนรถแล้ว',
			'notrecieved' => 'ยังไม่รับเงิน',
			'recieved' => 'รับเงินแล้ว',
			'notclosed' => 'ยังไม่ปิดจ็อบ',
			'closed' => 'จ็อบปิดแล้ว',
		);

		$form->addField(
			'shop',
			array(
				'type' => 'select',
				'options' => $shopOptions,
				'value' => $getShop,
				'attr' => 'onchange="form.submit()"',
			)
		);

		$form->addField(
			'show',
			array(
				'type' => 'select',
				'name' => 'show',
				'options' => $showOptions,
				'value' => post('show'),
				'attr' => 'onchange="form.submit()"',
			)
		);
		$ui->add($form->build());
	}

	return Array('main' => $ui, 'more' => $dboxUi, 'preText' => $searchForm);
}
?>