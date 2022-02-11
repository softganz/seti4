<?php
/**
* Project : Local Fund Population Form
* Created 2020-04-10
* Modify  2020-04-10
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage project/fund/$orgId/population.form.y63
*/

$debug = true;

function project_fund_population_form_y63($self, $fundInfo, $data) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$isAddPopulation = $fundInfo->right->addPopulation;

	if (!$isAddPopulation) return 'ERROR: ACCESS DENIED';

	$getYear = SG\getFirst($data->year,date('Y')+1);

	//$ret .= print_o($fundInfo,'$fundInfo');
	$ret .= print_o($data,'$data');

	// Check this year is already input
	if (!$data->trid) {
		$stmt = 'SELECT
			  `trid`,`date1` `recordyear`
			, `num1` `balance`
			, `num2` `population`
			, `num3` `budgetnhso`
			, `num4` `budgetlocal`
			, `detail1` `haveplan`
			, `detail2` `byname`
			, `detail3` `byposition`
			FROM %project_tr%
			WHERE `formid`="population" AND `part`=:fundid AND YEAR(`date1`)=:year
			LIMIT 1';

		$isYearInput=mydb::select($stmt,':fundid',$fundid,':year',$getYear);

		//$ret.=print_o($isYearInput,'$isYearInput',1);
		//echo 'project/fund/population/'.$fundid.'/edit/'.$isYearInput->trid;
		if ($isYearInput->_num_rows) location('project/fund/'.$orgId.'/population/edit/'.$isYearInput->trid);
	}

	if ($fundid && post('save')) {
		$data->uid=i()->uid;
		$data->balance=sg_strip_money($data->balance);
		$data->population=sg_strip_money($data->population);
		$data->budgetnhso=sg_strip_money($data->budgetnhso);
		$data->budgetlocal=sg_strip_money($data->budgetlocal);
		$data->created=date('U');
		$data->modified=date('U');
		$data->modifyby=i()->uid;

		$stmt='UPDATE %project_fund%
			SET
				  `orgsize`=:orgsize
				, `openyear`=:openyear
				, `population`=:population
				, `estimatenhso`=:budgetnhso
				, `estimatelocal`=:budgetlocal
				, `orgemail`=:orgemail
				, `orgphone`=:orgphone
			WHERE `fundid`=:fundid
			LIMIT 1';

		//mydb()->simulate=true;
		mydb::query($stmt,':fundid',$fundid,$data);
		//$ret.=mydb()->_query.'<br />';

		if (empty($fundInfo->info->openbalance)) {
			$stmt='UPDATE %project_fund%
				SET `openbalance`=:balance
				WHERE `fundid`=:fundid
				LIMIT 1';
			mydb::query($stmt,':fundid',$fundid,$data);
			//$ret.=mydb()->_query.'<br />';
		}

		$data->recordyear=$data->year.'-07-01';
		if (empty($data->haveplan)) $data->haveplan=-1;
		$stmt='INSERT INTO %project_tr%
			(
			  `trid`,`uid`,`formid`,`part`,`date1`
			, `num1`,`num2`,`num3`,`num4`
			, `detail1`,`detail2`,`detail3`
			, `text1`,`text2`
			, `created`
			) VALUES (
			  :trid,:uid,"population",:fundid,:recordyear
			, :balance,:population,:budgetnhso,:budgetlocal
			, :haveplan,:byname,:byposition
			, :orgemail,:orgphone
			, :created
			)
			ON DUPLICATE KEY UPDATE
			  `date1`=:recordyear
			, `num1`=:balance
			, `num2`=:population
			, `num3`=:budgetnhso
			, `num4`=:budgetlocal
			, `detail1`=:haveplan
			, `detail2`=:byname
			, `detail3`=:byposition
			, `text1`=:orgemail
			, `text2`=:orgphone
			, `modified`=:modified
			, `modifyby`=:modifyby
			';

		mydb::query($stmt,':fundid',$fundid,$data);
		//$ret.=mydb()->_query.'<br />';

		//return $ret;
		location('project/fund/'.$orgId);

	}





	if (!$data->orgsize) $data->orgsize=$fundInfo->info->orgsize;
	if (!$data->openyear) $data->openyear=$fundInfo->info->openyear;
	if (!$data->orgemail) $data->orgemail=$fundInfo->info->orgemail;
	if (!$data->orgphone) $data->orgphone=$fundInfo->info->orgphone;

	$ret.='<h3>แบบฟอร์มกรอกข้อมูลเพื่อจัดทำข้อมูลประชากรการจัดสรรงบประมาณกองทุนหลักประกันสุขภาพระดับท้องถิ่น<br />ประจำปีงบประมาณ '.($getYear+543).($data->trid?'<br />[แก้ไขข้อมูลประจำปีงบประมาณ '.($getYear+543).']<br />':'').'</h3>';

	//$ret.=print_o($fundInfo,'$fundInfo');
	$form=new Form('data',url('project/fund/'.$orgId.'/population.save'),'project-population-edit',debug('form')?'':'sg-form');
	$form->addData('checkValid',true);

	$form->addField('trid',array('type'=>'hidden','value'=>$data->trid));
	$form->addField('year',array('type'=>'hidden','value'=>$getYear));

	$form->addField('h1','<h4>ส่วนที่ 1 : ข้อมูลกองทุน</h4>');

	$form->addField('fundname','<strong><big>1.1 ชื่อกองทุนหลักประกันสุขภาพระดับท้องถิ่น อบต./เทศบาล <em>'.$fundInfo->name.'</em> อำเภอ <em>'.$fundInfo->info->nameampur.'</em> จังหวัด <em>'.$fundInfo->info->namechangwat.'</em></big></strong>');

	$form->addField(
		'orgsize',
		array(
			'type'=>'radio',
			'label'=>'1.2 ขนาดขององค์กรปกครองส่วนท้องถิ่น:',
			'require'=>true,
			'options'=>array(
								'เทศบาล:'=>array('6'=>'เทศบาลนคร','5'=>'เทศบาลเมือง','4'=>'เทศบาลตำบล'),
								'องค์การบริหารส่วนตำบล:'=>array('3'=>'อบต.ขนาดใหญ่','2'=>'อบต.ขนาดกลาง','1'=>'อบต.ขนาดเล็ก'),
								),
			'value'=>$data->orgsize,
		)
	);

	$options=array();
	$options[-1]='== เลือกปีงบประมาณ ==';
	for ($i=2006; $i <= date('Y'); $i++) $options[$i]='พ.ศ. '.($i+543);
	$form->addField(
		'openyear',
		array(
			'type'=>'select',
			'label'=>'1.3 จัดตั้งกองทุนเมื่อปีงบประมาณ:',
			'require'=>true,
			'options'=>$options,
			'value'=>$data->openyear,
		)
	);

	$form->addField('h2','<h4>ส่วนที่ 2 : การเงิน</h4>');

	$form->addField(
		'balance',
		array(
			'type'=>'hidden',
			'label'=>'2.1 ปัจจุบันกองทุนมีเงินเหลืออยู่จำนวนทั้งสิ้น',
			'posttext'=>' บาท',
			'require'=>false,
			'description'=>'(นำสมุดธนาคารของกองทุนฯไปปรับให้เป็นปัจจุบันก่อนกรอกข้อมูลข้อนี้)',
			'value'=>$data->balance
		)
	);

	$form->addField(
		'population',
		array(
			'type'=>'text',
			'label'=>'2.2 ข้อมูลประชากรตามทะเบียนราษฎร์ ณ วันที่ 1 เมษายน '.($getYear-1+543).' จำนวนทั้งสิ้น',
			'posttext'=>' คน',
			'require'=>true,
			'description'=>'(เอกสารแนบ....ที่มาของยอดประชากร)',
			'value'=>$data->population,
		)
	);

	$form->addField(
		'budgetnhso',
		array(
			'type'=>'text',
			'label'=>'2.3 ประมาณการการได้รับเงินจาก สปสช.ปี '.($getYear+543).' จำนวน',
			'posttext'=>' บาท',
			'require'=>true,
			'description'=>'(ข้อมูลประชากรตามข้อ 2.2 X 45 บาท)',
			'value'=>$data->budgetnhso,
		)
	);

	$form->addField(
		'budgetlocal',
		array(
			'type'=>'text',
			'label'=>'2.4 จำนวนเงินสมทบที่ตั้งไว้ในข้อบัญญัติงบประมาณของ อบต./เทศบาล ประจำปี '.($getYear+543).' จำนวน',
			'posttext'=>' บาท',
			'require'=>true,
			'value'=>$data->budgetlocal,
		)
	);

	$form->addField('h3','<h4>ส่วนที่ 3 : การจัดทำแผนปฏิบัติการกองทุนฯ</h4>');

	$form->addField(
		'haveplan',
		array(
			'type'=>'radio',
			'label'=>'3.1 การจัดทำแผนปฏิบัติการกองทุนฯปี '.($getYear+543).':',
			'require'=>true,
			'options'=>array(-1=>'ยังไม่ได้จัดทำแผนปฏิบัติการ',1=>'อยู่ระหว่างการจัดทำแผนปฏิบัติการ',2=>'จัดทำแผนปฏิบัติการเรียบร้อยแล้ว'),
			'value'=>$data->haveplan,
		)
	);

	$form->addField('h4','<h4>ส่วนที่ 4 : รายละเอียดผู้ป้อนข้อมูล</h4>');

	$form->addField(
		'byname',
		array(
			'type'=>'text',
			'label'=>'ผู้กรอกข้อมูล',
			'class'=>'-fill',
			'require'=>true,
			'value'=>$data->byname,
		)
	);

	$form->addField(
		'byposition',
		array(
			'type'=>'text',
			'label'=>'ตำแหน่ง',
			'class'=>'-fill',
			'require'=>true,
			'value'=>$data->byposition,
		)
	);

	$form->addField(
		'orgemail',
		array(
			'type'=>'text',
			'label'=>'อีเมล์ที่ติดต่อได้',
			'class'=>'-fill',
			'value'=>$data->orgemail,
			'description'=>'อีเมล์ที่ติดต่อได้จะเปิดเผยต่อสาธารณะ กรุณาใช้อีเมล์สำนักงาน',
		)
	);

	$form->addField(
		'orgphone',
		array(
			'type'=>'text',
			'label'=>'เบอร์โทรศัพท์ที่ติดต่อได้',
			'class'=>'-fill',
			'value'=>$data->orgphone,
			'description'=>'เบอร์โทรศัพท์ที่ติดต่อได้จะเปิดเผยต่อสาธารณะ กรุณาใช้เบอร์โทรสำนักงาน',
		)
	);

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'name'=>'save',
			'value'=>'<i class="icon -save -white"></i><span>บันทึกข้อมูลประชากร</span>',
			'posttext'=>'<a href="'.url('project/fund/'.$orgId).'">ยกเลิก</a>',
		)
	);

	$ret.=$form->build();

	//$ret.=print_o($data,'$data');

	$ret.='<p>หมายเหตุ  : โปรดกรอกข้อมูลให้ครบทุกข้อ</p>';

	$ret.='<style type="text/css">
	.__main h3 {padding:16px; margin:0 0 32px 0; background:#ccc; text-align:center; line-height:1.6em;}
	#project-population-edit h4 {padding:8px; margin:0 0 10px 0; background:#ccc;}
	#project-population-edit .form-item {margin: 0 0 40px 0;}
	#project-population-edit .form-select {width:210px;}
	#project-population-edit #form-item-edit-data-byname {margin-bottom:0;}
	</style>';
	return $ret;
}
?>