<?php
/**
* Project :: Child Bank Edit
* Created 2021-02-28
* Modify  2021-02-28
*
* @param Object $self
* @param Object $projectInfo
* @return String
*
* @usage project/{id}/info.child.bank.edit
*/

$debug = true;

function project_info_child_bank_edit($self, $projectInfo, $childId) {
	// Data Model
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$isEdit = $projectInfo->right->isAdmin || $projectInfo->right->isOwner;

	if (!$isEdit) return message('error', 'Access Denied');

	$childInfo = R::Model('project.get', $childId);

	if ($childInfo->orgid != $projectInfo->orgid) return message('error', 'ขออภัย ไม่ใช่โครงการภายใต้ความรับผิดชอบของท่าน');

	$data = (Object) post('data');
	if ($data->psnid) {
		$ret = 'บันทึกเรียบร้อย';
		$data->projectId = $childId;

		mydb::query(
			'UPDATE %db_person% SET
			`cid` = :cid
			, `graduated` = :graduated
			, `faculty` = :faculty
			, `phone` = :phone
			WHERE `psnid` = :psnid
			LIMIT 1
			',
			$data
		);
		//$ret .= mydb()->_query;

		mydb::query(
			'UPDATE %project% SET
			`bankaccount` = :bankaccount
			, `bankno` = :bankno
			, `bankname` = :bankname
			WHERE `tpid` = :projectId
			LIMIT 1
			',
			$data
		);
		//$ret .= mydb()->_query;

		return $ret;
	}

	mydb::where('p.`tpid` = :projectId', ':projectId', $childId);

	$stmt = 'SELECT
		  p.`tpid` `projectId`, t.`title` `employeeName`
		, p.`bankaccount`, p.`bankno`, p.`bankname`
		, pn.`psnid`, pn.`name`, pn.`lname`, pn.`cid`
		, pn.`phone`
		, pn.`graduated`, pn.`faculty`
		, t.`uid`
		, tp.`title` `parentTitle`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_person% pn ON pn.`userid` = t.`uid`
			LEFT JOIN %topic% tp ON tp.`tpid` = t.`parent`
		%WHERE%
		LIMIT 1
		';

	$personInfo = mydb::select($stmt);


	// View Model
	$toolbar = new Toolbar($self, $projectInfo->title);
	$headerNav = new Ui();
	$headerNav->config('container', '{tag: "nav", class: "nav"}');
	$headerNav->add('');

	$ret = '<header class="header">'._HEADER_BACK.'<h3>'.$personInfo->employeeName.'</h3>'.$headerNav->build().'</header>';

	$form = new Form('data', url('project/'.$projectId.'/info.child.bank.edit/'.$childId), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close');

	$form->addField('psnid', array('type' => 'hidden', 'value' => $personInfo->psnid));

	$form->addField(
		'bankaccount',
		array(
			'type' => 'text',
			'label' => 'ชื่อบัญชี',
			'class' => '-fill',
			'require' => true,
			'value' => $personInfo->bankaccount,
			'placeholder' => 'ชื่อบัญชี',
		)
	);

	$form->addField(
		'bankno',
		array(
			'type' => 'text',
			'label' => 'หมายเลขบัญชี <b style="color:red">(ที่ผูกพร้อมเพย์เลข 13 หลัก)</b>',
			'class' => '-fill',
			'require' => true,
			'maxlength' => 13,
			'value' => htmlspecialchars($personInfo->bankno),
		)
	);

	$form->addField(
		'bankname',
		array(
			'type' => 'text',
			'label' => 'ชื่อธนาคาร',
			'class' => '-fill',
			'require' => true,
			'maxlength' => 50,
			'value' => htmlspecialchars($personInfo->bankname),
		)
	);

	$form->addField(
		'cid',
		array(
			'type' => 'text',
			'label' => 'หมายเลขประจำตัว 13 หลัก',
			'class' => '-fill',
			'maxlength' => 13,
			'value' => $personInfo->cid,
		)
	);

	$form->addField(
		'phone',
		array(
			'type' => 'text',
			'label' => 'โทรศัพท์',
			'class' => '-fill',
			'maxlength' => 10,
			'value' => $personInfo->phone,
		)
	);

	$form->addField(
		'faculty',
		array(
			'type' => 'text',
			'label' => 'คณะที่ศึกษา/จบการศึกษา',
			'class' => '-fill',
			'maxlength' => 100,
			'value' => htmlspecialchars($personInfo->faculty),
			'placeholder' => 'ระบุคณะ/ภาควิชา/สาขา',
		)
	);

	$form->addField(
		'graduated',
		array(
			'type' => 'text',
			'label' => 'สถานศึกษาที่กำลังศึกษา/จบการศึกษา',
			'class' => '-fill',
			'maxlength' => 100,
			'value' => htmlspecialchars($personInfo->graduated),
			'placeholder' => 'ระบุชื่อมหาวิทยาลัย/วิทยาลัย/โรงเรียน',
			'posttext' => '<span class="input-append"><a class="btn -link" href="javascript:void(0)" onClick=\'sgSwapValue("#edit-data-graduated", "#edit-data-faculty"); return false;\'><i class="icon -material">swap_vertical_circle</i></a></span>',
			'container' => '{class: "-group"}',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	$ret .= '<script type="text/javascript">
	function sgSwapValue(id1, id2) {
		let id1Value = $(id1).val();
		$(id1).val($(id2).val())
		$(id2).val(id1Value)
	}
	</script>';
	//$ret .= print_o($personInfo, '$personInfo');
	return $ret;
}
?>