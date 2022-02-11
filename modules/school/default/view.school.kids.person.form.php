<?php
function view_school_kids_person_form($orgid,$data) {
	$form=new Form('data',url('school/kids/person/add/'.$orgid));

	$form->addConfig('title','ข้อมูลส่วนบุคคลนักเรียนใหม่');

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
							)
						);
	$form->addField(
						'term',
						array(
							'type'=>'select',
							'label'=>'ภาคการศึกษา',
							'class'=>'-fill',
							)
						);
	$form->addField(
						'term',
						array(
							'type'=>'select',
							'label'=>'ชั้นปี',
							'options'=>array('ประถมศึกษา 1','ประถมศึกษา 2','ประถมศึกษา 3','ประถมศึกษา 4','ประถมศึกษา 5','ประถมศึกษา 6','มัธยมศึกษา 1','มัธยมศึกษา 2','มัธยมศึกษา 3','มัธยมศึกษา 4','มัธยมศึกษา 5','มัธยมศึกษา 6'),
							'class'=>'-fill',
							)
						);
	$form->addField(
						'cid',
						array(
							'type'=>'text',
							'label'=>'เลขประจำตัว 13 หลัก',
							'class'=>'-fill',
							)
						);

	$form->addField(
						'birth',
						array(
							'type'=>'text',
							'label'=>'วันเกิด',
							'class'=>'sg-datepicker -fill',
							)
						);

	$form->addField(
						'date',
						array(
							'type'=>'text',
							'label'=>'วันที่เข้าศึกษา',
							'class'=>'sg-datepicker -fill',
							)
						);
	$form->addField(
						'weight',
						array(
							'type'=>'text',
							'label'=>'น้ำหนัก (ก.ก.)',
							'class'=>'-fill',
							)
						);
		$form->addField(
						'height',
						array(
							'type'=>'text',
							'label'=>'ส่วนสูง (ซ.ม.)',
							'class'=>'-fill',
							)
						);

/*
วันเกิด
CID
ชั้น
ผู้ปกครอง
เบอร์โทร
*/

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