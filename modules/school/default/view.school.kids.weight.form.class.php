<?php
function view_school_kids_weight_form_class($orgid,$data) {
	$form=new Form('data',url('school/kids/weight/add/'.$orgid));

	$form->addConfig('title','บันทึกน้ำหนัก/ส่วนสูง : รายห้องเรียน');

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
							'options'=>array(1,2),
							)
						);
	$form->addField(
						'class',
						array(
							'type'=>'select',
							'label'=>'ชั้นเรียน',
							'class'=>'-fill',
							'options'=>array('ประถมศึกษา 1','ประถมศึกษา 2','ประถมศึกษา 3','ประถมศึกษา 4','ประถมศึกษา 5','ประถมศึกษา 6','มัธยมศึกษา 1','มัธยมศึกษา 2','มัธยมศึกษา 3','มัธยมศึกษา 4','มัธยมศึกษา 5','มัธยมศึกษา 6'),
							)
						);
	$form->addField(
						'room',
						array(
							'type'=>'text',
							'label'=>'ห้องเรียน',
							'class'=>'-fill',
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

	$kids=array('สมชาย','สมศรี','สมโชค','สมบูรณ์');

	$no=0;
	$tables = new Table();
	$tables->thead=array('no'=>'','ชื่อ นามสกุล นักเรียน','date -birth'=>'ว.ด.ป.เกิด','amt -age'=>'อายุ(ป/ด)','น้ำหนัก(กก.)','ส่วนสูง(ซม.)','น/อ','ส/อ','น/ส','รอบเอว(ซม.)');
	foreach ($kids as $rs) {
		$tables->rows[]=array(
							++$no,
							$rs,
							'10-ต.ค.52',
							12,
							'<input class="form-text" type="text" size="4" placeholder="0.00" />',
							'<input class="form-text" type="text" size="4" placeholder="0.00" />',
							'','','',
							'<input class="form-text" type="text" size="4" placeholder="0.00" />',
							);
	}
	$form->table=$tables->build();





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