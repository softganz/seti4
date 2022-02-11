<?php
/**
* Project :: Paiddoc Edit Form
* Created 2019-09-01
* Modify  2020-08-03
*
* @param Object $self
* @param Object $projectInfo
* @param Int $tranId
* @return String
*/

$debug = true;

function project_info_paiddoc_form($self, $projectInfo, $tranId = NULL) {
	if (!($tpid = $projectInfo->tpid)) return message('error', 'PROCESS ERROR');

	$isEdit = user_access('administer projects')
		|| in_array($projectInfo->info->membershipType, array('ADMIN','OFFICER'))
		|| in_array($fundInfo->officers[i()->uid], array('ADMIN','OFFICER'));

	$ret = '';

	if ($tranId) {
		$paiddocInfo = R::Model('project.paiddoc.get', $tpid, $tranId);
		$glTran = R::Model('project.gl.tran.get', $paiddocInfo->refcode);
	}

	$ui = new Ui();
	$ui->addConfig('nav', '{class: "nav"}');

	if ($isEdit) {
		if (count($glTran->items) > 2) {
			$ui->add('<a class="sg-action btn" href="'.url('project/'.$tpid.'/info/paiddoc.newcode/'.$tranId).'" data-rel="notify" data-done="load->clear:box:'.url('project/'.$tpid.'/info.paiddoc.form/'.$tranId).'" data-title="สร้างเลขที่เอกสารอ้างอิงใหม่" data-confirm="ต้องการสร้างเลขที่เอกสารอ้างอิงใหม่ กรุณายืนยัน?"><i class="icon -material">autorenew</i><span>Create new refcode</span></a>');
		}
		if (empty($glTran->items) || $paiddocInfo->orgid != $glTran->orgid) {
			$ui->add('<a class="sg-action btn -danger" href="'.url('project/'.$tpid.'/info/paiddoc.glcreate/'.$tranId).'" data-rel="notify" data-done="load->clear:box:'.url('project/'.$tpid.'/info.paiddoc.form/'.$tranId).'" data-title="สร้างรายการบัญชี" data-confirm="ต้ัองการสร้างรายการบัญชี กรุณายืนยัน?" rel="nofollow"><i class="icon -material -white">refresh</i><span>Re-Create GL Transaction</span></a>');
		}
	}

	$ret .= '<header class="header -box">'._HEADER_BACK.'<h3>ใบเบิกเงิน ('.$paiddocInfo->refcode.')</h3>'.$ui->build().'</header>';
	//$ret.=__project_form_paiddoc_menu($tpid,$topic,$fundInfo,$paiddocInfo->paidid);

	//$ret .= print_o($glTran, '$glTran');

	if (empty($paiddocInfo->amount)) {
		$paiddocInfo->amount=number_format($projectInfo->info->budget-$paided,2);
	}

	$form = new Form('data',url('project/'.$tpid.'/info/paiddoc.add/'.$paiddocInfo->paidid),'project-edit-paiddoc','sg-form');
	$form->addData('checkValid',true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load:#main:'.url('project/'.$tpid.'/info.paiddoc/'.$paiddocInfo->paidid));

	$form->addField('paidid',array('type'=>'hidden','value'=>$paiddocInfo->paidid));
	$form->addField('refcode',array('type'=>'hidden','value'=>$paiddocInfo->refcode));

	$form->addField(
		'docno',
		array(
			'type'=>'text',
			'label'=>'เลขที่ใบเบิกเงิน',
			'require'=>true,
			'value'=>htmlspecialchars($paiddocInfo->docno)
		)
	);

	$form->addField(
		'paiddate',
		array(
			'type'=>'text',
			'label'=>'วันที่',
			'require'=>true,
			'class'=>'sg-datepicker -date',
			'value'=>sg_date($paiddocInfo->paiddate,'d/m/Y'),
			'readonly'=>true,
			'attr'=>array(
			'data-max-date'=>date('d/m/Y'),
			'data-min-date'=>sg_date($projectInfo->info->date_approve,'d/m/Y')
			)
		)
	);

	$form->addField(
		'amount',
		array(
			'type' => 'text',
			'label' => 'จำนวนเงิน',
			'class' => '-money',
			'require' => true,
			'value' => htmlspecialchars($paiddocInfo->amount)
		)
	);

	$form->addField(
		'fieldname',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -save -white"></i><span>'.tr('Save').'</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" rel="nofollow" data-rel="close"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	$tables = new Table();
	$tables->caption = 'GL Transaction';
	$tables->thead = array('ID', 'code -nowrap' => 'GL Code', 'รายละเอียด', 'dr -money' => 'เดบิท', 'cr -money' => 'เครดิต');
	foreach ($glTran->items as $item) {
		$tables->rows[] = array(
			$item->pglid,
			$item->glcode,
			$item->glname,
			$item->amount >= 0 ? number_format($item->amount,2) : '',
			$item->amount < 0 ? number_format(abs($item->amount),2) : '',
		);
	}
	$ret .= $tables->build();

	//$ret .= print_o($glTran, '$glTran');
	//$ret .= print_o($paiddocInfo,'$paiddocInfo');

	//$ret.=print_o($fundInfo,'$fundInfo');
	//$ret.=print_o($projectInfo,'$projectInfo');
	return $ret;
}
?>