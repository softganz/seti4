<?php
/**
 * Project Development View
 * @param Object $self
 * @param Object $proposalInfo
 * @param String $action
 * @return String
 */

import('widget:project.like.status.php');

function project_proposal_info_view($self, $proposalInfo, $action = NULL) {
	//R::SetPage('__view');
	head('<meta name="robots" content="noindex,nofollow">');

	$tpid = $proposalInfo->tpid;
	$actionMode = SG\getFirst(post('mode'),$_SESSION['mode']);

	//debugMsg('$action='.$action);
	//debugMsg($_SESSION,'$_SESSION');
	unset($_SESSION['mode']);

	if (!$tpid) return message('error', 'PROCESS ERROR');

	//$ret .= print_o($proposalInfo, '$proposalInfo');

	if ($proposalInfo->info->topicStatus == _BLOCK
		&& !user_access('administer contents,administer papers')) {
		header('HTTP/1.0 404 Not Found');
		head('<meta name="robots" content="noarchive" />');
		return message('error','This topic was blocked.');
	}

	//R::On('project.proposal.change',$tpid,'update',$ret);

	R::View('project.toolbar', $self, $proposalInfo->title, 'proposal', $proposalInfo);

	head('js.project.proposal.js','<script type="text/javascript" src="project/js.project.proposal.js"></script>');


	$isAdmin = $proposalInfo->RIGHT & _IS_ADMIN;
	$isTrainer = $proposalInfo->RIGHT & _IS_TRAINER;
	$isEditable = $isAdmin || $proposalInfo->RIGHT & _IS_EDITABLE;
	$isFullView = $proposalInfo->RIGHT & _IS_RIGHT;
	$isInEditMode = $actionMode === 'edit' && $isEditable;

	$is_comment_sss = user_access('comment project');
	$is_comment_hsmi = user_access('administer papers,administer projects') || $isTrainer;

	R::Model('reaction.add', $tpid, 'TOPIC.VIEW');

	$ret .= (new ScrollView([
		'child' => new ProjectLikeStatusWidget([
			'action' => 'PDEV',
			'projectInfo' => $proposalInfo,
		]),
	]))->build();



	$inlineAttr = array();
	$inlineAttr['class'] = 'project-proposal';
	if ($isInEditMode) {
		$inlineAttr['class'] .= ' sg-inline-edit';
		$inlineAttr['data-update-url'] = url('project/develop/update/'.$tpid);
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-refresh-url'] = url('project/proposal/'.$tpid,array('debug'=>post('debug')));
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}

	$ret .= '<div id="project-proposal" '.sg_implode_attr($inlineAttr).'>'._NL;

	$ret .= '<header class="header -hidden -to-print"><h2 class=" -sg-text-center">แบบเสนอโครงการ<br />'.$proposalInfo->title.'</h2></header>';

	$ret .= '<div class="header"><h3>1. ชื่อโครงการ</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$ret .= '<div id ="section-1" class="section">';

	$ret .= view::inlineedit(
		array(
			'group' => 'topic',
			'fld' => 'title',
			'class' => '-fill -primary',
			'label' => 'ชื่อโครงการ (ภาษาไทย)',
			'desc' => '<em>ควรสั้น กระชับ เข้าใจง่าย และสื่อสาระของสิ่งที่จะทำอย่างชัดเจน ควรจะระบุชื่อชุมชนในชื่อโครงการเพื่อความสะดวกในการค้นหา</em>',
		),
		$proposalInfo->title,
		$isInEditMode
	);


	/*
	$ret .= '<span class="inline-edit-item -text"><label class="inline-edit-label">ชื่อหน่วยงานหลัก</label><span class="inline-edit-field -text -fill -empty" onclick="" data-fld="orgnamedo" data-group="bigdata::orgnamedo" data-label="ชื่อหน่วยงานหลัก" data-options="{
    &quot;class&quot;: &quot;-fill&quot;,
    &quot;placeholder&quot;: &quot;ระบุชื่อหน่วยงานหลักในการดำเนินการ&quot;
}" data-type="text" data-value="" title="คลิกเพื่อแก้ไข"><span><span class="placeholder -no-print">ระบุชื่อหน่วยงานหลักในการดำเนินการ</span></span></span></span>
*/
	$ret .= '<span class="inline-edit-item -text"><label class="inline-edit-label">สถาบันอุดมศึกษาหลัก</label>'
		. ($isInEditMode ? '<a id="project-info-org" class="sg-action" href="'.url('project/proposal/'.$tpid.'/info.selectorg').'" data-rel="box" data-width="640"><span class="inline-edit-field -text -readonly"><span>'.SG\getFirst($proposalInfo->info->orgName,'<span class="placeholder">เลือกชื่อสถาบันอุดมศึกษาหลัก</span>').'</span></span><i class="icon -material -gray">keyboard_arrow_down</i></a>' : $proposalInfo->info->orgName)
		. '</span>';


	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::orgnamedo',
			'fld' => 'orgnamedo',
			'label' => 'ชื่อหน่วยงานหลัก',
			'options' => '{class: "-fill", placeholder: "ระบุชื่อหน่วยงานหลักในการดำเนินการ"}',
		),
		$proposalInfo->data['orgnamedo'],
		$isInEditMode
	);

	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::project-coorg',
			'fld' => 'project-coorg',
			'label' => 'ชื่อหน่วยงานร่วม',
			'options' => '{class: "-fill", placeholder: "ระบุชื่อหน่วยงานที่ร่วมดำเนินการ"}',
			'placeholder' => '',
		),
		$proposalInfo->data['project-coorg'],
		$isInEditMode
	);

	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::commune',
			'fld' => 'commune',
			'label' => 'ชื่อชุมชน',
			'options' => '{class: "-fill", placeholder: "ระบุชื่อชุมชน"}',
			'placeholder' => '',
		),
		$proposalInfo->data['commune'],
		$isInEditMode
	);

	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::prowner',
			'fld' => 'prowner',
			'label' => 'ชื่อผู้รับผิดชอบ',
			'options' => '{class: "-fill", placeholder: "ระบุชื่อผู้รับผิดชอบ"}',
			'placeholder' => '',
		),
		$proposalInfo->data['prowner'],
		$isInEditMode
	);
	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::owner-address',
			'fld' => 'owner-address',
			'label' => 'ที่อยู่ผู้รับผิดชอบ',
			'options' => '{class: "-fill", placeholder: "ระบุที่อยู่ผู้รับผิดชอบ"}',
			'placeholder' => '',
		),
		$proposalInfo->data['owner-address'],
		$isInEditMode
	);


	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::prphone',
			'fld' => 'prphone',
			'label' => 'การติดต่อ',
			'options' => '{class: "-fill", placeholder: "ระบุช่องทางการติดต่อ เช่น เบอร์โทร"}',
			'placeholder' => '',
		),
		$proposalInfo->data['prphone'],
		$isInEditMode
	);

	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::owner-join',
			'fld' => 'owner-join',
			'label' => 'ชื่อผู้ร่วมโครงการ',
			'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุรายละเอียดชื่อผู้ร่วมโครงการ/สาขา ชื่อละ 1 บรรทัด"}',
			'value'=>trim($proposalInfo->data['owner-join']),
		),
		nl2br($proposalInfo->data['owner-join']),
		$isInEditMode,
		'textarea'
	);

	/*
	$tables = new Table();
	$tables->rows[] = array(
		'ชื่อชุมชน',
		view::inlineedit(array('group'=>'bigdata','fld'=>'commune', 'options' => '{class: "-fill", placeholder: "ระบุชื่อชุมชน"}'),$projectInfo->bigdata['commune'],$isInEditMode)
	);
	$tables->rows[] = array(
		'ชื่อผู้รับผิดชอบ',
		view::inlineedit(array('group'=>'bigdata','fld'=>'prowner', 'options' => '{class: "-fill", placeholder: "ระบุชื่อผู้รับผิดชอบ"}'),$projectInfo->info->prowner,$isInEditMode)
	);
	$tables->rows[] = array(
		'ที่อยู่ผู้รับผิดชอบ',
		view::inlineedit(array('group'=>'bigdata','fld'=>'owner-address', 'options' => '{class: "-fill", placeholder: "ระบุที่อยู่ผู้รับผิดชอบ"}'),$projectInfo->bigdata['owner-address'],$isInEditMode)
	);
	$tables->rows[] = array(
		'การติดต่อ',
		view::inlineedit(array('group'=>'bigdata','fld'=>'prphone', 'options' => '{class: "-fill", placeholder: "ระบุช่องทางการติดต่อ เช่น เบอร์โทร"}'),$projectInfo->info->prphone,$isInEditMode)
	);

	$ret .= $tables->build();
	*/


	$ret .= '</div><!-- section-1 -->';


	$ret .= '<div class="header"><h3>2. พื้นที่ดำเนินงาน</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$ret .= '<div id ="section-area" class="section">';
	/*
	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata',
			'fld' => 'commune-name',
			'class' => '-fill',
			'label' => 'ชื่อชุมชน (ใหม่/เดิม)',
		),
		$proposalInfo->data['commune-name'],
		$isInEditMode
	);
	*/

	$ret .= R::Page('project.proposal.info.area', NULL, $proposalInfo, $actionMode);

	$tables->rows[] = array(
		'ละติจูด-ลองจิจูด',
		view::inlineedit(
			array(
				'group'=>'project',
				'fld'=>'location',
				'options' => '{id: "project-info-gis",class: "project-info-latlng -fill", placeholder: "เช่น 7.0000,100.0000"}',
				'posttext' => '<nav style="display:inline; position: absolute; right: 8px; z-index: 1;"><a class="sg-action project-pin" href="'.url('project/'.$tpid.'/info.map').'" data-rel="box" data-width="640" data-class-name="-map"><i class="icon -material">place</i></a> <a href="https://maps.google.com/maps?daddr='.$projectInfo->info->lat.','.$projectInfo->info->lnt.'" target="_blank"><i class="icon -material -gray">directions</i></a></nav>',
			),
			($projectInfo->info->location ? $projectInfo->info->lat.','.$projectInfo->info->lnt:''),
			$isInEditMode
		)
	);

	$ret .= '</div><!-- section-1 -->';


	$ret .= '<div class="header"><h3>3. รายละเอียดชุมชน</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$ret .= '<div id ="section--" class="section">';

	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::info-commune',
			'fld' => 'info-commune',
			'label' => 'ข้อมูลพื้นฐาน',
			'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุข้อมูลพื้นฐานของชุมชน"}',
			'value'=>trim($proposalInfo->data['info-commune']),
		),
		nl2br($proposalInfo->data['info-commune']),
		$isInEditMode,
		'textarea'
	);

	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::info-potential',
			'fld' => 'info-potential',
			'label' => 'ข้อมูลศักยภาพ/ทรัพยากร',
			'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุข้อมูลศักยภาพ/ทรัพยากร"}',
			'value'=>trim($proposalInfo->data['info-potential']),
		),
		nl2br($proposalInfo->data['info-potential']),
		$isInEditMode,
		'textarea'
	);

	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::info-issue',
			'fld' => 'info-issue',
			'label' => 'ข้อมูลประเด็นปัญหา',
			'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุข้อมูลข้อมูลประเด็นปัญหา"}',
			'value'=>trim($proposalInfo->data['info-issue']),
		),
		nl2br($proposalInfo->data['info-issue']),
		$isInEditMode,
		'textarea'
	);

	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::info-need',
			'fld' => 'info-need',
			'label' => 'ข้อมูลความต้องการเชิงพื้นที่',
			'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุข้อมูลความต้องการเชิงพื้นที่"}',
			'value'=>trim($proposalInfo->data['info-need']),
		),
		nl2br($proposalInfo->data['info-need']),
		$isInEditMode,
		'textarea'
	);

	$ret .= '</div><!-- section-- -->';



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

	$ret .= '<div class="header"><h3>4. ประเด็นปัญหาหลัก</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';
	$ret .= '<section class="project-develop-issue" id="project-develop-issue">';

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
			.' </label></abbr>';
	}
	$ret .= '</section><!-- project-info-issue -->';


	// ประเด็นที่เกี่ยวข้อง
	$ret .= '<div class="header"><h3>5. ประเด็นที่เกี่ยวข้อง</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$stmt = 'SELECT
		  tg.`catid`, tg.`catparent`, tg.`name`
		, tr.`bigid`, tr.`flddata`, tg.`process`, tg.`weight`
		FROM %tag% tg
			LEFT JOIN %bigdata% tr ON tr.`keyname` = "project.develop" AND tr.`keyid` = :tpid AND tr.`fldname` = CONCAT("category-",tg.`catid`)
		WHERE tg.`taggroup` = "project:issue" AND tg.`process` = 2
		ORDER BY tg.`weight` ASC, tg.`catid` ASC';

	$issueDbs = mydb::select($stmt,':tpid',$tpid);
	//$ret .= print_o($issueDbs, '$issueDbs');

	$ret .= '<section class="project-info-issue" id="project-info-issue">';

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
			.' </label></abbr>';
	}

	//if ($issueDbs->_empty) $ret .= '<div class="-no-print">ยังไม่มีการสร้างรายการความสอดคล้องในระบบ</div>';
	$ret .= ($isInEditMode ? implode('', $optionsIssue) : implode(' , ', $optionsIssue));
	//$ret .= print_o($optionsIssue,'$optionsIssue');
	$ret .= '</section><!-- project-info-issue -->';




	$ret .= '<div class="header"><h3>6. องค์ความรู้หรือนวัตกรรมที่ใช้ในการดำเนินโครงงาน</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$ret .= '<section class="project-info-knowledge" id="project-info-knowledge">';

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

	/*
	$ret .= view::inlineedit(
		array(
			'group'=>'bigdata:project.info:knowledge',
			'fld'=>'knowledge',
			'ret'=>'nl2br',
			'options' => '{placeholder: "ระบุรายละเอียดองค์ความรู้หรือนวัตกรรมที่ใช้ในการดำเนินโครงงาน"}',
			'value'=>trim($projectInfo->bigdata['knowledge']),
		),
		nl2br($projectInfo->bigdata['knowledge']),
		$isEdit,
		'textarea'
	);
	*/
	$ret .= '</section><!-- project-info-desc -->';




	$ret .= '<div class="header"><h3>7. วัตถุประสงค์</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$ret .= '<div id ="section-3" class="section">';
	$ret .= R::Page('project.proposal.info.objective', NULL, $proposalInfo, $actionMode);
	$ret .= '</div><!-- section-3 -->';


	$ret .= '<div class="header"><h3>8. กลุ่มเป้าหมาย</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$ret .= '<div id ="section-4" class="section">';
	$ret .= R::Page('project.proposal.info.target', NULL, $proposalInfo, $actionMode);
	$ret .= '</div><!-- section-4 -->';



	$ret .= '<div class="header"><h3>9. ระยะเวลา</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$ret .= '<div id ="section-5" class="section">';
	$ret .= view::inlineedit(
		array(
			'group' => 'dev',
			'fld' => 'date_from',
			'class' => '-inline',
			'label' => 'ตั้งแต่',
			'value' => sg_date($proposalInfo->info->date_from,'d/m/Y'),
			'ret' => 'date:ว ดดด ปปปป',
			'container' => '{class: "-inline"}',
		),
		$proposalInfo->info->date_from ? sg_date($proposalInfo->info->date_from, 'ว ดดด ปปปป') : '',
		$isInEditMode,
		'datepicker'
	);
	$ret .= view::inlineedit(
		array(
			'group' => 'dev',
			'fld' => 'date_end',
			'class' => '-inline',
			'label' => 'ถึง',
			'value' => sg_date($proposalInfo->info->date_end,'d/m/Y'),
			'ret' => 'date:ว ดดด ปปปป',
			'container' => '{class: "-inline"}',
		),
		$proposalInfo->info->date_end ? sg_date($proposalInfo->info->date_end, 'ว ดดด ปปปป') : '',
		$isInEditMode,
		'datepicker'
	);
	$ret .= '</div><!-- section-5 -->';


	$ret .= '<div class="header"><h3>10. วิธีดำเนินงาน</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$ret .= '<div id ="section-6" class="section">';
	$ret .= R::Page('project.proposal.info.plan.single', NULL, $proposalInfo, $actionMode);
	$ret .= '</div><!-- section-6 -->';


	$ret .= '<div class="header"><h3>11. งบประมาณ</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$ret .= '<div id ="section-7" class="section">';
	$ret .= view::inlineedit(
		array(
			'group' => 'dev',
			'fld' => 'budget',
			'class' => '-money',
			'label' => 'งบประมาณ',
			'posttext' => 'บาท',
			'ret' => 'money',
		),
		number_format($proposalInfo->info->budget,2),
		$isInEditMode
	);
	$ret .= '</div><!-- section-7 -->';



	$ret .= '<div class="header"><h3>12. การติดตามประเมินผล</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

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

	if ($isViewOnly) {
		// Not show floating menu
	} else if ($isInEditMode) {
		$ret .= '<div class="btn-floating -right-bottom -no-print"><a class="sg-action btn -primary -circle48" href="'.url('project/proposal/'.$tpid,array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -material">done_all</i></a></div>';
	} else if ($isEditable) {
		$ret .= '<div class="btn-floating -right-bottom -no-print"><a class="sg-action btn -floating -circle48" href="'.url('project/proposal/'.$tpid,array('mode'=>'edit','debug'=>post('debug'))).'" data-rel="#main"><i class="icon -material">edit</i></a></div>';
	}

	$ret.='<section class="-no-print" style="margin: 16px 0; padding: 8px;">นำเข้าสู่ระบบโดย <a class="sg-action" href="'.url('project/'.$tpid.'/info.u/'.$proposalInfo->info->uid).'" data-rel="box" data-width="640" title="'.htmlspecialchars($proposalInfo->info->ownerName).'"><img src="'.model::user_photo($proposalInfo->info->username).'" width="30" height="30" alt="'.htmlspecialchars($proposalInfo->info->ownerName).'" style="border-radius: 50%; vertical-align: middle;" /> '.$proposalInfo->info->ownerName.'</a> เมื่อวันที่ '.sg_date($proposalInfo->info->created,'ว ดดด ปปปป H:i').' น.</section>';

	$ret .= '</div><!-- project-proposal -->';


	$ret .= '<style type="text/css">
	.module-project.-inno .page.-main .header {margin: 8px 0; position: relative;}
	.inline-edit-item {display: block; padding: 4px 16px;}
	.inline-edit-item.-inline {display: inline-block; padding: 16px;}
	.inline-edit-view.-text.-fill {display: block;}
	.checkbox.-block .inline-edit-item {padding: 0;}
	.inline-edit-desc {padding: 8px;}
	.section {margin-bottom: 64px;}
	.project-toogle-display {position: absolute; top: 6px; right: 6px; box-shadow:0 0 0 1px #ccc inset; border-radius: 50%; padding: 8px;}
	</style>';

	//$ret.=print_o($proposalInfo,'$proposalInfo');

	return $ret;

































	// NOT USE CODE



	if ($info->date_from) $info->date_from=sg_date($info->date_from,'d/m/Y');
	if ($info->date_end) $info->date_end=sg_date($info->date_end,'d/m/Y');



	$cfg['domain']=cfg('domain');
	$cfg['url']=cfg('url');
	$cfg['tpid']=$tpid;
	$cfg['prid']=$info->prid;
	$cfg['orgname']=$info->orgName;
	if ($isEdit) {
		$cfg['action']=$action?'/'.$action:'';
		$cfg['para-action']=$action;
	}
	$cfg['hidden']=$isEdit?'-show':'-hidden';


	// Get develop template file
	$devTemplate=cfg('template');
	$devTemplateFileName=SG\getFirst($info->template?'file.develop.'.$info->template.'.html':NULL,cfg('project.develop.file'),'file.develop.default.html');
	foreach (explode(';', cfg('template')) as $devTemplate) {
		$devFileName = dirname(__FILE__).'/../template/'.($devTemplate?'/'.$devTemplate:'').'/'.$devTemplateFileName;
		//$ret .= dirname(__FILE__).'<br />'.$devFileName.'<br />';
		if (file_exists($devFileName)) {
			break;
		}
		unset($devFileName);
	}
	if (empty($devFileName)) {
		$devFileName=dirname(__FILE__).'/'.$devTemplateFileName;
	}

	if (!file_exists($devFileName)) return $ret.message('error','ไม่สามารถเปิดแฟ้มข้อมูลพัฒนาโครงการได้');



	$body=file_get_contents($devFileName);

	$data=project_model::explode_body($info->body);

	foreach ($info as $key => $value) {
		if (substr($key, 0,1)=='_') continue;
		$data[$key]=$value;
	}
	$data['title']=$info->title;

	$stmt='SELECT SUM(`flddata`) rating FROM %bigdata% WHERE `keyid`=:tpid AND `keyname`="project.develop" AND `fldname` LIKE "rating-indicator-%" LIMIT 1';
	$cfg['ratingIndicator']=number_format(mydb::select($stmt,':tpid',$tpid)->rating);
	$cfg['ratingPercent']=number_format($cfg['ratingIndicator']*100/45,2);

	$stmt='SELECT `fldname`,`flddata` FROM %bigdata% WHERE `keyid`=:tpid AND `keyname`="project.develop"';
	foreach (project_model::get_develop_data($tpid) as $key=>$value) {
		$data[$key]=$value;
	}
	if ($action=='showdata') {
		$ret.='<div style="width:50%;float:left;"><p>'.str_replace("\n", '</p><p>', $info->body).'</p></div><div style="width:50%;float:left;">'.print_o($data,'$data').'</div>';
		return $ret;
	}

	preg_match_all('|(<span(.*?data-fld=\"([a-zA-Z0-9\-].*?)\".*?)>)(.*?)(</span>)|s',$body,$matchesField);
	//$ret.=print_o($matchesField,'$matches');

	foreach ($matchesField[3] as $keyField => $fieldName) {
		$fields[$fieldName]=sg_explode_attr($matchesField[2][$keyField]);
		if ($data[$fieldName]!='') {
			if ($fields[$fieldName]['type']=='textarea') {
			} else if ($fields[$fieldName]['ret']=='numeric') {
				$data[$fieldName]=number_format(preg_replace('/[^0-9\.\-]/','',$data[$fieldName]),2);
			}
			//$data[$fieldName]=nl2br($data[$fieldName]);
			//$ret.=$fieldName.'='.htmlspecialchars($data[$fieldName]).'<br />';
		}
	}

	$problemWeight=array();
	foreach ($data as $key => $value) {
		list($a,$b,$c,$d,$e)=explode('-',$key);
		if ($a=="commune" && $b=="problem" && $e=="weight") $problemWeight[$c][]=$value;
	}
	foreach ($problemWeight as $key => $value) {
		$weightTotal=1;
		foreach ($value as $wv) $weightTotal*=$wv;
		$data['commune-problem-'.$key.'-weight']=$weightTotal;
	}



	// Remove ข้อมูล่วนบุคค
	if (!$isFullView) {
		$data['name-leader-cid']='*************';
		$data['name-leader-address']='**';
		$data['name-leader-phone']='********';
		$data['name-leader-mobile']='********';
		$data['name-leader-fax']='********';
		$data['name-leader-email']='********';

		$data['owner-cid']='*************';
		$data['owner-address']='**';
		$data['owner-phone']='********';
		$data['owner-mobile']='********';
		$data['owner-fax']='********';
		$data['owner-email']='********';

		for ($i=1;$i<=5;$i++) {
			$data['coowner-'.$i.'-cid']='*************';
			$data['coowner-'.$i.'-address']='**';
			$data['coowner-'.$i.'-phone']='********';
			$data['coowner-'.$i.'-mobile']='********';
			$data['coowner-'.$i.'-fax']='********';
			$data['coowner-'.$i.'-email']='********';
		}
	}



	//$ret.=print_o($fields,'$fields');
	//$ret.=print_o($data,'$data');
	//$ret.=print_o($problemWeight,'$problemWeight');



	// Replace date into data-value
	$body=preg_replace_callback(
						'|(<span.*?data-fld=\"([a-zA-Z0-9\-].*?)\".*?)>(.*?)(</span>)|s',
						function($m) use ($data) {
							$value=$data[$m[2]]!=''?$data[$m[2]]:$m[3];
							return $m[1].(' data-value="'.htmlspecialchars($value).'"').'><span>'.nl2br($value).'</span>'.$m[4];
						},
						$body
					);

	$body=preg_replace_callback(
						'/(<input type=\"radio\".*?data-fld=\"([a-zA-Z0-9\-].*?)\".*? value=\"(.*?)\")(.*?)(\/>)/s',
						function($m) use ($data) {
							// $m[1]=เริ่มจาก <input .... value="..."
							// $m[2]=ชื่อฟิลด์
							// $m[3]=value
							// $m[4]=attribute หลัง value
							// $m[5]=/>
							$dataField=$m[2];
							$radioValue=$m[3];
							$dataValue=$data[$m[2]];
							//echo 'm1='.htmlspecialchars($m[1]).' m2='.$m[2].' m3="'.$m[3].'" m4='.htmlspecialchars($m[4]).' m5='.htmlspecialchars($m[5]).'<br />'._NL;
							$checked='';
							if (array_key_exists($dataField, $data) && $dataValue==$radioValue) {
								$checked=' checked="checked"';
							}
							//echo '$dataValue="'.$dataValue.'" radioValue="'.$radioValue.'" '.$checked.'<br />';
							return $m[1].$checked.$m[4].$m[5];
						},
						$body
					);

	$body=preg_replace_callback(
						'/(<input type=\"checkbox\".*?data-fld=\"([a-zA-Z0-9\-].*?)\".*? value=\"(.*?)\")(.*?)(\/>)/s',
						function($m) use ($data,$isEdit) {
							// $m[1]=เริ่มจาก <input .... value="..."
							// $m[2]=ชื่อฟิลด์
							// $m[3]=value
							// $m[4]=attribute หลัง value
							// $m[5]=/>
							$dataField=$m[2];
							$radioValue=$m[3];
							$dataValue=$data[$m[2]];
							//echo 'm1='.htmlspecialchars($m[1]).' m2='.$m[2].' m3="'.$m[3].'" m4='.htmlspecialchars($m[4]).' m5='.htmlspecialchars($m[5]).'<br />'._NL;
							$checked='';
							if (array_key_exists($dataField, $data) && $dataValue) {
								$checked=' checked="checked"';
							}
							if (!$isEdit) $checked.=' disabled="disabled"';
							//echo '$dataValue="'.$dataValue.'" radioValue="'.$radioValue.'" '.$checked.'<br />';
							return $m[1].$checked.$m[4].$m[5];
						},
						$body
					);

	for ($i=1; $i<=20; $i++) {
		if ($data['commune-problem-'.$i.'-title']==''
				&& $data['commune-problem-'.$i.'-size']==''
				&& $data['commune-problem-'.$i.'-violence']==''
				&& $data['commune-problem-'.$i.'-awareness']==''
				&& $data['commune-problem-'.$i.'-difficulty']=='') {
			$cfg['commune-problem-'.$i.'-class']='noprint';
		}

		if ($data['objective-'.$i.'-title']==''
				&& $data['objective-'.$i.'-indicators']==''
				&& $data['objective-'.$i.'-indicators-qu']=='') {
			$cfg['objective-'.$i.'-class']='noprint';
		}

		if ($data['plan-'.$i.'-objective']==''
				&& $data['plan-'.$i.'-indicator']==''
				&& $data['plan-'.$i.'-target']==''
				&& $data['plan-'.$i.'-activity']==''
				&& $data['plan-'.$i.'-period']==''
				&& $data['plan-'.$i.'-activity']==''
				&& $data['plan-'.$i.'-output']==''
				&& $data['plan-'.$i.'-budget']==''
				&& $data['plan-'.$i.'-parties']==''
				) {
			$cfg['plan-'.$i.'-class']='noprint';
		}
	}

	$cfg['planhidden']=post('showplan')?'show':'hidden';
	$cdate=date('Y-m-d H:i:s');

	if ($isEdit) $cfg['datainput']='inline-edit-field'; else $cfg['datainput']='datainput-disable';
	if ($is_comment_hsmi) $cfg['comment-hsmi-input']='inline-edit-field';
	if ($is_comment_sss) $cfg['comment-sss-input']='inline-edit-field';
	$cfg['css-print']= post('o')=='word' ? '' : '@media print {';
	$cfg['css-print-end']= post('o')=='word' ? '' : '}';

	$cfg['historyurl']=url('project/history');//,array('tpid'=>$tpid,'k'=>'tr,info,mainact,detail1,'.$mainact->trid));
	$cfg['removeOnNoEdit']=$isEdit?'':'yes';

	$body=preg_replace_callback('|(\{\$([a-zA-Z0-9\-].*?)\})|',
						function($m) use ($cfg) {
						//	echo $m[1];
							return $cfg[$m[2]];
						},
						$body);

	// Remove link with attribute data-remove="yes"
	$body=preg_replace('/<a.+?data-remove=\"yes\".+?>.+?<\/a>/i', "", $body);




	if ($isEdit || $is_comment_sss || $is_comment_hsmi) {
		head('<script>var tpid='.$tpid.'</script>');

		$inlinePara['class'] = 'sg-inline-edit';
		$inlinePara['data-tpid'] = $tpid;
		$inlinePara['data-update-url'] = url('project/proposal/update/'.$tpid);
		if (debug('inline')) $inlinePara['data-debug']='inline';
	}

	foreach ($inlinePara as $k => $v) {
		$inlineStr.=$k.'="'.$v.'" ';
	}

	if (post('a')=='download') {
		sendheader('application/octet-stream');
		header('Content-Disposition: attachment; filename="'.$info->title.'.doc"');
	}
	if (post('o')=='word') {
		// move style tag to head section
		if (preg_match_all('/<style.*?>.*?<\/style>/si',$body,$out)) {
			foreach ($out[0] as $style) $styles.=$style._NL;
			$body=preg_replace('/(<style.*?>.*?<\/style>)/si','',$body);
		}
		$ret='<HTML>
		<HEAD>
		<META HTTP-EQUIV="CONTENT-TYPE" CONTENT="text/html; charset=utf-8">
		<TITLE>'.$info->title.'</TITLE>
		'.$styles.'
		</HEAD>
		<BODY>
		'.$body.'
		</BODY>
		</HTML>';
		cfg('Content-Type','text/xml');
		return $ret;
	}

	$ret.='<div id="project-develop" '.$inlineStr.'>'._NL;
	$ret.=$body;
	$ret.='</div>';


	if ($isViewOnly) {
		// Do nothing
	} else if ($isEdit) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/proposal/'.$tpid,array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a></div>';
	} else if ($isEditable) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/proposal/'.$tpid,array('mode'=>'edit','debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
	}

	/*
	if ($isEditable && $action != 'edit') {
		//$ret.='<a class="sg-action btn -floating" href="'.url('project/proposal/'.$tpid.'/edit',array('debug'=>post('debug'))).'" data-rel="#main" style="position: fixed; bottom: 16px; right:16px; z-index: 9999999; border-radius: 50%; padding: 16px;"><i class="icon -edit -white"></i></a>';
		$ret.='<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('project/proposal/'.$tpid.'/edit',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
	}
  if ($action=='edit') {
		$ret.='<div class="-sg-text-right -no-print"><a class="btn -primary" href="'.url('project/proposal/'.$tpid).'"><i class="icon -save -white"></i><span>เรียบร้อย</span></a></div>';
	}
	*/

	//$ret.=print_o($info,'$info');
	return $ret;
}
?>