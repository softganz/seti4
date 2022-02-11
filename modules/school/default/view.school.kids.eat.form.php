<?php
function view_school_kids_eat_form($orgid,$data) {
	$form=new Form('data',url('school/kids/eat/add/'.$orgid));

	$form->addConfig('title','บันทึกแบบสอบถามการกินอาหารและการออกกำลังกายของนักเรียน');

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
							)
						);





	$stmt='SELECT
					  qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, tr.`parent`
					, tr.`part`
					, tr.`sorder`
					, tr.`num1` total
					, tr.`num5` bad
					, tr.`num6` fair
					, tr.`num7` good
					, qt.`description`
				FROM %qt% qt
					LEFT JOIN %project_tr% tr
						ON tr.`tpid`=:tpid AND tr.`parent`=:trid AND tr.`formid`=:formid AND tr.`part`=:formid
							AND tr.`part`=qt.`qtgroup` AND tr.`sorder`=qt.`qtno`
				WHERE `qtgroup`=:formid
				ORDER BY `qtgroup` ASC, `qtno` ASC';
	$qtResultDbs=mydb::select($stmt,':trid',$trid,':tpid',$tpid,':formid','schooleat');

	$tables=new table('item -std3');
	if ($at=='บ้าน') {
		$tables->thead=array('no'=>'','พฤติกรรมการกินและการออกกำลังกายที่บ้าน','amt total'=>'จำนวนนักเรียน<br />(คน)','amt bad'=>'ทำได้น้อย<br />(0-2 วันต่อสัปดาห์)<br />(คน)','amt fair'=>'ทำได้ปานกลาง<br />(3-5 วันต่อสัปดาห์)<br />(คน)','amt good'=>'ทำได้ดี<br />(6-7 วันต่อสัปดาห์)<br />(คน)');
	} else {
		$tables->thead=array('no'=>'','พฤติกรรมการกินและการออกกำลังกายที่โรงเรียน (เฉพาะมื้อกลางวัน)','amt total'=>'จำนวนนักเรียน<br />(คน)','amt bad'=>'ทำได้น้อย<br />(0-1 วันต่อสัปดาห์)<br />(คน)','amt fair'=>'ทำได้ปานกลาง<br />(2-3 วันต่อสัปดาห์)<br />(คน)','amt good'=>'ทำได้ดี<br />(4-5 วันต่อสัปดาห์)<br />(คน)');
	}

	$tables->rows[]='<tr><td colspan="8"><h4>'.$stdName.'</h4></td></tr>';
	foreach ($qtResultDbs->items as $rs) {
		$radioName='qt['.$stdKey.']['.$rs->qtno.'][2]';
		$tables->rows[]=array(
											$rs->qtno,
											$rs->question
											//.'<br />'.$stdKey.print_o($rs,'$rs')
											,
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][total]" value="'.number_format($rs->total,0,'.','').'" />',
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][bad]" value="'.number_format($rs->bad,0,'.','').'" />',
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][fair]" value="'.number_format($rs->fair,0,'.','').'" />',
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][good]" value="'.number_format($rs->good,0,'.','').'" />',
											);
		$subtotal+=$rs->answer;
	}
	$form->std3Table=$tables->build();


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