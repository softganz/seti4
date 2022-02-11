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

function garage_recieve_form_head($self, $rcvInfo = NULL) {
	if (!($rcvId = $rcvInfo->rcvid)) return message('error', 'PROCESS ERROR:NO RECIEVE');

	$shopInfo = $rcvInfo->shopInfo;


	new Toolbar($self,'ใบเสร็จรับเงิน'.($rcvInfo?' - '.$rcvInfo->rcvno:''),'finance',$rcvInfo);

	if (empty($rcvId)) return '<p class="notify">ไม่มีรายการที่ระบุ</p>';

	//$ret .= print_o($rcvInfo, '$rcvInfo');


	$ret .= __garage_recieve_view_form($shopInfo,$rcvInfo);
			return $ret;

	//$ret.=print_o($rcvInfo,'$rcvInfo');

	return $ret;
}

function __garage_recieve_view_form($shopInfo,$data) {
	$ret = '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -back"></i></a></nav><h3 class="title">แก้ไขใบเสร็จรับเงิน</h3></header>';

	$form = new Form('data',url('garage/info/'.$data->rcvid.'/recieve.save'), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load');

	$form->addField('rcvdate',array('label'=>'วันที่','type'=>'text','class'=>'-fill sg-datepicker','value'=>sg_date($data->rcvdate,'d/m/Y')));
	$form->addField('rcvcustname',array('label'=>'นามผู้ซื้อ','type'=>'text','class'=>'-fill','value'=>$data->rcvcustname));
	$form->addField('rcvaddr',array('label'=>'ที่อยู่','type'=>'textarea','class'=>'-fill','value'=>$data->rcvaddr,'rows'=>2));
	$form->addField('rcvphone',array('label'=>'โทรศัพท์','type'=>'text','class'=>'-fill','value'=>$data->rcvphone));
	$form->addField('rcvtaxid',array('label'=>'เลขประจำตัวผู้เสียภาษี','type'=>'text','class'=>'-fill','value'=>$data->rcvtaxid,'maxlength'=>13));
	$form->addField('rcvbranch',array('label'=>'สาขาลำดับที่','type'=>'text','class'=>'-fill','value'=>$data->rcvbranch));

	$form->addField(
		'vatrate',
		array(
			'label'=>'อัตราภาษีร้อยละ',
			'type'=>'text',
			'class'=>'-money -fill',
			'value'=>$data->vatrate,
			'posttext' => '<div class="input-append"><span>%</span></div>',
			'container' => '{class: "-group -sg-flex"}',
		)
	);


	$form->addField(
		'subtotal',
		array(
			'label'=>'รวมเงิน',
			'type'=>'text',
			'class'=>'-money -fill',
			'value'=>$data->subtotal,
			'posttext' => '<div class="input-append"><span>บาท</span></div>',
			'container' => '{class: "-group -sg-flex"}',
		)
	);
	$form->addField(
		'vattotal',
		array(
			'label'=>'ภาษีมูลค่าเพิ่ม',
			'type'=>'text',
			'class'=>'-money -fill',
			'value'=>$data->vattotal,
			'posttext' => '<div class="input-append"><span>บาท</span></div>',
			'container' => '{class: "-group -sg-flex"}',
		)
	);
	$form->addField(
		'total',
		array(
			'label'=>'รวมเงินทั้งสิ้น',
			'type'=>'text',
			'class'=>'-money -fill',
			'value'=>number_format($data->total,2),
			'readonly' => true,
			'posttext' => '<div class="input-append"><span>บาท</span></div>',
			'container' => '{class: "-group -sg-flex"}',
		)
	);

	$form->addField('rcvremark',array('label'=>'หมายเหตุ','type'=>'textarea','class'=>'-fill','value'=>$data->rcvremark,'rows'=>3));
	$form->addField('showno',array('type'=>'checkbox','value'=>$data->showno,'options'=>array('1'=>'แสดงลำดับที่')));
	$form->addField('showsingle',array('type'=>'checkbox','value'=>$data->showsingle,'options'=>array('1'=>'รวมรายการ')));
	$form->addField('showinsuno',array('type'=>'checkbox','value'=>$data->showinsuno,'options'=>array('1'=>'แสดงเลขกรมธรรม์')));

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
			'pretext' => '<a class="btn -link -cancel" href="'.url('garage/recieve/'.$data->rcvid).'"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
			'container' => array('class'=>'-sg-text-right'),
		)
	);

	$ret.=$form->build();

	$ret .= '<p>หมายเหตุ : การแก้ไขข้อมูลบริษัทประกันจะไม่มีผลต่อข้อมูลในใบเสร็จรับเงินที่สร้างไว้แล้ว</p>';

	//$ret.=print_o($data,'$data');

	return $ret;
}
?>