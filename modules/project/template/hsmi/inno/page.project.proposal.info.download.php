<?php
/**
 * Project Development Download
 * @param Object $self
 * @param Object $proposalInfo
 * @param String $action
 * @return String
 */

function project_proposal_info_download($self, $proposalInfo, $action = NULL) {
	//R::SetPage('__view');
	head('<meta name="robots" content="noindex,nofollow">');

	$tpid = $proposalInfo->tpid;
	$actionMode = SG\getFirst(post('mode'),$_SESSION['mode']);


	if (!$tpid) return message('error', 'PROCESS ERROR');

	//$ret .= print_o($proposalInfo, '$proposalInfo');

	if ($proposalInfo->info->topicStatus == _BLOCK
		&& !user_access('administer contents,administer papers')) {
		header('HTTP/1.0 404 Not Found');
		head('<meta name="robots" content="noarchive" />');
		return message('error','This topic was blocked.');
	}




	$ret .= '<header><h2>แบบเสนอโครงการ<br />'.$proposalInfo->title.'</h2></header>';

	$ret .= '<h3>1. ชื่อโครงการ</h3>';

	$ret .= 'ชื่อโครงการ : '.$proposalInfo->title.'<br />';


	$ret .= 'สถาบันอุดมศึกษาหลัก : '.$proposalInfo->info->orgName.'<br />';


	$ret .= 'ชื่อหน่วยงานหลัก : '.$proposalInfo->data['orgnamedo'].'<br />';

	$ret .= 'ชื่อหน่วยงานร่วม : '.$proposalInfo->data['project-coorg'].'<br />';

	$ret .= 'ชื่อชุมชน : '.$proposalInfo->data['commune'].'<br />';

	$ret .= 'ชื่อผู้รับผิดชอบ : '.$proposalInfo->data['prowner'].'<br />';

	$ret .= 'ที่อยู่ผู้รับผิดชอบ : '.$proposalInfo->data['owner-address'].'<br />';

	$ret .= 'การติดต่อ : '.$proposalInfo->data['prphone'].'<br />';





	$ret .= '<h3>2. พื้นที่ดำเนินงาน</h3>';


	$ret .= R::Page('project.proposal.info.area', NULL, $proposalInfo, $actionMode);



	$ret .= '<h3>3. รายละเอียดชุมชน</h3>';

	$ret .= '<p>ข้อมูลพื้นฐาน :<br />'.nl2br($proposalInfo->data['info-commune']).'</p>';
	$ret .= '<p>ข้อมูลศักยภาพ/ทรัพยากร :<br />'.nl2br($proposalInfo->data['info-potential']).'</p>';
	$ret .= '<p>ข้อมูลประเด็นปัญหา :<br />'.nl2br($proposalInfo->data['info-issue']).'</p>';
	$ret .= '<p>ข้อมูลความต้องการเชิงพื้นที่ :<br />'.nl2br($proposalInfo->data['info-need']).'</p>';

	// ประเด็นปัญหาหลัก

	$stmt = 'SELECT
		  tg.`catid`, tg.`catparent`, tg.`name`
		, tr.`bigid`, tr.`flddata`, tg.`process`
		FROM %tag% tg
			LEFT JOIN %bigdata% tr ON tr.`keyname` = "project.develop" AND tr.`keyid` = :tpid AND tr.`fldname` = CONCAT("mainissue-",tg.`catid`)
		WHERE tg.`taggroup` = "project:mainissue" AND tg.`process` = 1
		ORDER BY `catid` ASC';

	$mainIssueDbs=mydb::select($stmt,':tpid',$tpid);
	//$ret .= print_o($mainIssueDbs, '$mainIssueDbs');

	$ret .= '<h3>4. ประเด็นปัญหาหลัก</h3>';

	foreach ($mainIssueDbs->items as $rs) {
		$ret .= '<abbr class="checkbox -block"><label>'
			.view::inlineedit(
				array(
					'group'=>'bigdata:project.develop:'.$rs->catid,
					'fld' => 'mainissue-'.$rs->catid,
					'tr' => $rs->bigid,
					'value' => $rs->flddata,
					'removeempty'=>'yes',
				),
				$rs->catid.':'.$rs->name,
				$isInEditMode,
				'checkbox')
			.' </label></abbr><br />';
	}


	// ประเด็นที่เกี่ยวข้อง
	$ret .= '<h3>5. ประเด็นที่เกี่ยวข้อง</h3>';

	$stmt = 'SELECT
		  tg.`catid`, tg.`catparent`, tg.`name`
		, tr.`bigid`, tr.`flddata`, tg.`process`, tg.`weight`
		FROM %tag% tg
			LEFT JOIN %bigdata% tr ON tr.`keyname` = "project.develop" AND tr.`keyid` = :tpid AND tr.`fldname` = CONCAT("category-",tg.`catid`)
		WHERE tg.`taggroup` = "project:issue" AND tg.`process` = 2
		ORDER BY tg.`weight` ASC, tg.`catid` ASC';

	$issueDbs = mydb::select($stmt,':tpid',$tpid);
	//$ret .= print_o($issueDbs, '$issueDbs');


	foreach ($issueDbs->items as $rs) {
		$ret .='<abbr class="checkbox -block"><label>'
			.view::inlineedit(
				array(
					'group'=>'bigdata:project.develop:'.$rs->catid,
					'fld' => 'category-'.$rs->catid,
					'tr' => $rs->bigid,
					'value'=>$rs->flddata,
					'removeempty'=>'yes',
				),
				$rs->catid.':'.$rs->name,
				$isInEditMode,
				'checkbox')
			.' </label></abbr><br />';
	}

	$ret .= ($isInEditMode ? implode('', $optionsIssue) : implode(' , ', $optionsIssue));




	$ret .= '<h3>6. องค์ความรู้หรือนวัตกรรมที่ใช้ในการดำเนินโครงงาน</h3>';


	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::knowledge',
			'fld' => 'knowledge',
			'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุข้อมูลองค์ความรู้หรือนวัตกรรมที่ใช้ในการดำเนินโครงงาน"}',
			'value'=>trim($proposalInfo->data['knowledge']),
		),
		nl2br($proposalInfo->data['knowledge']),
		$isInEditMode,
		'textarea'
	);





	$ret .= '<h3>7. วัตถุประสงค์</h3>';

	$ret .= R::Page('project.proposal.info.objective', NULL, $proposalInfo, $actionMode);


	$ret .= '<h3>8. กลุ่มเป้าหมาย</h3>';

	$ret .= R::Page('project.proposal.info.target', NULL, $proposalInfo, $actionMode);



	$ret .= '<h3>9. ระยะเวลา</h3>';

	$ret .= 'ตั้งแต่ '.( $proposalInfo->info->date_from ? sg_date($proposalInfo->info->date_from,'d/m/Y') : '')
		. ' - '.($proposalInfo->info->date_end ? sg_date($proposalInfo->info->date_end, 'ว ดดด ปปปป') : '')
		. '<br />';



	$ret .= '<h3>10. วิธีดำเนินงาน</h3>';

	$ret .= R::Page('project.proposal.info.plan.single', NULL, $proposalInfo, $actionMode);


	$ret .= '<h3>11. งบประมาณ</h3>';

	$ret .= view::inlineedit(
		array(
			'group' => 'dev',
			'fld' => 'budget',
			'class' => '-money',
			'label' => 'งบประมาณ',
			'posttext' => 'บาท'
		),
		$proposalInfo->info->budget,
		$isInEditMode
	);



	$ret .= '<h3>12. การติดตามประเมินผล</h3>';

	$tables = new Table();
	$tables->thead = array('','ต่อชุมชน','ต่อนักศึกษา');
	$tables->colgroup = array('a -nowrap' => 'width="10%"', '2' => 'width="40%', '3' => 'width="40%"');
	$tables->rows[] = array(
		'ผลผลิต (Output)',
		view::inlineedit(
			array(
				'group' => 'bigdata::output-commune',
				'fld' => 'output-commune',
				'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุผลผลิตต่อชุมชน"}',
				'value'=>trim($proposalInfo->data['output-commune']),
			),
			nl2br($proposalInfo->data['output-commune']),
			$isInEditMode,
			'textarea'
		),
		view::inlineedit(
			array(
				'group' => 'bigdata::output-student',
				'fld' => 'output-student',
				'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุผลผลิตต่อนักศึกษา"}',
				'value'=>trim($proposalInfo->data['output-student']),
			),
			nl2br($proposalInfo->data['output-student']),
			$isInEditMode,
			'textarea'
		),
	);
	$tables->rows[] = array(
		'ผลลัพธ์ (Outcome)',
		view::inlineedit(
			array(
				'group' => 'bigdata::outcome-commune',
				'fld' => 'outcome-commune',
				'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุผลลัพธ์ต่อชุมชน"}',
				'value'=>trim($proposalInfo->data['outcome-commune']),
			),
			nl2br($proposalInfo->data['outcome-commune']),
			$isInEditMode,
			'textarea'
		),
		view::inlineedit(
			array(
				'group' => 'bigdata::outcome-student',
				'fld' => 'outcome-student',
				'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุผลลัพธ์ต่อนักศึกษา"}',
				'value'=>trim($proposalInfo->data['outcome-student']),
			),
			nl2br($proposalInfo->data['outcome-student']),
			$isInEditMode,
			'textarea'
		),
	);
	$tables->rows[] = array(
		'ผลกระทบ (Impact)',
		view::inlineedit(
			array(
				'group' => 'bigdata::impact-commune',
				'fld' => 'impact-commune',
				'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุผลกระทบต่อชุมชน"}',
				'value'=>trim($proposalInfo->data['impact-commune']),
			),
			nl2br($proposalInfo->data['impact-commune']),
			$isInEditMode,
			'textarea'
		),
		view::inlineedit(
			array(
				'group' => 'bigdata::impact-student',
				'fld' => 'impact-student',
				'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุผลกระทบต่อนักศึกษา"}',
				'value'=>trim($proposalInfo->data['impact-student']),
			),
			nl2br($proposalInfo->data['impact-student']),
			$isInEditMode,
			'textarea'
		),
	);

	$ret .= $tables->build();



	$ret = preg_replace('/<!--(.|\s)*?-->/', '', $ret);
	$ret = preg_replace('/(?i)<a([^>]+)>(.+?)<\/a>/','',$ret);

	sendheader('application/octet-stream');
	header('Content-Disposition: attachment; filename="'.$proposalInfo->title.'.doc"');

	$ret='<HTML>
	<HEAD>
	<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=utf-8">
	<TITLE>'.$info->title.'</TITLE>
	</HEAD>
	<BODY>
	'.$ret.'
	</BODY>
	</HTML>';
	cfg('Content-Type','text/xml');
	return $ret;
}
?>