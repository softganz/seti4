<?php
define(_KAMSAIINDICATOR,'schooleat');

function view_school_summary_learn_form($orgid) {
	$form=new Form('learn',url('school/summary/learn/add/'.$orgid),'learn-add');

	$form->title='<h3>บันทึกผลสัมฤทธิ์ทางการเรียน</h3>';

	for ($i=2015;$i<=date('Y')+1;$i++) $yearOptions[$i]=$i+543;
	$form->year->type='radio';
	$form->year->label='ปีการศึกษา :';
	$form->year->require=true;
	$form->year->options=$yearOptions;
	$form->year->value=SG\getFirst($post->year,date('Y'));

	$form->termperiod->type='radio';
	$form->termperiod->label='ภาคการศึกษา :';
	$form->termperiod->require=true;
	$form->termperiod->options=array(
													'1'=>'ภาคการศึกษา 1',
													'2'=>'ภาคการศึกษา 2');
	$form->termperiod->value=SG\getFirst($post->termperiod,1);


	$form->postby->type='text';
	$form->postby->label='ผู้ประเมิน';
	$form->postby->require=true;
	$form->postby->value=htmlspecialchars($post->postby);

	$form->dateinput->type='text';
	$form->dateinput->label='วันที่ประเมิน';
	$form->dateinput->class='sg-datepicker';
	$form->dateinput->require=true;
	$form->dateinput->value=htmlspecialchars(sg_date(SG\getFirst($post->dateinput,date('Y-m-d')),'d/m/Y'));
	$tables->thead=array('ครั้งที่','ปีการศึกษา','ภาคการศึกษา','ช่วงเวลา','ผู้ประเมิน','วันที่ประเมิน','ผอม<br />(%)','ค่อนข้างผอม<br />(%)','สมส่วน<br />(%)','ท้วม<br />(%)','เริ่มอ้วน<br />(%)','อ้วน<br />(%)','');


	$tables=new table('item -weight');
	$tables->thead='<tr><th>ชั้น</th><th>ภาษาไทย</th><th>คณิตศาสตร์</th><th>วิทยาศาสตร์</th><th>สังคมฯ</th><th>สุขศึกษา</th><th>การงานฯ</th><th>ศิลปะ</th><th>ภาษาอังกฤษ</th><th>ประวัติศาสตร์</th><th>ภาษาไทยเพิ่มเติม</th><th>หน้าที่ฯ</th><th>รวม</th></tr>';
	$stmt='SELECT
					  qt.`question`
					, qt.`qtgroup`
					, qt.`qtno`
					, tr.`parent`
					, tr.`part`
					, tr.`sorder`
					, tr.`num1` total
					, tr.`num2` subject1
					, tr.`num3` subject2
					, tr.`num4` subject3
					, tr.`num5` subject4
					, tr.`num6` subject5
					, tr.`num7` subject6
					, tr.`num8` subject7
					, tr.`num9` subject8
					, tr.`num10` subject9
					, tr.`num11` subject10
					, tr.`num12` subject11
					, qt.`description`
				FROM %qt% qt
					LEFT JOIN %project_tr% tr
						ON tr.`tpid`=:tpid AND tr.`parent`=:trid AND tr.`formid`=:formid AND tr.`part`=:formid
							AND qt.`qtgroup`="schoolclass" AND tr.`sorder`=qt.`qtno`
				WHERE `qtgroup`="schoolclass"
				ORDER BY `qtgroup` ASC, `qtno` ASC';
	$qtResultDbs=mydb::select($stmt,':trid',$trid,':tpid',$tpid,':formid',_FORMID);
	//$ret.=print_o($qtResultDbs);

	$i=0;
	foreach ($qtResultDbs->items as $rs) {
		if ($rs->qtno<=20) continue;
		$i++;
		if (in_array($rs->qtno,array(11,21,31))) $tables->rows[]=array('<th colspan="13"><h3>'.rtrim(substr($rs->question,0,strpos($rs->question,' ')),'ปีที่').'</h3></th>');
		$tables->rows[]=array(
											$rs->question
											//.'<br />'.$stdKey.print_o($rs,'$rs')
											,
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject1]" value="'.number_format($rs->subject1,$percentDigit).'" />',
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject2]" value="'.number_format($rs->subject2,$percentDigit).'" />', 
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject3]" value="'.number_format($rs->subject3,$percentDigit).'" />',
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject4]" value="'.number_format($rs->subject4,$percentDigit).'" />',
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject5]" value="'.number_format($rs->subject5,$percentDigit).'" />',
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject6]" value="'.number_format($rs->subject6,$percentDigit).'" />',
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject7]" value="'.number_format($rs->subject7,$percentDigit).'" />',
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject8]" value="'.number_format($rs->subject8,$percentDigit).'" />',
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject9]" value="'.number_format($rs->subject9,$percentDigit).'" />',
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject10]" value="'.number_format($rs->subject10,$percentDigit).'" />',
											'<input class="form-text -numeric" type="text" size="3" name="qt['.$rs->qtno.'][subject11]" value="'.number_format($rs->subject11,$percentDigit).'" />',
											'<span id="">'.number_format($rs->total,$percentDigit).'</span>',
											);
		$subtotal+=$rs->answer;
	}
	$form->weight=$tables->build();


	$form->addField(
					'save',
					array(
						'type'=>'button',
						'value'=>'<i class="icon -save -white"></i><span>{tr:Save}</span>',
						)
					);


	$ret.=$form->build();

	//$ret.=print_o($qtResultDbs,'$qtResultDbs');
	$ret.='<style type="text/css">
	.item.-weight td:nth-child(n+2) {width:60px; text-align:center;}
	.item.-weight td:nth-child(13) {font-weight:bold;}
	.item.-weight input {margin:0 auto; display:block;}

	.item.-weight tr:nth-child(2n+1) td {background-color:#FFF18E;}
	.item h3 {padding-left:10px;text-align:left; background:#9400FF; color:#fff;}
	.item input {width:50px;}

	form>.form-item {margin: 7px 0; display: inline-block; border: 1px #ccc solid; padding: 0 4px 4px; vertical-align: top; border-radius:2px; whitespace: no-wrap;}
	form>#form-item-edit-weight-submit {display:block; border:none;}
	</style>';

	//$ret.=print_o($qtResultDbs,'$qtResultDbs');
	return $ret;
}
?>