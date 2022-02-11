<?php
/**
* Garage Invoice Header Form
* Created 2019-10-15
* Modify  2019-10-15
*
* @param Object $self
* @param Int $invoiceId
* @return String
*/

$debug = true;

function garage_invoice_form($self, $invoiceId) {
	$shopInfo = R::Model('garage.get.shop');

	$invoiceInfo = R::Model('garage.invoice.get', $shopInfo->shopid, $invoiceId);

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3 class="title">แก้ไขใบแจ้งหนี้</h3></header>';

	$form = new Form('data',url('garage/job/*/invoice.save/'.$invoiceInfo->invoiceid), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load:#main');

	$form->addField(
		'docdate',
		array(
			'label'=>'วันที่',
			'type'=>'text',
			'class'=>'-fill sg-datepicker',
			'value'=>sg_date($invoiceInfo->docdate,'d/m/Y')
		)
	);

	$form->addField(
		'custname',
		array(
			'label'=>'นามผู้ซื้อ',
			'type'=>'text',
			'class'=>'-fill',
			'value'=>$invoiceInfo->custname
		)
	);

	$form->addField(
		'address',
		array(
			'label'=>'ที่อยู่',
			'type'=>'textarea',
			'class'=>'-fill',
			'value'=>$invoiceInfo->address,
			'rows'=>2
		)
	);

	$form->addField(
		'phone',
		array(
			'label'=>'โทรศัพท์',
			'type'=>'text',
			'class'=>'-fill',
			'value'=>$invoiceInfo->phone
		)
	);

	$form->addField(
		'taxid',
		array(
			'label'=>'เลขประจำตัวผู้เสียภาษี',
			'type'=>'text',
			'class'=>'-fill',
			'value'=>$invoiceInfo->taxid,
			'maxlength'=>13
		)
	);

	$form->addField(
		'branch',
		array(
			'label'=>'สาขาลำดับที่',
			'type'=>'text',
			'class'=>'-fill',
			'value'=>$invoiceInfo->branch
		)
	);

	$form->addField(
		'vatrate',
		array(
			'label'=>'อัตราภาษีร้อยละ',
			'type'=>'text',
			'class'=>'-fill',
			'value'=>$invoiceInfo->vatrate
		)
	);

	$form->addField(
		'remark',
		array(
			'label'=>'หมายเหตุ',
			'type'=>'textarea',
			'class'=>'-fill',
			'value'=>$invoiceInfo->remark,
			'rows'=>3
		)
	);

	$form->addField(
		'showno',
		array(
			'type'=>'checkbox',
			'value'=>$invoiceInfo->showno,
			'options'=>array('1'=>'แสดงลำดับที่')
		)
	);

	$form->addField(
		'showsingle',
		array(
			'type'=>'checkbox',
			'value'=>$invoiceInfo->showsingle,
			'options'=>array('1'=>'รวมรายการ')
		)
	);

	$form->addField(
		'showinsuno',
		array(
			'type'=>'checkbox',
			'value'=>$invoiceInfo->showinsuno,
			'options'=>array('1'=>'แสดงเลขกรมธรรม์')
		)
	);

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
			'container' => array('class'=>'-sg-text-right'),
		)
	);

	$ret.=$form->build();

	$ret.='<p>หมายเหตุ : การแก้ไขข้อมูลบริษัทประกันจะไม่มีผลต่อข้อมูลในใบแจ้งหนี้ที่สร้างไว้แล้ว</p>';

	//$ret.=print_o($invoiceInfo,'$invoiceInfo');

	return $ret;
}
?>