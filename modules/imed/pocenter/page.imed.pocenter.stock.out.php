<?php
/**
* Prosthesis and Orthosis Center
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function imed_pocenter_stock_out($self, $orgId = NULL, $stockId) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.pocenter.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	if (is_object($stockId)) {
		$data = $stockId;
	} else {
		$data = new stdClass();
		$data->stkid = $stockId;
	}

	R::View('imed.toolbar', $self, $orgInfo->name.' @ศูนย์กายอุปกรณ์', 'pocenter', $orgInfo);

	if (!$orgInfo) return message('error', 'ไม่มีข้อมูลตามที่ระบุ');

	$ret = '';

	$isAdmin = user_access('administer imeds')
					|| $orgInfo->RIGHT & _IS_ADMIN;
	$isEditable = $isAdmin || $orgInfo->is->officer;

	$ui = new Ui();
	//$ui->add('<a href=""><i class="icon -material">delete</i></a>');
	//$ui->add('<a href=""><i class="icon -material">close</i></a>');
	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action -back" href="'.url('imed/pocenter/'.$orgId.'/stock.card/'.$data->stkid).'" data-rel="box"><i class="icon -material">arrow_back</i></a></nav><h3>บันทึกจ่ายกายอุปกรณ์</h3><nav class="nav">'.$ui->build().'</nav></header>';


	$form = new Form('stk',url('imed/pocenter/'.$orgId.'/stock.tr.save'),NULL,'sg-form');
	$form->addData('rel','none');
	$form->addData('done', 'close | load:#imed-pocenter-stock');

	$form->addField('stktrid',array('type'=>'hidden','value'=>$data->stktrid));
	$form->addField('trtype',array('type'=>'hidden','value'=>'OUT'));
	$form->addField('psnid',array('type'=>'hidden','id'=>'psnid','value'=>$data->psnid));

	$form->addField(
		'stkdate',
		array(
			'type' => 'text',
			'label' => 'วันที่',
			'class' => 'sg-datepicker -date',
			'require' => true,
			'value' => sg_date(SG\getFirst($data->stkdate, date('U')),'d/m/Y'),
		)
	);

	$form->addField(
		'refname',
		array(
			'type' => 'text',
			'label' => 'ชื่อผู้รับ',
			'class' => 'sg-autocomplete -fill',
			'value' => htmlspecialchars($data->refname),
			'attr'=>array(
				'data-query'=>url('imed/api/person'),
				'data-altfld'=>'psnid',
			),
		)
	);

	$stmt = 'SELECT `stkid`,`name` FROM %imed_stkcode% WHERE `parent` IN ( "01", "03" ) ORDER BY CONVERT(`name` USING tis620) ASC';
	$dbs = mydb::select($stmt);

	$options = array(''=>'== เลือกกายอุปกรณ์ ==');
	foreach ($dbs->items as $rs) $options[$rs->stkid] = $rs->name;
	$form->addField(
		'stkid',
		array(
			'type' => 'select',
			'label' => 'กายอุปกรณ์:',
			'class' => '-fill',
			'require' => true,
			'options' => $options,
			'value' => $data->stkid,
		)
	);

	$form->addField(
		'qty',
		array(
			'type' => 'text',
			'label' => 'จำนวน',
			'class' => '-numeric',
			'require' => true,
			'value' => $data->qty,
		)
	);

	$form->addField(
		'description',
		array(
			'type' => 'text',
			'label' => 'บันทึกช่วยจำ',
			'class' => '-fill',
			'value' => htmlspecialchars($data->description),
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>บันทึกรับ</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
			'container' => array('class'=>'-sg-text-right'),
		)
	);

	$ret .= $form->build();

	$ret .= R::Page('imed.pocenter.stock.card',NULL, $orgId, $data->stkid);
	//$ret .= print_o($orgInfo,'$orgInfo');

	return $ret;
}
?>