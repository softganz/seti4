<?php
/**
* Project :: Show Action Report Of Period
* Created 2021-04-17
* Modify  2021-04-17
*
* @param Object $self
* @param Object $projectInfo
* @param Int $periodId
* @return String
*
* @usage project/{id}/operate.action.period/{periodId}
*/

$debug = true;

function project_operate_action_period($self, $projectInfo, $periodId) {
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	// Data Model
	$periodInfo = R::Model('project.period.get', $projectId, $periodId);
	$actionList = R::Model('project.action.get', ['projectId' => $projectId, 'period' => $periodId], '{includePhoto: false}');

	$isAdmin = is_admin();
	$isEdit = $projectInfo->right->isAdmin || $projectInfo->right->isOwner;


	// View Model
	$toolbar = new Toolbar($self, $projectInfo->title);

	$ret = '';

	$editBtn = '';
	if ($isEdit) $editBtn = '<nav class="nav -page -sg-text-right -no-print"><a class="sg-action btn -primary" href="'.url('project/'.$projectId.'/info.send.form/'.$periodId).'" data-rel="box" data-width="480"><i class="icon -material">edit</i><span>เขียนบันทึกเพิ่มเติม</span></a></nav>';

	$ret .= '<div class="-sg-text-center">'
		. '<h3>รายงานปฏิบัติงานของนวัตกรชุมชน '.$projectInfo->info->orgName.'</h3>'
		. 'ประจำตำบล '.$projectInfo->info->parentTitle.' เดือน '.sg_date($periodInfo->dateEnd, 'ดดด ปปปป').'<br />โครงการยกระดับเศรษฐกิจและสังคมรายตำบลแบบบูรณาการ'
		. '<hr />'
		. '</div>';

	$ret .= '<div><b>1. '.$projectInfo->title.'</b></div>';
	$ret .= '<div><b>2. รายงาน</b> ณ วันที่ '.sg_date($periodInfo->dateEnd, 'ว ดดด ปปปป')
		. ' ผ่านโปรแกรม 1T1U โดยมีผลการปฏิบัติงานดังแนบ</div>';

	$tables = new Table();
	$tables->thead = '<tr><th colspan="2">'.$projectInfo->title.'</th></tr>';
	//['date -date' => 'วันที่', 'กิจกรรม'];

	foreach ($actionList as $actionInfo) {
		$tables->rows[] = [
			sg_date($actionInfo->actionDate, 'ว ดด ปปปป'),
			$actionInfo->title,
		];
	}

	$ret .= $tables->build();

	$ret .= '<div><p>ทั้งนี้ รายงานดังกล่าวเป็นไปตามแผนการปฏิบัติงานประจำเดือน '.sg_date($periodInfo->dateEnd, 'ดดด ปปปป').' และได้ผ่านการรับรองโดยผู้ควบคุมการปฏิบัติงานผ่านระบบ 1T1U เรียบร้อยแล้ว</p></div>';

	$ret .= '<div><b>3. การฝึกอบรมทักษะต่าง ๆ</b><br />'.nl2br($periodInfo->ownerTraining).'</div>';

	$ret .= '<div><b>4. สิ่งที่ได้เรียนรู้</b><br />'.($editBtn.nl2br($periodInfo->ownerLearning)).'</div>';

	$ret .= '<div><b>5. แผนปฏิบัติงานต่อไป</b><br />- เป็นไปตามที่ผู้ควบคุมการปฏิบัติงานมอบหมายให้ในเดือน '.sg_date(strtotime($periodInfo->dateEnd.' +1 month'), 'ดดด ปปปป').' ผ่านระบบ 1T1U</div>';

	$ret .= '<div class="-billsign -sg-flex">'
		. '<p>ขอรับรองว่าได้ปฏิบัติงานดังกล่าวข้างต้นในเดือน '.sg_date($periodInfo->dateEnd, 'ดดด ปปปป').' จริงทุกประการ</p>'
		. '<div>ลงชื่อ<br /><br />.....................................'
		//. ($isEdit ? '<a class="-no-print" href=""><i class="icon -material">add_a_photo</i></a>' : '')
		. '<br />( '.$projectInfo->title.' )<br />ผู้ถูกจ้างงาน<br />'.sg_date($periodInfo->dateEnd, 'ว ดดด ปปปป').'</div>'
		. '<div>ลงชื่อ<br /><br />.....................................<br />(...........................................)<br />ผู้ควบคุมการปฏิบัติงาน<br />'.sg_date($periodInfo->dateEnd, 'ว ดดด ปปปป').'</div>'
		. '</div>';
		;

	$ret .= '<style type="text/css">
	.item>thead>tr>th {text-align: left;}
	.item>tbody>tr>td:first-child {white-space: nowrap;}
	.page.-main>div {padding: 8px 0;}
	.page.-main>div>p {text-indent: 0.2in;}
	.-billsign>p {flex: 1 0 100%;}
	.-billsign>div {margin-top: 32px; text-align: center;}
	@media print {
		.sg-toolbar.-main {display: none;}
	}
	</style>';

	//$ret .= print_o($actionList, '$actionList');
	//$ret .= print_o($periodInfo, '$periodInfo');
	//$ret .= print_o($projectInfo, '$projectInfo');

	return $ret;
}
?>