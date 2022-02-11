<?php
function view_school_kids_weight_form($orgid,$data) {
	$form=new Form('data',url('school/kids/weight/add/'.$orgid));

	$form->addConfig('title','บันทึกน้ำหนัก/ส่วนสูง : รายคน');

	$form->addField(
						'fullname',
						array(
							'type'=>'text',
							'label'=>'ชื่อ นามสกุล',
							'class'=>'-fill',
							)
						);
	$form->addField(
						'year',
						array(
							'type'=>'select',
							'label'=>'ปีการศึกษา',
							'class'=>'-fill',
							'options'=>array(date('Y')+543),
							)
						);
	$form->addField(
						'term',
						array(
							'type'=>'select',
							'label'=>'ภาคการศึกษา',
							'class'=>'-fill',
							'options'=>array(1),
							)
						);
	$form->addField(
						'by',
						array(
							'type'=>'text',
							'label'=>'ชื่อผู้เก็บข้อมูล',
							'class'=>'-fill',
							)
						);
	$form->addField(
						'date',
						array(
							'type'=>'text',
							'label'=>'วันที่เก็บข้อมูล',
							'class'=>'sg-datepicker -fill',
							'placeholder'=>'31/12/2017',
							)
						);

	$form->addField(
						'weight',
						array(
							'type'=>'text',
							'label'=>'น้ำหนัก (กก.)',
							'class'=>'-fill',
							'placeholder'=>'0.00',
							)
						);

		$form->addField(
						'height',
						array(
							'type'=>'text',
							'label'=>'ส่วนสูง (ซม.)',
							'class'=>'-fill',
							'placeholder'=>'0.00',
							)
						);
	$form->addField(
						'round',
						array(
							'type'=>'text',
							'label'=>'รอบเอว (ซม.)',
							'class'=>'-fill',
							'placeholder'=>'0.00',
							)
						);



	$form->addField(
				'save',
				array(
					'type'=>'button',
					'value'=>'<i class="icon -save -white"></i><span>{tr:Save}</span>',
					)
				);

	$ret.=$form->build();

	//$ret.=print_o($schoolInfo,'$schoolInfo');

	return $ret;
}
?>