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
// debugMsg(func_get_args(),'$args');
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
	$ret .= '<span class="inline-edit-item -text"><label class="inline-edit-label">สถาบันอุดมศึกษา</label>'
		. ($isInEditMode ? '<a id="project-info-org" class="sg-action" href="'.url('project/proposal/'.$tpid.'/info.selectorg').'" data-rel="box" data-width="640"><span class="inline-edit-field -text -readonly"><span>'.SG\getFirst($proposalInfo->info->orgName,'<span class="placeholder">เลือกชื่อสถาบันอุดมศึกษา</span>').'</span></span><i class="icon -material -gray">keyboard_arrow_down</i></a>' : $proposalInfo->info->orgName)
		. '</span>';


	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::orgnamedo',
			'fld' => 'orgnamedo',
			'label' => ' (คณะ/สถาบัน)',
			'options' => '{class: "-fill", placeholder: "ระบุชื่อหน่วยงานหลักในการดำเนินการ"}',
		),
		$proposalInfo->data['orgnamedo'],
		$isInEditMode
	);

	/*
	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::project-coorg',
			'fld' => 'project-coorg',
			'label' => 'ชื่อหน่วยงาน (คณะ/สถาบัน)',
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
	*/

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

	/*
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
	*/


	$ret .= '</div><!-- section-1 -->';



	// ประเภทและเส้นทางงบประมาณ
	$ret .= '<div class="header"><h3>2. ประเภทและเส้นทางงบประมาณ</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$ret .= '<div id ="section-budget-line" class="section -budget-line">';

	$tabUi = new Ui(NULL, 'ui-tab -no-print');

	$stmt = 'SELECT b.`flddata` `grantId`, p.`catid` `policyId`, p.`name` `policyName`
		FROM %bigdata% b
			LEFT JOIN %tag% g ON g.`taggroup` = "project:uokr:grant" AND g.`catid` = b.`flddata`
			LEFT JOIN %tag% p ON p.`taggroup` = "project:uokr:policy" AND p.`catid` = g.`catparent`
		WHERE `keyname` = "project.develop" AND `keyid` = :tpid AND `fldname` = "grant"
		LIMIT 1
		';
	$policyInfo = mydb::select($stmt, ':tpid', $tpid);

	$stmt = 'SELECT * FROM %tag% WHERE `taggroup` = "project:uokr:policy" ORDER BY `catid`';
	foreach (mydb::select($stmt)->items as $rs) {
		$tabUi->add('<a class="sg-action" href="'.url('project/proposal/'.$tpid.'/info.policy/'.$rs->catid.($isInEditMode ? '/edit' : '')).'" data-rel="#budget-line-info"><i class="icon -material">check_circle</i><span>'.$rs->name.'</span></a>', '{class: "'.($rs->catid == $policyInfo->policyId ? '-active' : '').'"}');
	}

	//$ret .= print_o($policyInfo, '$policyInfo');

	$ret .= '<div class="sg-tabs">'
		. $tabUi->build()
		. '<div id="budget-line-info">'.($policyInfo->policyId ? R::Page('project.proposal.info.policy', NULL, $proposalInfo, $policyInfo->policyId, $isInEditMode) : '').'</div>'
		. '</div>';

	$ret .= '</div><!-- section-- -->';



/*
	// รายละเอียดชุมชน
	$ret .= '<div class="header"><h3>3. รายละเอียดชุมชน</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

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

	*/



	// ประเด็นที่เกี่ยวข้อง
	$ret .= '<div class="header"><h3>3. ประเด็นที่เกี่ยวข้อง</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$stmt = 'SELECT
		  tg.`catid`, tg.`catparent`, tg.`name`
		, tr.`bigid`, tr.`flddata`, tg.`process`, tg.`weight`
		FROM %tag% tg
			LEFT JOIN %bigdata% tr ON tr.`keyname` = "project.develop" AND tr.`keyid` = :tpid AND tr.`fldname` = "category" AND `fldref` = tg.`catid`
		WHERE tg.`taggroup` = "project:issue" AND tg.`process` = 2
		ORDER BY tg.`weight` ASC, tg.`catid` ASC';

	$issueDbs = mydb::select($stmt,':tpid',$tpid);
	//$ret .= print_o($issueDbs, '$issueDbs');

	$ret .= '<section class="project-info-issue" id="project-info-issue">';

	foreach ($issueDbs->items as $rs) {
		$ret .='<abbr class="checkbox -block"><label>'
			.view::inlineedit(
				array(
					'group'=>'bigdata:project.develop:categoty-'.$rs->catid,
					'fld' => 'category',
					'fldref' => $rs->catid,
					'tr' => $rs->bigid,
					'value'=>$rs->flddata,
					'removeempty'=>'yes',
				),
				$rs->catid.':'.$rs->name,
				$isInEditMode,
				'checkbox')
			.' </label></abbr>';
	}

	$ret .= '</section><!-- project-info-issue -->';


	// ยุทธศาสตร์ชาติ 20 ปี
	$ret .= '<div class="header"><h3>4. ความสอดคล้องกับยุทธศาสตร์ชาติ 20 ปี</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$stmt = 'SELECT
		  tg.`catid`, tg.`catparent`, tg.`name`
		, tr.`bigid`, `keyname`, `fldname`, `fldref`, tr.`flddata`, tg.`process`, tg.`weight`
		FROM %tag% tg
			LEFT JOIN %bigdata% tr ON tr.`keyname` = "project.develop" AND tr.`keyid` = :tpid AND tr.`fldname` = "strategy20yr" AND tr.`fldref` = tg.`catid`
		WHERE tg.`taggroup` = "project:uokr:strategy20yr" AND tg.`process` = 1
		ORDER BY tg.`weight` ASC, tg.`catid` ASC';

	$strategyDbs = mydb::select($stmt,':tpid',$tpid);
	//$ret .= print_o($strategyDbs, '$strategyDbs');

	$ret .= '<section class="project-info-strategy20yr" id="project-info-strategy20yr">';

	foreach ($strategyDbs->items as $rs) {
		$ret .='<abbr class="checkbox -block"><label>'
			.view::inlineedit(
				array(
					'group'=>'bigdata:project.develop:strategy20yr-'.$rs->catid,
					'fld' => 'strategy20yr',
					'fldref' => $rs->catid,
					'tr' => $rs->bigid,
					'value' => $rs->flddata,
					'removeempty' => 'yes',
				),
				$rs->catid.':'.$rs->name,
				$isInEditMode,
				'checkbox')
			.' </label></abbr>';
	}

	$ret .= '</section><!-- project-info-issue -->';



	$ret .= '<div class="header"><h3>5. พื้นที่ดำเนินงาน</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$ret .= '<div id ="section-area" class="section">';
	$ret .= R::Page('project.proposal.info.area', NULL, $proposalInfo, $actionMode);
	$ret .= '</div><!-- section-1 -->';


	$ret .= '<div class="header"><h3>6. หลักการและเหตุผล</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$ret .= '<section class="project-info-problem" id="project-info-problem">';

	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::problem',
			'fld' => 'problem',
			'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุหลักการและเหตุผล"}',
			'value'=>trim($proposalInfo->data['problem']),
		),
		nl2br($proposalInfo->data['problem']),
		$isInEditMode,
		'textarea'
	);
	$ret .= '</section><!-- project-info-problem -->';




	/*
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

	$ret .= '</section><!-- project-info-knowledge -->';
	*/




	$ret .= '<div class="header"><h3>7. วัตถุประสงค์</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$ret .= '<div id ="section-3" class="section">';
	$ret .= R::Page('project.proposal.info.objective', NULL, $proposalInfo, $actionMode);
	$ret .= '</div><!-- section-3 -->';


	$ret .= '<div class="header"><h3>7. ทบทวนวรรณกรรม</h3><a class="project-toogle-display -no-print" href="javascript:void(0)"><icon class="icon -up"></i></a></div>';

	$ret .= '<section class="project-info-literature" id="project-info-literature">';

	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::literature',
			'fld' => 'literature',
			'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุรายละเอียดทบทวนวรรณกรรม"}',
			'value'=>trim($proposalInfo->data['literature']),
		),
		nl2br($proposalInfo->data['literature']),
		$isInEditMode,
		'textarea'
	);
	$ret .= '</section><!-- project-info-literature -->';


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

	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::output',
			'fld' => 'output',
			'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุผลผลิต"}',
			'label' => 'ผลผลิต (Output)',
			'value'=>trim($proposalInfo->data['output']),
		),
		nl2br($proposalInfo->data['output']),
		$isInEditMode,
		'textarea'
	);

	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::outcome',
			'fld' => 'outcome',
			'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุผลลัพธ์"}',
			'label' => 'ผลลัพธ์ (Outcome)',
			'value'=>trim($proposalInfo->data['outcome']),
		),
		nl2br($proposalInfo->data['outcome']),
		$isInEditMode,
		'textarea'
	);

	$ret .= view::inlineedit(
		array(
			'group' => 'bigdata::impact',
			'fld' => 'impact-commune',
			'options' => '{class: "-fill",ret: "nl2br", placeholder: "ระบุผลกระทบ"}',
			'label' => 'ผลกระทบ (Impact)',
			'value'=>trim($proposalInfo->data['impact']),
		),
		nl2br($proposalInfo->data['impact']),
		$isInEditMode,
		'textarea'
	);


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
	.page.-main .header {margin: 8px 0; position: relative;}
	.inline-edit-item {display: block; padding: 4px 16px;}
	.inline-edit-item.-inline {display: inline-block; padding: 16px;}
	.inline-edit-view.-text.-fill {display: block;}
	.inline-edit-item.-textarea>label {font-weight: bold; display: block;}
	.checkbox.-block .inline-edit-item {padding: 0;}
	.inline-edit-desc {padding: 8px;}
	.section {margin-bottom: 64px;}
	.project-toogle-display {position: absolute; top: 6px; right: 6px; box-shadow:0 0 0 1px #ccc inset; border-radius: 50%; padding: 8px;}
	.sg-tabs>.ui-tab {background-color: transparent;}
	.sg-tabs>.ui-tab .icon {color: transparent;}
	.sg-tabs>.ui-tab>.ui-item.-active .icon {color: green;}
	.ui-tab>li.-active {background-color: #fff;}
	.module-project.-uokr .project-proposal>.section.-budget-line {background-color: transparent;}
	#budget-line-info {background-color: #fff;}
	#budget-line-info>.header {margin-top: 0;}
	</style>';

	//$ret.=print_o($proposalInfo,'$proposalInfo');

	return $ret;
}
?>