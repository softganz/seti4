<?php
/**
* Show Project Money Back Form
* Created 2019-10-01
* Modify  2019-10-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_info_moneyback_form($self, $projectInfo, $tranId = NULL) {
	if (!($tpid = $projectInfo->tpid)) return message('error', 'PROCESS ERROR');

	$fundInfo = R::Model('project.fund.get', $projectInfo->orgid);

	$isEdit = user_access('administer projects')
		|| in_array($projectInfo->info->membershipType, array('ADMIN','OFFICER'))
		|| in_array($fundInfo->officers[i()->uid], array('ADMIN','OFFICER'));

	R::View('project.toolbar', $self, $projectInfo->title, NULL, $projectInfo);

	if ($tranId) {
		$moneybackInfo = R::Model('project.moneyback.get', $tpid, $tranId);
		$glTran = R::Model('project.gl.tran.get', $moneybackInfo->refcode);
	}

	if (!$fundInfo->hasInitAccount) {
		return '<p class="notify">กองทุนยังไม่มีการกำหนดข้อมูลบัญชี-การเงิน</p><nav class="nav -page -sg-text-center"><a class="sg-action btn -primary" href="'.url('project/fund/'.$fundInfo->orgid.'/info.finance').'" data-rel="box" data-width="640">กำหนดข้อมูลบัญชี-การเงิน</a></nav>';
	}

	$paidDocs = R::Model('project.paiddoc.get', $tpid, NULL, NULL, '{getAllRecord: true, debug: false}');

	$moneyPaided = 0;
	foreach ($paidDocs as $rs) $moneyPaided += $rs->amount;

	$minDate = $projectInfo->info->date_approve > $fundInfo->finclosemonth ? $projectInfo->info->date_approve : $fundInfo->finclosemonth;

	$ui = new Ui();
	$ui->addConfig('nav', '{class: "nav"}');

	if ($tranId && $isEdit) {
		if (count($glTran->items) > 2) {
			//$ui->add('<a class="sg-action btn" href="'.url('project/'.$tpid.'/info/paiddoc.newcode/'.$tranId).'" data-rel="notify" data-done="load->clear:box:'.url('project/'.$tpid.'/info.paiddoc.form/'.$tranId).'" data-title="สร้างเลขที่เอกสารอ้างอิงใหม่" data-confirm="ต้องการสร้างเลขที่เอกสารอ้างอิงใหม่ กรุณายืนยัน?"><i class="icon -material">autorenew</i><span>Create new refcode</span></a>');
		}
		if (empty($glTran->items) || $moneybackInfo->orgid != $glTran->orgid) {
			$ui->add('<a class="sg-action btn -danger" href="'.url('project/'.$tpid.'/info/moneyback.glcreate/'.$tranId).'" data-rel="notify" data-done="load->clear:box:'.url('project/'.$tpid.'/info.moneyback.form/'.$tranId).'" data-title="สร้างรายการบัญชี" data-confirm="ต้ัองการสร้างรายการบัญชี กรุณายืนยัน?" rel="nofollow"><i class="icon -material -white">refresh</i><span>Re-Create GL Transaction</span></a>');
		}
	}

	$ret .= '<header class="header -box">'._HEADER_BACK.'<h3>บันทึกการคืนเงิน ('.$moneybackInfo->refcode.')</h3>'.$ui->build().'</header>';

	$form = new Form('data', url('project/'.$tpid.'/info/moneyback.save/'.$tranId), 'project-edit-moneyback', 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load:#main');

	$form->addField(
		'no',
		array(
			'type' => 'text',
			'label' => 'เลขที่ใบรับเงิน',
			'require' => true,
			'value' => htmlspecialchars($moneybackInfo->no)
		)
	);

	$form->addField(
		'rcvdate',
		array(
			'type' => 'text',
			'label' => 'วันที่',
			'require' => true,
			'class' => 'sg-datepicker -date',
			'value' => sg_date(SG\getFirst($moneybackInfo->rcvdate, date('Y-m-d')), 'd/m/Y'),
			'readonly' => true,
			'attr' => array(
				'data-max-date' => date('d/m/Y'),
				'data-min-date' => sg_date($minDate,'d/m/Y')
			)
		)
	);

	$form->addField(
		'amount',
		array(
			'type' => 'text',
			'label' => 'จำนวนเงินรับคืน',
			'class' => '-money',
			'require' => true,
			'value' => htmlspecialchars($moneybackInfo->amount)
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:Save}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('project/'.$tpid.'/info.paiddoc').'" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();


	$tables = new Table();
	$tables->caption = 'GL Transaction';
	$tables->thead = array('ID', 'GL Code', 'รายละเอียด', 'dr -money' => 'เดบิท', 'cr -money' => 'เครดิต');
	foreach ($glTran->items as $item) {
		$tables->rows[] = array(
			$item->pglid,
			$item->glcode,
			$item->glname,
			$item->amount > 0 ? number_format($item->amount,2) : '',
			$item->amount < 0 ? number_format(abs($item->amount),2) : '',
		);
	}
	$ret .= $tables->build();


	$ret .= '<p><b>โครงการ '.$projectInfo->title.'</b></p>';
	$ret .= '<p>งบประมาณ <b>'.number_format($projectInfo->info->budget,2).'</b> บาท เบิกจ่ายแล้ว <b>'.number_format($moneyPaided,2).'</b> บาท คงเหลือเบิกจ่าย <b>'.number_format($projectInfo->info->budget - $moneyPaided,2).'</b> บาท</p>';

	//$ret .= print_o($glTran, '$glTran');
	//$ret .= print_o($moneybackInfo,'$moneybackInfo');

	//$ret .= print_o($projectInfo,'$projectInfo');
	//$ret .= print_o($fundInfo,'$fundInfo');
	return $ret;
}
?>