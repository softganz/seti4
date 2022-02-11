<?php
/**
* Project View
*
* @param Object $self
* @param Int $tpid
* @return String
*/

import('widget:project.like.status.php');

function project_info_view($self, $tpid = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid);
	$tpid = $projectInfo->tpid;
	$actionMode = SG\getFirst(post('mode'),$_SESSION['mode']);
	//debugMsg($_SESSION,'$_SESSION');
	unset($_SESSION['mode']);

	if (!$tpid) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

	$self->theme->class .= ' project-status-'.$projectInfo->info->project_statuscode;

	R::View('project.toolbar', $self, $projectInfo->title, NULL, $projectInfo,'{showPrint: true}');

	page_class('-inno');

	$isAdmin = $projectInfo->info->isAdmin;
	$isEditable = $projectInfo->info->isEdit;
	$isEdit = $projectInfo->info->isEdit && $actionMode === 'edit';
	$isEditDetail = $isEdit && $projectInfo->info->isEditDetail;
	$lockReportDate = $projectInfo->info->lockReportDate;

	$showBudget = $projectInfo->is->showBudget;

	$isInnovationProject = $projectInfo->settings->type === 'INNO';

	//$ret .= print_o($projectInfo,'$projectInfo');

	R::Model('reaction.add', $tpid, 'TOPIC.VIEW');

	$ret .= (new ScrollView([
		'child' => new ProjectLikeStatusWidget([
			'projectInfo' => $projectInfo,
		]),
	]))->build();

	if ($isEditable) {
		$ret .= '<nav class="nav btn-floating -right-bottom">';
		if ($actionMode == 'edit') {
			$ret .= '<a class="sg-action btn -primary -circle48" href="'.url('project/'.$tpid).'" data-rel="#main" title="เรียบร้อย"><i class="icon -material">done_all</i></a>';
		} else {
			$ret .= '<a class="sg-action btn -floating -circle48" href="'.url('project/'.$tpid,array('mode'=>'edit')).'" data-rel="#main" title="แก้ไข"><i class="icon -material">edit</i></a>';
		}
		$ret .= '</nav>';
	}


	// รายละเอียดโครงการ
	$inlineAttr = array();
	$inlineAttr['class'] = 'project-info';
	if ($isEdit) {
		$inlineAttr['class'] .= ' sg-inline-edit';
		$inlineAttr['data-update-url'] = url('project/edit/tr');
		$inlineAttr['data-tpid'] = $tpid;
		$inlineAttr['data-refresh-url'] = url('project/'.$tpid,array('debug'=>post('debug')));
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}

	$ret .= '<div id="project-info" '.sg_implode_attr($inlineAttr).'>'._NL;



	$ret .= '<section id="project-detail-spec" class="project-detail-spec"><!-- section start -->'._NL;
	$ret .= '<header class="header -hidden -to-print"><h3>'.$projectInfo->title.'</h3></header>';

	$ret .= '<header class="header"><h3>ข้อมูลโครงการ</h3></header>';

	$tables = new Table();
	$tables->addClass('item__card project-info');
	$tables->colgroup = array('width="30%"','width="70%"');
	//$tables->caption = 'รายละเอียดโครงการ';


	$tables->rows[] = array(
		'ชื่อ'.SG\getFirst($projectInfo->settings->strings->{'โครงการ'},'โครงการ').($projectInfo->info->prtype!='โครงการ'?'<br />('.$projectInfo->info->prtype.')':''),
		'<strong>'
		.view::inlineedit(
			array('group'=>'topic','fld'=>'title','class'=>'-fill')
			,$projectInfo->title,
			$isEditDetail
		).'</strong>'.($isEdit?'<span class="form-required" style="margin-left:-16px;">*</span>':'')
	);

	$tables->rows[] = array(
		'สถาบันอุดมศึกษาหลัก',
		$isEdit ? '<a id="project-info-org" class="sg-action" href="'.url('project/'.$tpid.'/info.selectorg').'" data-rel="box" data-width="640"><span class="inline-edit-field -text -readonly"><span>'.SG\getFirst($projectInfo->info->orgName,'<span class="placeholder">เลือกชื่อสถาบันอุดมศึกษาหลัก</span>').'</span></span><i class="icon -material -gray">keyboard_arrow_down</i></a>' : $projectInfo->info->orgName,
	);

	$tables->rows[] = array(
		'หน่วยงานหลัก',
		view::inlineedit(array('group'=>'project','fld'=>'orgnamedo', 'options' => '{class: "-fill", placeholder: "ระบุชื่อหน่วยงานหลักในการดำเนินการ"}'),$projectInfo->info->orgnamedo,$isEditDetail)
	);

	$tables->rows[] = array(
		'หน่วยงานร่วม',
		view::inlineedit(array('group'=>'bigdata:project.info:project-coorg','fld'=>'project-coorg', 'options' => '{class: "-fill", placeholder: "ระบุชื่อหน่วยงานที่ร่วมดำเนินการ"}'),$projectInfo->bigdata['project-coorg'],$isEditDetail)
	);
	$tables->rows[] = array(
		'ชื่อชุมชน',
		view::inlineedit(array('group'=>'bigdata:project.info:commune','fld'=>'commune', 'options' => '{class: "-fill", placeholder: "ระบุชื่อชุมชน"}'),$projectInfo->bigdata['commune'],$isEditDetail)
	);
	$tables->rows[] = array(
		'ชื่อผู้รับผิดชอบ',
		view::inlineedit(array('group'=>'project','fld'=>'prowner', 'options' => '{class: "-fill", placeholder: "ระบุชื่อผู้รับผิดชอบ"}'),$projectInfo->info->prowner,$isEditDetail)
	);
	$tables->rows[] = array(
		'ที่อยู่ผู้รับผิดชอบ',
		view::inlineedit(array('group'=>'bigdata:project.info:owner-address','fld'=>'owner-address', 'options' => '{class: "-fill", placeholder: "ระบุที่อยู่ผู้รับผิดชอบ"}'),$projectInfo->bigdata['owner-address'],$isEditDetail)
	);

	$tables->rows[] = array(
		'ชื่อผู้ร่วมโครงการ/สาขา',
		view::inlineedit(
			array(
				'group'=>'bigdata:project.info:owner-join',
				'fld'=>'owner-join',
				'ret'=>'nl2br',
				'options' => '{placeholder: "ระบุรายละเอียดชื่อผู้ร่วมโครงการ/สาขา ชื่อละ 1 บรรทัด"}',
				'value'=>trim($projectInfo->bigdata['owner-join']),
			),
			nl2br($projectInfo->bigdata['owner-join']),
			$isEdit,
			'textarea'
		)
	);


	$tables->rows[] = array(
		'การติดต่อ',
		view::inlineedit(array('group'=>'project','fld'=>'prphone', 'options' => '{class: "-fill", placeholder: "ระบุช่องทางการติดต่อ เช่น เบอร์โทร"}'),$projectInfo->info->prphone,$isEditDetail)
	);


	$openYear = SG\getFirst($projectInfo->info->pryear,date('Y'));
	$pryearList = array();
	for ($i = $openYear-5; $i <= date('Y')+1; $i++) {
		$pryearList[$i] = $i + 543;
	}


	$tables->rows[] = array(
		'ปี พ.ศ.',
		view::inlineedit(array('group'=>'project','fld'=>'pryear','value'=>$projectInfo->info->pryear),$projectInfo->info->pryear+543,$isEditDetail,'select',$pryearList)
	);

	$tables->rows[] = array(
			'ระยะเวลาดำเนินโครงการ',
			view::inlineedit(
				array('group'=>'project','fld'=>'date_from','ret'=>'date:ว ดดด ปปปป','value'=>$projectInfo->info->date_from?sg_date($projectInfo->info->date_from,'d/m/Y'):''),
				$projectInfo->info->date_from,
				$isEdit,
				'datepicker'
			)
			. ' - '
			. view::inlineedit(
				array('group'=>'project','fld'=>'date_end','ret'=>'date:ว ดดด ปปปป', 'value'=>$projectInfo->info->date_end?sg_date($projectInfo->info->date_end,'d/m/Y'):''),
				$projectInfo->info->date_end,
				$isEdit,
				'datepicker'
			)
			.($isEdit ? ' <span class="form-required">*</span>' : '')
		);

	if ($showBudget) {
		$tables->rows[] = array(
			'งบประมาณ',
			view::inlineedit(array('group'=>'project','fld'=>'budget', 'ret'=>'money'),$projectInfo->info->budget,$isEditDetail,'money').' บาท'.($isEdit?' <span class="form-required">*</span>':'')
		);
	}


	if (empty($projectInfo->info->area))
		$projectInfo->info->area = $projectInfo->info->areaName;

	/*
	$tables->rows[] = array(
		'พื้นที่ดำเนินการ',
		R::Page('project.info.area', NULL, $projectInfo, $actionMode)
		.view::inlineedit(
			array(
				'group' => 'project',
				'fld' => 'area',
				'areacode' => $projectInfo->info->areacode,
				'options' => '{
					class: "-fill",
					autocomplete: {
						target: "areacode",
						query: "'.url('api/address').'",
						minlength: 5
					}
				}',
			),
			$projectInfo->info->area,
			$isEditDetail,
			'autocomplete'
		).'เลือกประเทศ/ภาค/จังหวัด/อำเภอ/ตำบล'
	);
	*/

	/*
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
			$isEdit
		)
	);
	*/


	$ret .= $tables->build()._NL;

	$ret .= '</section><!-- project-detail-spec -->';


	$ret .= '<section class="project-info-issue" id="project-info-issue">';
	$ret .= '<header class="header"><h3>พื้นที่ดำเนินงาน</h3></header><span>';
	$ret .= R::Page('project.info.area', NULL, $projectInfo, $actionMode);
	//$ret .= $projectInfo->info->location ? ' ('.$projectInfo->info->lat.','.$projectInfo->info->lnt .')' : '';
	$ret .= '</span></section>';


	// รายละเอียดโครงการ
	//$ret .= '<section id="project-detail-information" class="project-detail-information"><!-- section start -->'._NL;

	//$ret .= '<h2 class="title -main">ข้อมูลในการดำเนินโครงการ</h2>'._NL;


	$ret .= '<section class="project-info-desc" id="project-info-desc">';
	$ret .= '<header class="header"><h3>รายละเอียดชุมชน</h3></header>';
	$ret .= '<h5>ข้อมูลพื้นฐาน</h5>'
		. view::inlineedit(
		array(
			'group'=>'bigdata:project.info:info-commune',
			'fld'=>'info-commune',
			'ret'=>'nl2br',
			'options' => '{placeholder: "ระบุรายละเอียดข้อมูลพื้นฐานของชุมชน"}',
			'value'=>trim($projectInfo->bigdata['info-commune']),
		),
		nl2br($projectInfo->bigdata['info-commune']),
		$isEdit,
		'textarea'
	);


	$ret .= '<h5>ข้อมูลศักยภาพ/ทรัพยากร</h5/>'
		. view::inlineedit(
		array(
			'group'=>'bigdata:project.info:info-potential',
			'fld'=>'info-potential',
			'ret'=>'nl2br',
			'options' => '{placeholder: "ระบุรายละเอียดข้อมูลศักยภาพ/ทรัพยากร"}',
			'value'=>trim($projectInfo->bigdata['info-potential']),
		),
		nl2br($projectInfo->bigdata['info-potential']),
		$isEdit,
		'textarea'
	);

	$ret .= '<h5>ข้อมูลประเด็นปัญหา</h5>'
		. view::inlineedit(
		array(
			'group'=>'bigdata:project.info:info-issue',
			'fld'=>'info-issue',
			'ret'=>'nl2br',
			'options' => '{placeholder: "ระบุรายละเอียดข้อมูลประเด็นปัญหา"}',
			'value'=>trim($projectInfo->bigdata['info-issue']),
		),
		nl2br($projectInfo->bigdata['info-issue']),
		$isEdit,
		'textarea'
	);

	$ret .= '<h5>ข้อมูลความต้องการเชิงพื้นที่</h5>'
		. view::inlineedit(
		array(
			'group'=>'bigdata:project.info:info-need',
			'fld'=>'info-need',
			'ret'=>'nl2br',
			'options' => '{placeholder: "ระบุรายละเอียดข้อมูลความต้องการเชิงพื้นที่"}',
			'value'=>trim($projectInfo->bigdata['info-need']),
		),
		nl2br($projectInfo->bigdata['info-need']),
		$isEdit,
		'textarea'
	);

	$ret .= '</section><!-- project-info-desc -->';




	// ประเด็นปัญหาหลัก

	$stmt = 'SELECT
		  tg.`catid`, tg.`catparent`, tg.`name`
		, tr.`bigid`, tr.`flddata`, tg.`process`
		FROM %tag% tg
			LEFT JOIN %bigdata% tr ON tr.`keyname` = "project.info" AND tr.`keyid` = :tpid AND tr.`fldname` = CONCAT("mainissue-",tg.`catid`)
		WHERE tg.`taggroup` = "project:mainissue" AND tg.`process` = 1
		ORDER BY `catid` ASC';

	$mainIssueDbs=mydb::select($stmt,':tpid',$tpid);
	//$ret .= print_o($mainIssueDbs, '$mainIssueDbs');

	$ret .= '<section class="project-info-issue" id="project-info-issue">';
	$ret .= '<header class="header"><h3>ประเด็นปัญหาหลัก</h3></header>';

	foreach ($mainIssueDbs->items as $rs) {
		$ret .= '<abbr class="checkbox -block"><label>'
			.view::inlineedit(
				array(
					'group'=>'bigdata:project.info:'.$rs->catid,
					'fld' => 'mainissue-'.$rs->catid,
					'tr' => $rs->bigid,
					'value' => $rs->flddata,
					'removeempty'=>'yes',
				),
				$rs->catid.':'.$rs->name,
				$isEdit,
				'checkbox')
			.' </label></abbr>';
	}
	$ret .= '</section><!-- project-info-issue -->';



	// ประเด็นที่เกี่ยวข้อง

	$stmt = 'SELECT
		  tg.`catid`, tg.`catparent`, tg.`name`
		, tr.`bigid`, tr.`flddata`, tg.`process`
		FROM %tag% tg
			LEFT JOIN %bigdata% tr ON tr.`keyname` = "project.info" AND tr.`keyid` = :tpid AND tr.`fldname` = CONCAT("category-",tg.`catid`)
		WHERE tg.`taggroup` = "project:issue" AND tg.`process` = 2
		ORDER BY `catid` ASC';

	$issueDbs=mydb::select($stmt,':tpid',$tpid);
	//$ret .= print_o($issueDbs, '$issueDbs');

	$ret .= '<section class="project-info-issue" id="project-info-issue">';
	$ret .= '<header class="header"><h3>ประเด็นที่เกี่ยวข้อง</h3></header>';

	foreach ($issueDbs->items as $rs) {
		$ret .= '<abbr class="checkbox -block"><label>'
			.view::inlineedit(
				array(
					'group'=>'bigdata:project.info:'.$rs->catid,
					'fld' => 'category-'.$rs->catid,
					'tr' => $rs->bigid,
					'value' => $rs->flddata,
					'removeempty'=>'yes',
				),
				$rs->catid.':'.$rs->name,
				$isEdit,
				'checkbox')
			.' </label></abbr>';
	}
	$ret .= '</section><!-- project-info-issue -->';



	$ret .= '<section class="project-info-desc" id="project-info-desc">';
	$ret .= '<header class="header"><h3>องค์ความรู้หรือนวัตกรรมที่ใช้ในการดำเนินโครงงาน</h3></header>';
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

	$ret .= '</section><!-- project-info-desc -->';


	if ($isInnovationProject) {
		$stmt = 'SELECT
			  tg.`catid`, tg.`catparent`, tg.`name`
			, tr.`bigid`, tr.`flddata`, tg.`process`
			FROM %tag% tg
				LEFT JOIN %bigdata% tr ON tr.`keyname` = "project.info" AND tr.`keyid` = :tpid AND tr.`fldname` = "inno" AND tr.`fldref` = tg.`catid`
			WHERE tg.`taggroup` = "project:inno" AND tg.`process` = 1';
		$innoDbs=mydb::select($stmt,':tpid',$tpid);
		//$ret .= print_o($innoDbs, '$innoDbs');

		if ($innoDbs->_num_rows) {
			$optionsIssue = array();
			foreach ($innoDbs->items as $rs) {
				if (empty($rs->catparent)) {
					$optionsIssue[] = '<b style="display: block; padding: 4px 8px; font-size: 1.1em;">'.$rs->name.'</b>';
					foreach ($innoDbs->items as $innoItem) {
						if ($innoItem->catparent != $rs->catid) continue;
						if ($isEdit) {
							$optionsIssue[] = '<abbr class="checkbox -block"><label>'
								.view::inlineedit(
									array(
										'group'=>'bigdata:project.info:'.$innoItem->catid,
										'fld' => 'inno',
										'fldref' => $innoItem->catid,
										'tr' => $innoItem->bigid,
										'value' => $innoItem->flddata,
										'removeempty'=>'yes',
									),
									$innoItem->catid.':'.$innoItem->name,
									$isEdit,
									'checkbox')
								.' </label></abbr>';
						} else {
							if ($innoItem->trid) $optionsIssue[] = $innoItem->name;
						}
					}
				}
			}

			$ret .= '<section class="project-info-issue" id="project-info-issue">';
			$ret .= '<header class="header"><h3>ประเภทนวัตกรรม</h3></header>';
			$ret .= '<div class="project-info-issue-list" id="project-info-list">';
			//if ($issueDbs->_empty) $ret .= '<div class="-no-print">ยังไม่มีการสร้างรายการความสอดคล้องในระบบ</div>';
			$ret .= ($isEdit ? implode('', $optionsIssue) : implode(' , ', $optionsIssue));
			$ret .= '</div><!-- project-info-issue-list -->';
			$ret .= '</section><!-- project-info-issue -->';
		}
	}


	$ret .= '<section class="project-info-desc" id="project-info-desc">';
	$ret .= '<header class="header"><h3>รายละเอียด{tr:โครงการ}/หลักการและเหตุผล</h3></header>';
	$ret .= view::inlineedit(
		array(
			'group'=>'revision', 'tr' => $projectInfo->info->revid, 'fld'=>'body', 'ret'=>'nl2br', 'options' => '{placeholder: "ระบุรายละเอียดโครงการ / หลักการและเหตุผล"}', 'value'=>trim($projectInfo->info->body)
		),
		nl2br($projectInfo->info->body),
		$isEdit,
		'textarea'
	);


	$ret .= '</section><!-- project-info-desc -->';




	// Show tags
	// TODO: Query old tag from bigdata on keyup in input box
	$ret .= '<section class="project-info-tag" id="project-info-tag">';
	$ret .= '<header class="header"><h3>คำสำคัญเพื่อการค้นหา</h3></header>';
	$ret .= '<div>';

	$stmt = 'SELECT b.`bigid`, b.`flddata` `tagName` FROM %bigdata% b WHERE b.`keyid` = :keyid AND `keyname` = "project.info" AND b.`fldname` = "tag" ORDER BY CONVERT(`tagName` USING tis620) ASC';
	$dbs = mydb::select($stmt, ':keyid', $tpid);

	$ui = new Ui(NULL, 'ui-tag');
	foreach ($dbs->items as $rs) {
		$ui->add('<span>'.$rs->tagName.'</span>'.($isEdit ? '<nav class="nav -no-print"><a class="sg-action" href="'.url('project/'.$tpid.'/info/tag.remove/'.$rs->bigid).'" data-rel="notify" data-title="ลบคำสำคัญ" data-confirm="ต้องการลบคำสำคัญ กรุณายืนยัน?" data-done="remove:parent li"><i class="icon -material -gray">cancel</i></a></nav>' : ''));
	}

	$ret .= $ui->build();

	if ($isEdit) {
		$form = new Form(NULL, url('project/'.$tpid.'/info/tag.save'), NULL, 'sg-form -sg-flex -nowrap -no-print');
		$form->addData('rel', 'notify');
		$form->addData('done', 'load:#main:'.url('project/'.$tpid,array('mode'=>'edit')));
		$form->addField(
			'tagname',
			array(
				'type' => 'text',
				'class' => 'sg-autocomplete -fill',
				'placeholder' => 'ระบุคำสำคัญเพื่อการค้นหา แบ่งด้วยเครื่องหมาย , ไม่เกิน 100 อักษร',
				'maxlength' => '100',
				'attr' => array(
					'data-query' => url('project/api/tag')
				),
				'container'=>array('style' => 'flex:1 0 10%'),
			)
		);
		$form->addField('save',array('type'=>'button','value'=>'<i class="icon -material">add</i><span>เพิ่มคำค้น</span>'));
		$ret .= $form->build();
	}
	$ret .= '</div>';
	$ret .= '</section><!-- project-info-tag -->';



	// Show Valuation
	if (!$isInnovationProject) {
		$ret .= '<section class="project-info-value" id="project-info-value">';
		$ret .= '<header class="header"><h3>ประเมินคุณค่าโครงการ</h3></header>';

		$outputList = array(
			1 => 'เกิดความรู้ หรือ นวัตกรรมชุมชน',
			'เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
			'การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ (กายภาพ สังคม และเศรษฐกิจ)',
			'การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
			'เกิดกระบวนการชุมชน',
			'มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ',
		);

		$stmt = 'SELECT SUBSTR(`part`,3,1) `valueKey` FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "valuation" AND `rate1` = 1 GROUP BY `valueKey`; -- {key: "valueKey"}';
		$dbs = mydb::select($stmt, ':tpid', $tpid);

		$tables = new Table();
		$tables->addClass('project-valuation');
		$tables->thead = array('no'=>'','detail -fill'=>'คุณค่าที่เกิดขึ้น', 'result -center -nowrap'=>'ผลที่เกิดขึ้น','icons -c1' => '');
		$no = 0;

		foreach ($outputList as $value) {
			++$no;
			$hasValueation = isset($dbs->items[$no]) ? true : false;
			$tables->rows[] = array(
				$no,
				$value,
				$isEdit
					?
					'<a class="sg-action" href="'.url('project/'.$tpid.'/eval.valuation/edit', array('id'=>$no)).'" data-rel="box">' . ($hasValueation ? '<i class="icon -material -gray">edit</i>' : '<i class="icon -material -gray">add_circle_outline</i>').'</a>'
					:
					($hasValueation ? '<a class="sg-action" href="'.url('project/'.$tpid.'/eval.valuation', array('id'=>$no)).'" data-rel="box"><i class="icon -material -gray">find_in_page</i></a>' : ''),
			);
		}
		$ret .= $tables->build();
		$ret .= '</section><!-- project-info-value -->';
	}





	// Show photo
	$stmt = 'SELECT
		f.`fid`, f.`refid`, f.`type`, f.`tagname`, f.`file`, f.`title`
		FROM %topic_files% f
		WHERE f.`tpid` = :tpid AND f.`type` = "photo" AND f.`tagname` = "project,info"
		';
	$photoDbs = mydb::select($stmt, ':tpid', $tpid);

	$ret .= '<section class="project-info-photo" id="project-info-photo">';

	$ui = new Ui();

	if ($isEdit) {
		$ui->add('<form class="sg-upload -no-print" '
			. 'method="post" enctype="multipart/form-data" '
			. 'action="'.url('project/'.$tpid.'/info/photo.upload').'" '
			. 'data-rel="#project-info-photo-card'.'" data-append="li">'
			. '<input type="hidden" name="tagname" value="info" />'
			. '<input type="hidden" name="link" value="href" />'
			. '<input type="hidden" name="delete" value="none" />'
			. '<span class="btn -primary btn-success fileinput-button"><i class="icon -material">add_a_photo</i>'
			. '<span>ส่งภาพถ่าย</span>'
			. '<input type="file" name="photo[]" multiple="true" class="inline-upload -actionphoto" />'
			. '</span>'
			. '</form>'
		);
	}

	$ret .= '<header class="header"><h3>ภาพถ่าย</h3><nav class="nav -no-print">'.$ui->build().'</nav></header>';

	$photoUi = new Ui(NULL, 'ui-album');
	$photoUi->addId('project-info-photo-card');

	foreach ($photoDbs->items as $item) {
		$photoStrItem = '';
		$ui = new Ui('span');

		if ($item->type == 'photo') {
			//$ret.=print_o($item,'$item');
			$photo = model::get_photo_property($item->file);

			if ($isEdit) {
				//$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info/photo.delete/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="notify" data-done="remove:parent li"><i class="icon -material">cancel</i></a>');
			}

			$photo_alt = $item->title;

			$photoStrItem .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';

			$photoStrItem .= '<a class="sg-action" href="'.url('project/'.$tpid.'/info.photo/'.$item->fid).'" data-rel="box" data-width="840" data-height="80%" title="'.htmlspecialchars($photo_alt).'">';
			$photoStrItem .= '<img class="photoitem -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" width="100%" ';
			$photoStrItem .= ' />';
			$photoStrItem .= '</a>';


			$photoStrItem .= '<span>'.SG\getFirst($item->title,'คำอธิบายภาพ').'</span>';

			$photoUi->add($photoStrItem, '{id: "photo-'.$item->fid.'", class: "-hover-parent"}');

		} else if ($item->type == 'doc') {
			$docStr = '<a href="'.cfg('paper.upload.document.url').$item->file.'" title="'.htmlspecialchars($item->title).'" target="_blank">';
			$docStr .= '<img class="photoitem" src="http://img.softganz.com/icon/pdf-icon.png" width="80%" alt="'.$item->title.'" />';
			$docStr .= '</a>';

			if ($isEdit) {
				$ui->add(' <a class="sg-action" href="'.url('project/'.$tpid.'/docs.delete/'.$item->fid).'" title="ลบไฟล์นี้" data-title="ลบไฟล์" data-confirm="ยืนยันว่าจะลบไฟล์รายงานนี้จริง?"  data-rel="notify" data-done="remove:parent li"><i class="icon -cancel -gray"></i></a>');
			}
			$docStr .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';
			$photoUi->add($docStr, '{id: "photo-'.$item->fid.'", class: "-doc -hover-parent"}');
		} else if ($item->type == 'movie') {
			list($a,$youtubeId) = explode('?v=', $item->file);
			$docStr = '<iframe width="100%" height="100%" src="https://www.youtube.com/embed/'.$youtubeId.'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe><div class="detail"><span>'.$item->title.'</span><span><a href="'.$item->file.'" target="_blank">View on YouTube</a></span></div>';

			if ($isEdit) {
				$ui->add(' <a class="sg-action" href="'.url('project/'.$tpid.'/info/vdo.delete/'.$item->fid).'" title="ลบ Video" data-title="ลบ Video" data-confirm="ยืนยันว่าจะลบ Video นี้จริง?"  data-rel="notify" data-done="remove:parent li"><i class="icon -cancel -gray"></i></a>');
			}
			$docStr .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';
			$photoUi->add($docStr, '{id: "photo-'.$item->fid.'", class: "-vdo -hover-parent"}');
		}
	}

	$ret .= $photoUi->build(true);

	$ret .= '</section><!-- project-info-photo -->';



	$stmt = 'SELECT
		f.`fid`, f.`refid`, f.`type`, f.`tagname`, f.`file`, f.`title`
		FROM %topic_files% f
		WHERE f.`tpid` = :tpid AND f.`type` = "movie"
		';
	$vdoDbs = mydb::select($stmt, ':tpid', $tpid);

	$ret .= '<section class="project-info-vdo" id="project-info-vdo">';

	$ui = new Ui();
	if ($isEdit) {
		$ui->add('<a class="sg-action btn -primary" href="'.url('project/'.$tpid.'/info.vdo.form', array('tag' => 'project,info')).'" data-rel="box" data-width="640"><i class="icon -material">video_call</i><span>ส่งวีดิโอ</span></a>');
	}
	$ret .= '<header class="header"><h3>วีดิโอ</h3><nav class="nav -no-print">'.$ui->build().'</nav></header>';

	$cardUi = new Ui(NULL, 'ui-album');
	$cardUi->addId('project-info-vdo-card');

	foreach ($vdoDbs->items as $item) {
		$ui = new Ui('span');

		list($a,$youtubeId) = explode('?v=', $item->file);

		$cardStr = '<div class="vdoframe"><iframe width="100%" height="100%" src="https://www.youtube.com/embed/'.$youtubeId.'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>'
			. '<div class="detail">'.$item->title.'</div>'
			. '<nav class="nav -card"><a class="btn -link" href="'.$item->file.'" target="_blank">View on YouTube</a></nav>';

		if ($isEdit) {
			$ui->add(' <a class="sg-action" href="'.url('project/'.$tpid.'/info/vdo.delete/'.$item->fid).'" title="ลบ Video" data-title="ลบ Video" data-confirm="ยืนยันว่าจะลบ Video นี้จริง?"  data-rel="notify" data-done="remove:parent li"><i class="icon -cancel -gray"></i></a>');
		}
		$cardStr .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';
		$cardUi->add($cardStr, '{class: "-hover-parent"}');

	}

	$ret .= $cardUi->build(true);

	$ret .= '</section><!-- project-info-vdo -->';




	$ret .='<section id="project-docs" class="project-docs">'._NL;
	$ret .= '<header class="header"><h3>ไฟล์เอกสาร</h3></header>';
	$ret .= R::PageWidget('project.info.docs', [$projectInfo])->build();
	$ret .= '</section><!-- project-docs -->'._NL._NL;


	$stmt = 'SELECT d.*, t.`title` FROM %project_dev% d LEFT JOIN %topic% t USING(`tpid`) WHERE t.`thread` = :tpid';
	$dbs = mydb::select($stmt, ':tpid', $tpid);

	$ret .='<section id="project-docs" class="project-extend">'._NL;
	$ret .= '<header class="header"><h3>โครงการขยายผล</h3></header>';
	$ui = new Ui();
	$no = 0;
	foreach ($dbs->items as $rs) {
		$ui->add(++$no.'. <a href="'.url('project/proposal/'.$rs->tpid).'">'.$rs->title.'</a>');
	}
	$ret .= $ui->build();
	//$ret .= print_o($dbs,'$dbs');
	$ret .= '</section>';





	/*

	$stmt = 'SELECT
		`trid`, `formid`, `part` `innoName`, `text1` `detail`
		, u.`username`, u.`name` `posterName`, tr.`created`
		FROM %project_tr% tr
			LEFT JOIN %users% u USING(`uid`)
		WHERE `tpid` = :tpid AND `formid` = "valuation" ';

	$dbs = mydb::select($stmt, ':tpid', $tpid);

	$stmt = 'SELECT
		f.`fid`, f.`refid`, f.`type`, f.`tagname`, f.`file`, f.`title`
		FROM %project_tr% tr
			RIGHT JOIN %topic_files% f ON f.`tpid` = tr.`tpid` AND f.`tagname` = "project,valuation" AND f.`refid` = tr.`trid`
		WHERE tr.`tpid` = :tpid AND tr.`formid` = "valuation"
		-- {group: "refid", key: "fid"} ';
	$photoDbs = mydb::select($stmt, ':tpid', $tpid);

	//$ret .= print_o($photoDbs,'$photoDbs');

	$cardUi = new Ui('div','ui-card');

	foreach ($dbs->items as $rs) {
		$cardStr = '';
		$headerUi = new Ui();
		$dropUi = new Ui();

		if ($isEdit) {
			$dropUi->add('<a href=""><i class="icon -material">delete</i><span>{tr:DELETE}</span></a>');
		}
		if ($dropUi->count()) $headerUi->add(sg_dropbox($dropUi->build()));

		$photoUi = new Ui(NULL, 'ui-album');
		$photoUi->addId('project-actphoto-'.$rs->trid);
		//$photoUi->add('<a class="btn " href=""><i class="icon -material">add_a_photo</i><span>อัพโหลดภาพ</span></a>');

		foreach ($photoDbs->items[$rs->trid] as $item) {
			$photoStrItem = '';
			$ui = new Ui('span');

			if ($item->type == 'photo') {
				//$ret.=print_o($item,'$item');
				$photo = model::get_photo_property($item->file);

				if ($isEdit) {
					$ui->add('<a class="sg-action" href="'.url('project/'.$tpid.'/info/photo.delete/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="notify" data-done="remove:parent li"><i class="icon -material">cancel</i></a>');
				}

				$photo_alt = $item->title;

				$photoStrItem .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';

				$photoStrItem .= '<a class="sg-action" data-group="photo'.$actionId.'" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
				$photoStrItem .= '<img class="photoitem photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" width="100%" ';
				$photoStrItem .= ' />';
				$photoStrItem .= '</a>';


				if ($isEdit) {
					$photoStrItem .= view::inlineedit(array('group' => 'photo', 'fld' => 'title', 'tr' => $item->fid, 'class' => '-fill'), $item->title, $isEdit, 'text');
				} else {
					$photoStrItem .= '<span>'.$item->title.'</span>';
				}

				$photoUi->add($photoStrItem, '{class: "-hover-parent"}');

			} else if ($item->type == 'doc') {
				$docStr = '<a href="'.cfg('paper.upload.document.url').$item->file.'" title="'.htmlspecialchars($item->title).'" target="_blank">';
				$docStr .= '<img class="photoitem" src="http://img.softganz.com/icon/pdf-icon.png" width="80%" alt="'.$item->title.'" />';
				$docStr .= '</a>';

				if ($isEdit) {
					$ui->add(' <a class="sg-action" href="'.url('project/'.$tpid.'/photo.del/'.$item->fid).'" title="ลบไฟล์นี้" data-title="ลบไฟล์" data-confirm="ยืนยันว่าจะลบไฟล์รายงานนี้จริง?"  data-rel="notify" data-done="remove:parent li"><i class="icon -cancel -gray"></i></a>');
				}
				$docStr .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';
				$photoUi->add($docStr, '{class: "-doc -hover-parent"}');
			} else if ($item->type == 'movie') {
				list($a,$youtubeId) = explode('?v=', $item->file);
				$docStr = '<iframe width="100%" height="100%" src="https://www.youtube.com/embed/'.$youtubeId.'" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe><div class="detail"><span>'.$item->title.'</span><span><a href="'.$item->file.'" target="_blank">View on YouTube</a></span></div>';

				if ($isEdit) {
					$ui->add(' <a class="sg-action" href="'.url('project/'.$tpid.'/info/vdo.delete/'.$item->fid).'" title="ลบ Video" data-title="ลบ Video" data-confirm="ยืนยันว่าจะลบ Video นี้จริง?"  data-rel="notify" data-done="remove:parent li"><i class="icon -cancel -gray"></i></a>');
				}
				$docStr .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';
				$photoUi->add($docStr, '{class: "-vdo -hover-parent"}');
			}
		}


		$cardStr .= '<div class="header">'
				. '<span>'
				. $posterUrl
				. '<img class="poster-photo" src="'.model::user_photo($rs->username).'" width="32" height="32" alt="" />'
				. '<span class="poster-name">'.$rs->posterName.'</a> '
				. '</span>'
				. '</span>'
				. '<span class="timestamp"> เมื่อ '
				. sg_date($rs->created,'ว ดด ปปปป H:i').' น.'
				. '</span>'._NL
				. '<nav class="nav -header -sg-text-right">'
				. $headerUi->build()
				. '</nav>'
				. '</div><!-- header -->'._NL;

		$cardStr .= '<div class="header"><h4>นวัตกรรม : '.$innoList[$rs->innoName].'</h4></div>';
		$cardStr .= $photoUi->build(true);

		if ($isEdit) {
			$cardStr .= '<nav class="nav -card -sg-text-center"><ul><li>'
						. '<form class="sg-upload -no-print" '
						. 'method="post" enctype="multipart/form-data" '
						. 'action="'.url('project/'.$tpid.'/info/photo.upload/'.$rs->trid).'" '
						. 'data-rel="#project-actphoto-'.$rs->trid.'" data-append="li">'
						. '<input type="hidden" name="tagname" value="valuation" />'
						. '<span class="btn btn-success fileinput-button"><i class="icon -material">add_a_photo</i>'
						. '<span>ส่งภาพถ่ายหรือไฟล์รายงาน</span>'
						. '<input type="file" name="photo[]" multiple="true" class="inline-upload -actionphoto" />'
						. '</span>'
						. '</form>'
						. '</li>'
						. '<li><a class="sg-action btn" href="'.url('project/'.$tpid.'/info.vdo.form/'.$rs->trid, array('tag' => 'project,valuation')).'" data-rel="box" data-width="640"><i class="icon -material">video_call</i><span>ส่งวีดิโอ</span></a></li>'
						. '</nav>'._NL;
		}

		$cardStr .= '<div class="detail">';
		$cardStr .= view::inlineedit(array('group'=>$rs->formid.':'.$rs->part,'fld'=>'text1','tr'=>$rs->trid,'ret'=>'nl2br', 'value'=>trim($rs->detail)), nl2br($rs->detail),$isEdit,'textarea');
		//$cardStr .= nl2br($rs->detail);
		$cardStr .= '</div>';
		//$cardStr .= print_o($rs,'$rs');
		$cardUi->add($cardStr);
	}



	$ret .= '<section class="project-info-output" id="project-info-output">';
	$ret .= '<h3>ผลลัพธ์ / นวัตกรรม</h3>';
	$ret .= '<div class="project-info-output-list" id="project-info-output-list">';
	$ret .= $cardUi->build();
	$ret .= '</div><!-- project-info-output-list -->';

	if ($isEdit) {
		$ret .= '<nav class="nav -page -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/'.$tpid.'/info.inno.form').'" data-rel="box" data-width="640"><i class="icon -material">add</i><span>บันทึกผลลัพธ์ / นวัตกรรม</span></a></nav>';
	}
	$ret .= '</section><!-- project-info-output -->';

	*/


	// Section :: Project Creator
	$ret.='<section style="margin: 16px 0; padding: 8px;">นำเข้าสู่ระบบโดย <a class="sg-action" href="'.url('project/'.$tpid.'/info.u/'.$projectInfo->info->uid).'" data-rel="box" data-width="640" title="'.htmlspecialchars($projectInfo->info->ownerName).'"><img src="'.model::user_photo($projectInfo->info->username).'" width="30" height="30" alt="'.htmlspecialchars($projectInfo->info->ownerName).'" style="border-radius: 50%; vertical-align: middle;" /> '.$projectInfo->info->ownerName.'</a> เมื่อวันที่ '.sg_date($projectInfo->info->created,'ว ดดด ปปปป H:i').' น.</section>';



	//head('gmap.js','<script type="text/javascript" src="/js/gmaps.js"></script>');



	// Section :: Social share
	if (_ON_HOST && in_array($projectInfo->info->type,explode(',',cfg('social.share.type'))) && !is_home() && $projectInfo->property->option->social) {
		$ret .= view::social(url('project/'.$tpid));
	}

	//$ret .= '</div><!-- project-info -->'._NL._NL;
	//$ret .= print_o($projectInfo,'$projectInfo');

	$ret .= '<style type="text/css">
	#cboxContent .btn-floating {display: none;}
	.ui-album .nav {display: none;}
	.ui-album>.ui-item>span {height: 1.6em; padding: 0 8px; overflow: hidden; position: absolute; bottom: 0; left: 0; right: 0; background-color: #fff; opacity: 0.5; line-height: 1.6em;}
	</style>';

	return $ret;
}
?>
