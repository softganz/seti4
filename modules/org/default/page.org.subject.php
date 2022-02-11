<?php
function org_subject($self, $orgId = NULL, $action = NULL, $tranId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	$ret='';

	$isEdit = $orgInfo->RIGHT & (_IS_ADMIN | _IS_OWNER | _IS_OFFICER);


	if ($action) {
		switch ($action) {
			case 'edit':
				//if ($isEdit) $ret.=__project_fund_board_home($fundInfo,$tranId);
				break;

			case 'add':
				if ($isEdit) $ret.=__org_subject_add($orgInfo);
				//location('org/'.$orgInfo->orgid.'/subject');
				break;

			case 'delete':
				if ($isEdit && $tranId) $ret.=__org_subject_delete($orgInfo,$tranId);
				break;

			default:

				break;
		}
		return $ret;
	}

	$ret.='<div id="org-subject">'.__org_subject_list($orgInfo).'</div>';

	return $ret;
}

function __org_subject_list($orgInfo) {
	$isEdit = $orgInfo->RIGHT & (_IS_ADMIN | _IS_OWNER | _IS_OFFICER);

	$stmt = 'SELECT * FROM %org_subject% s LEFT JOIN %tag% t ON t.`taggroup`="subject" AND t.`catid`=s.`subject` WHERE s.`orgid`=:orgid';

	$dbs = mydb::select($stmt,':orgid',$orgInfo->orgid);

	$ret .= '<ul id="org-subject" class="org-subject">';
	foreach ($dbs->items as $rs) {
		$menu = $isEdit ? '<a class="sg-action hover" href="'.url('org/'.$rs->orgid.'/subject/delete/'.$rs->subject).'" data-rel="none" data-removeparent="li" data-confirm="ลบรายการนี้ กรุณายืนยัน?"><i class="icon -remove -gray"></i></a>' : '';
		$ret .= '<li class="ui-item hover">'
				. $rs->name
				. $menu
				. '</li>';
	}

	if ($isEdit) $ret .= '<li>'.__org_subject_add($orgInfo).'</li>';
	//if ($isEdit) $ret.='<li><a class="sg-action" href="'.url('org/'.$orgInfo->orgid.'/subject/add').'" data-rel="box" data-width="300"><i class="icon -add"></i></a></li>';
	$ret .= '</ul>';
	//$ret.=print_o($orgInfo,'$orgInfo');
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}

function __org_subject_add($orgInfo) {
	//$ret='<div class="toolbar"><h3 class="title">'.$orgInfo->name.'</h3></div>';

	// Save
	if ($orgInfo->orgid && post('subject')) {
		$stmt='INSERT INTO %org_subject% (`orgid`,`subject`) VALUES (:orgid,:subject) ON DUPLICATE KEY UPDATE `subject`=:subject';
		mydb::query($stmt,':orgid',$orgInfo->orgid, ':subject',post('subject'));
		//$ret .= mydb()->_query;
		//$ret .= __org_subject_list($orgInfo);
		return $ret;
	}

	// Show subject form
	$orgSubjectList = R::Model('category.get','subject','catid', (object) array('result'=>'group', 'condition'=>'tg.`catid` NOT IN ('.($orgInfo->subject ? implode(',',array_keys($orgInfo->subject)) : '0').')'));

	$form = new Form(NULL, url('org/'.$orgInfo->orgid.'/subject/add'),'org-subject-edit','sg-form -inlineitem');
	$form->addData('checkValid',true);
	$form->addData('rel','replace:#org-subject');
	$form->addData('ret',url('org/'.$orgInfo->orgid.'/subject'));
	$form->addData('complete','closebox');

	$form->addField(
					'subject',
					array(
						//'label' => 'ประเด็นการทำงานขององค์กร:',
						'type' => 'select',
						'require' => true,
						'options' => array('== เลือก ==')+$orgSubjectList,
						'container' => array('class'=>'-inline'),
					)
				);

	$form->addField(
					'save',
					array(
						'type' => 'button',
						'value' => '<i class="icon -add"></i><span class="-hidden">{tr:SAVE}</span>',
						'class' => '-link -circle24',
						//'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="close">ยกเลิก</a>',
						'container' => array('class'=>'-inline'),
					)
				);

	$ret .= $form->build();

	//$ret.=print_o(post(),'post()');
	return $ret;
}

function __org_subject_add_box($orgInfo) {
	//$ret='<div class="toolbar"><h3 class="title">'.$orgInfo->name.'</h3></div>';

	// Save
	if ($orgInfo->orgid && post('subject')) {
		$stmt='INSERT INTO %org_subject% (`orgid`,`subject`) VALUES (:orgid,:subject) ON DUPLICATE KEY UPDATE `subject`=:subject';
		mydb::query($stmt,':orgid',$orgInfo->orgid, ':subject',post('subject'));
		//$ret .= mydb()->_query;
		//$ret .= __org_subject_list($orgInfo);
		return $ret;
	}

	// Show subject form
	$orgSubjectList = model::get_category_by_group('subject','catid');

	$form = new Form(NULL, url('org/'.$orgInfo->orgid.'/subject/add'),'org-subject-edit','sg-form');
	$form->addData('checkValid',true);
	$form->addData('rel','replace:#org-subject');
	$form->addData('ret',url('org/'.$orgInfo->orgid.'/subject'));
	$form->addData('complete','closebox');

	$form->addField(
					'subject',
					array(
						//'label' => 'ประเด็นการทำงานขององค์กร:',
						'type' => 'select',
						'require' => true,
						'options' => $orgSubjectList,
					)
				);

	$form->addField(
					'save',
					array(
						'type' => 'button',
						'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
						'class' => '-circle24',
						//'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="close">ยกเลิก</a>',
						'container' => array('class'=>'-sg-text-right'),
					)
				);

	$ret .= $form->build();

	//$ret.=print_o(post(),'post()');
	return $ret;
}

function __org_subject_delete($orgInfo,$tranId) {
	$stmt='DELETE FROM %org_subject% WHERE `orgid` = :orgid AND `subject` = :subject LIMIT 1';
	mydb::query($stmt,':orgid',$orgInfo->orgid,':subject',$tranId);
	//debugMsg(mydb()->_query);
}
?>