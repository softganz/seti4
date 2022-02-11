<?php
/**
* Garage :: Show Recieve Edit Header Form
* Created 2020-06-16
* Modify  2020-06-16
*
* @param Object $self
* @param Int $rcvId
* @param String $action
* @return String
*/

$debug = true;

function garage_recieve_form_tran($self, $rcvInfo, $tranId) {
	if (!($rcvId = $rcvInfo->rcvid)) return message('error', 'PROCESS ERROR:NO RECIEVE');

	$shopInfo = $rcvInfo->shopInfo;

	new Toolbar($self,'ใบเสร็จรับเงิน'.($rcvInfo?' - '.$rcvInfo->rcvno:''),'finance',$rcvInfo);

	if (empty($rcvId)) return '<p class="notify">ไม่มีรายการที่ระบุ</p>';

	$data = $rcvInfo->qt[$tranId];

	$ret = '<header class="header">'._HEADER_BACK.'<h3 class="title">แก้ไขรายการ</h3></header>';

	$form = new Form(NULL,url('garage/info/'.$rcvId.'/recieve.tran.save/'.$tranId), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load');

	$form->addField(
		'price',
		array(
			'label' => 'ราคาต่อหน่วย',
			'type' => 'text',
			'class' => '-money',
			'value' => $data->unitprice,
			'placeholder' => '0.00',
			'posttext' => '<div class="input-append"><span>บาท</span></div>',
			'container' => '{class: "-group -sg-flex"}',
		)
	);

	$form->addField(
		'vat',
		array(
			'label' => 'ภาษีมูลค่าเพิ่ม',
			'type' => 'text',
			'class' => '-money',
			'value' => $data->vat,
			'placeholder' => '0.00',
			'posttext' => '<div class="input-append"><span>บาท</span></div>',
			'container' => '{class: "-group -sg-flex"}',
		)
	);

	$form->addField(
		'total',
		array(
			'label' => 'รวมเงิน',
			'type' => 'text',
			'readonly' => true,
			'class' => '-money',
			'value' => $data->replyprice,
			'placeholder' => '0.00',
			'posttext' => '<div class="input-append"><span>บาท</span></div>',
			'container' => '{class: "-group -sg-flex"}',
		)
	);

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('garage/recieve/'.$rcvId).'" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
			'container' => array('class'=>'-sg-text-right'),
		)
	);

	$ret.=$form->build();

	//$ret.=print_o($rcvInfo,'$rcvInfo');
	//$ret.=print_o($data,'$data');

	return $ret;
}
?>