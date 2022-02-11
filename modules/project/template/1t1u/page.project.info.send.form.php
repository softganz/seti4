<?php
/**
* Project :: Send Month Report Form
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $self
* @param Object $projectInfo
* @param Int $tranId
* @return String
*
* @usage project/{id}/info.send.form/{tranId}
*/

$debug = true;

function project_info_send_form($self, $projectInfo, $periodId = NULL) {
	// Data Model
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$isEdit = $projectInfo->right->isAdmin || $projectInfo->right->isOwner;

	if (!$isEdit) return message('error', 'ขออภัย ท่านไม่สามารถใช้งานเมนูนี้ได้');

	$periodInfo = R::Model('project.period.get', $projectId, $periodId);

	//debugMsg($periodInfo, '$periodInfo');

	// $data = NULL;
	// if ($periodId) {
	// 	$stmt = 'SELECT
	// 		  `trid`
	// 		, `tpid`
	// 		, `date1` `dateFrom`
	// 		, `date2` `dateEnd`
	// 		, `num1` `dateCount`
	// 		, `num2` `actionCount`
	// 		, `detail1` `actionList`
	// 		, `text1` `train`
	// 		, `text2` 	learn`
	// 		, `text3` `nextPlan`
	// 		FROM %project_tr%
	// 		WHERE `trid` = :trid
	// 		LIMIT 1';
	// 	$data = mydb::select($stmt, ':trid', $tranId);
	// } else {
	// 	$data->trid = $tranId;
	// 	$data->tpid = $projectId;
	// 	$stmt = 'SELECT * FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "info" AND `part` = "send" ORDER BY `trid` DESC LIMIT 1';
	// 	$lastReport = mydb::select($stmt, ':tpid', $projectId);
	// 	if ($lastReport->count()) {
	// 		$data->dateFrom = date('Y-m-01', strtotime($lastReport->date2.' +1 day'));
	// 		$data->dateEnd = date('Y-m-t', strtotime($lastReport->date2.' +1 day'));
	// 	} else {
	// 		$data->dateFrom = sg_date($projectInfo->info->date_from, 'Y-m-01');
	// 		$data->dateEnd = sg_date($projectInfo->info->date_from, 'Y-m-t');
	// 	}
	// 	//debugMsg($lastReport, '$lastReport');
	// 	//$data->reportMonth = post('month');
	// }

	$dateCount = array();

	//debugMsg($data, '$data');

	//$actionList = R::Model('project.action.get', $projectId);

	// View Model
	$ret = '';
	$toolbar = new Toolbar($self, 'ส่งรายงานประจำเดือน');

	$headerNav = new Ui();
	$headerNav->addConfig('container', '{tag: "nav", class: "nav"}');
	if ($tranId) {
		$headerNav->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info/send.delete/'.$tranId).'" data-rel="none" data-done="load | close" data-title="ลบรายงานประจำเดือน" data-confirm="ต้องการลบข้อมูลการส่งรายงานประจำเดือน กรุณายืนยัน?"><i class="icon -material">delete</i></a>');
	}
	$ret .= '<header class="header"><h3>ส่งรายงานประจำเดือน</h3>'.$headerNav->build().'</header>';

	$form = new Form('data', url('project/'.$projectId.'/info/send.save/'.$periodId), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('checkValid', true);
	$form->addData('done', 'close | load');

	$form->addConfig('title', 'แบบรายงานการปฏิบัติงานของผู้ถูกจ้างงาน');

	$form->addText('<p class="-sg-text-center">ประจำเดือน <b>'.sg_date($periodInfo->dateEnd.'','ดดด ปปปป').'</b></p>');
	$form->addText('<div class="form-item"><b>1. '.$projectInfo->title.'</b></div>');

	/*
	$tables = new Table();
	$tables->thead = '<tr><th rowspan="2">ลำดับ</th><th colspan="4">ผลการปฏิบัติงาน</th><th rowspan="2">หมายเหตุ</th></tr>'
		. '<tr><th>รายละเอียดการปฏิบัติงาน</th><th>ต่ำกว่าแผน</th><th>ตามแผน</th><th>สูงกว่าแผน</th></tr>';
	$tables->colgroup = array('no' => '');
	foreach ($actionList as $rs) {
		if ($rs->actionDate < $data->dateFrom OR $rs->actionDate > $data->dateEnd) continue;
		$dateCount[$rs->actionDate] = 1;

		$tables->rows[$rs->actionId] = array(
			++$no,
			'<b>'.$rs->title.'</b><br />@'.$rs->actionDate.'<br />'.nl2br($rs->actionReal),
			'',
			'',
			'',
			'',
		);
	}
	*/

	$form->addText('<div class="form-item"><b>2. รายงาน</b> ณ วันที่ '.sg_date($periodInfo->dateEnd, 'ว ดดด ปปปป').' ผ่านโปรแกรม 1T1U โดยมีผลการปฏิบัติงานดังแนบ</div>');
	/*
	$form->addText('<p><b>2. ผลการปฏิบัติงาน</b><br />'
		. 'จำนวนวันที่ปฎิบัติงาน <b>'.count($dateCount).'</b> วัน<br />'
		. 'จำนวนงานที่ได้รับมอบหมาย.................<br />'
		. 'จำนวนครั้งที่กิจกรรมที่ทำ <b>'.count($tables->rows).'</b> ครั้ง<br />'
		. '</p>');
	*/

	$form->addField(
		'ownerTraining',
		array(
			'type' => 'textarea',
			'label' => '3. การฝึกอบรมทักษะต่าง ๆ',
			'class' => '-fill',
			'rows' => 3,
			'value' => $periodInfo->ownerTraining,
			'placeholder' => 'ระบุรายละเอียดการฝึกอบรมทักษะต่าง ๆ',
		)
	);

	$form->addField(
		'ownerLearning',
		array(
			'type' => 'textarea',
			'label' => '4. สิ่งที่ได้เรียนรู้',
			'class' => '-fill',
			'require' => true,
			'rows' => 3,
			'value' => $periodInfo->ownerLearning,
			'placeholder' => 'ระบุรายละเอียดสิ่งที่ได้เรียนรู้',
		)
	);

	/*
	$form->addField(
		'ownerNextPlan',
		array(
			'type' => 'textarea',
			'label' => '5. แผนปฏิบัติงานต่อไป',
			'class' => '-fill',
			'require' => true,
			'rows' => 3,
			'value' => $periodInfo->ownerNextPlan,
			'placeholder' => 'ระบุรายละเอียด',
		)
	);
	*/
	/*
	$reportText = '<br />
		เดือน..........................<br />
		
		มหาวิทยาลัย ........................................................................
ตำบล ....................................................................................
รายงาน ณ วันที่ ............... เดือน ..................... พ.ศ. ..................
1. ชื่อ – สกุล ................................................................................
2. ผลการปฏิบัติงาน   
จำนวนวัน  ทีปฎิบัติงาน ....................... 
จำนวนงานที่ได้รับมอบหมาย.................
จำนวนครั้ง ที่กิจกรรมที่ทำ  ...................... 

ลำดับ
ผลการปฏิบัติงาน

รายละเอียดการปฏิบัติงาน
ต่ำกว่าแผน
ตามแผน
สูงกว่าแผน

3. การฝึกอบรมทักษะ ในด้านต่างๆ (ระบุรายละเอียด)
    • Digital Literacy,
    • English Competency, 
    • Financial Literacy
    • Social Literacy
..................................................................................................................................................................................................................................................................................................................................................................
4. สิ่งที่ได้เรียนรู้ เพิ่มเติม ในด้านต่างๆ (ระบุรายละเอียด)
    • Digital Literacy,
    • English Competency, 
    • Financial Literacy
    • Social Literacy
............................................................................................................................................................................................................................................................................................................................................................
5. แผนปฏิบัติงานต่อไป
............................................................................................................................................................................................................................................................................................................................................................ 
ขอรับรองว่าได้ปฏิบัติงานดังกล่าวข้างต้นในเดือน ....................... พ.ศ. ............ จริงทุกประการ

ลงชื่อ..................................................
 (.........................................................)
                 ผู้รับจ้าง
วันที่ ..................................................


ลงชื่อ..................................................
  (.........................................................)
    	    ผู้ควบคุมการปฏิบัติงาน 
วันที่ ..................................................

';
	*/

	$form->addText($reportText);


	// $form->addField(
	// 	'confirm',
	// 	array(
	// 		'type' => 'checkbox',
	// 		//'label' => 'ยืนยันการส่งรายงานประจำเดือน',
	// 		'class' => '-hide-label',
	// 		'require' => trye,
	// 		'options' => array('yes' => 'ยืนยันการส่งรายงานประจำเดือน'),
	// 	)
	// );

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>บันทึกรายงาน</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	//$ret .= print_o($actionList, '$actionList');
	//$ret .= print_o($projectInfo, '$projectInfo');
	return $ret;
}
?>