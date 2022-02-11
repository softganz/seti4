<?php
/**
* Project :: Check My Bank Account Information
* Created 2021-02-25
* Modify  2021-02-25
*
* @param Object $self
* @param Object $projectInfo
* @return String
*
* @usage project/{id}/info.bank.check
*/

$debug = true;

function project_info_bank_check($self, $projectInfo) {
	$getMode = post('mode');
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$isEdit = $projectInfo->right->isAdmin || $projectInfo->right->isOwner;

	if (!$isEdit) return message('error', 'ขออภัย ท่านไม่สามารถใช้งานเมนูนี้ได้');

	$ret = '';

	$data = _project_info_bank_check_data($projectInfo->projectId);

	// Create new person record on no record
	if ($data->tpid && empty($data->psnid)) {
		R::Model('project.1t1u.person.create', $data);

		// Get data again
		$data = _project_info_bank_check_data($projectInfo->projectId);
	}

	if ($getMode == 'correct') {
		$ret .= 'บันทึกข้อมูลเรียบร้อย';
		$bigData = new stdClass();
		unset($data->status, $data->bankData);

		$bigData->bigid = mydb::select(
			'SELECT * FROM %bigdata% WHERE `keyname` = "project.info" AND `fldname` = "bankcheck" AND `keyid` = :projectId LIMIT 1',
			':projectId', $projectId
		)->bigid;

		$bigData->keyname = 'project.info';
		$bigData->fldname = 'bankcheck';
		$bigData->keyid = $projectId;
		$bigData->fldref = 9;
		$bigData->fldtype = 'json';
		$bigData->flddata = SG\json_encode($data);
		$bigData->created = $bigData->modified = date('U');
		$bigData->ucreated = $bigData->umodified = i()->uid;

		mydb::query(
			'INSERT INTO %bigdata%
			(`bigid`, `keyname`, `keyid`, `fldname`, `fldtype`, `fldref`, `flddata`, `created`, `ucreated`)
			VALUES
			(:bigid, :keyname, :keyid, :fldname, :fldtype, :fldref, :flddata, :created, :ucreated)
			ON DUPLICATE KEY UPDATE
			`flddata` = :flddata
			, `fldref` = 1',
			$bigData
		);
		//$ret .= mydb()->_query;
		//$ret .= print_o($bigData, '$bigData');
		return $ret;
	} else if (post('data')) {
		$post = (Object) post('data');

		$ret .= 'บันทึกข้อมูลเรียบร้อย';
		$bigData = new stdClass();
		$bigData->bigid = mydb::select(
			'SELECT * FROM %bigdata% WHERE `keyname` = "project.info" AND `fldname` = "bankcheck" AND `keyid` = :projectId LIMIT 1',
			':projectId', $projectId
		)->bigid;

		$bigData->keyname = 'project.info';
		$bigData->fldname = 'bankcheck';
		$bigData->keyid = $projectId;
		$bigData->fldref = 1;
		$bigData->fldtype = 'json';
		$bigData->flddata = SG\json_encode($post);
		$bigData->created = $bigData->modified = date('U');
		$bigData->ucreated = $bigData->umodified = i()->uid;

		mydb::query(
			'INSERT INTO %bigdata%
			(`bigid`, `keyname`, `keyid`, `fldname`, `fldtype`, `fldref`, `flddata`, `created`, `ucreated`)
			VALUES
			(:bigid, :keyname, :keyid, :fldname, :fldtype, :fldref, :flddata, :created, :ucreated)
			ON DUPLICATE KEY UPDATE
			`flddata` = :flddata
			, `fldref` = 1',
			$bigData
		);
		//$ret .= mydb()->_query.'<br />';

		$post->projectId = $projectId;
		$post->psnid = $data->psnid;
		list($post->name, $post->lname) = sg::explode_name(' ', $post->fullname);

		mydb::query(
			'UPDATE %db_person% SET
			`prename` = :prename, `name` = :name, `lname` = :lname
			, `cid` = :cid
			, `phone` = :phone, `email` = :email
			, `graduated` = :graduated, `faculty` = :faculty
			WHERE `psnid` = :psnid',
			$post
		);
		//$ret .= mydb()->_query.'<br />';

		mydb::query(
			'UPDATE %project%
			SET `bankaccount` = :bankaccount, `bankno` = :bankno, `bankname` = :bankname
			WHERE `tpid` = :projectId',
			$post
		);
		//$ret .= mydb()->_query.'<br />';

		//$ret .= print_o($bigData, '$bigData');
		//$ret .= print_o($post, '$post');
		return $ret;
	}

	$ret .= '<header class="header -box"><h3>'.$data->projectTitle.'</h3><nav class="nav" style="margin-right: 32px;"><ul class="ui-action"><li class="ui-item"><a class="sg-action btn -primary -fill" href="'.url('project/'.$projectId.'/info.bank.check', array('mode' => 'edit')).'" data-rel="box"><i class="icon -material">edit</i><span>แก้ไขข้อมูล</span></a></li></ul></nav></header>';

	$form = new Form('data', url(q()), 'edit-child', 'sg-form');
	$form->addData('checkValid', true);
	if ($getMode != 'edit') $form->addConfig('readonly', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load:#project-app-message');

	$form->addText('<section><h3>ข้อมูลบัญชีธนาคาร</h3>');

	$form->addField(
		'bankaccount',
		array(
			'type' => 'text',
			'label' => 'ชื่อบัญชี',
			'class' => '-fill',
			'require' => true,
			'value' => $data->bankaccount,
			'placeholder' => 'ชื่อ นามสกุล',
		)
	);

	$form->addField(
		'bankno',
		array(
			'type' => 'text',
			'label' => 'หมายเลขบัญชี <b style="color:red">(พร้อมเพย์ที่ผูกกับหมายเลขบัตรประชาชน)</b>',
			'class' => '-fill',
			'require' => true,
			'maxlength' => 13,
			'value' => htmlspecialchars($data->bankno),
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
			'value' => htmlspecialchars($data->bankname),
		)
	);

	$form->addText('</section>');

	$form->addText('<section><h3>ข้อมูลส่วนบุคคล</h3>');

	$form->addField(
		'childType',
		array(
			'type' => 'radio',
			'label' => 'ประเภทการจ้างงาน:',
			'class' => 'require',
			'require' => true,
			'options' => array(
				_PROJECT_OWNERTYPE_GRADUATE => 'บัณฑิต',
				_PROJECT_OWNERTYPE_STUDENT => 'นักศึกษา',
				_PROJECT_OWNERTYPE_PEOPLE => 'ประชาชน'
			),
			'value' => $data->ownertype,
		)
	);

	$form->addField(
		'prename',
		array(
			'type' => 'text',
			'label' => 'คำนำหน้านาม',
			'class' => '-fill',
			'require' => true,
			'value' => $data->prename,
			'placeholder' => 'เช่น นาย',
		)
	);

	$form->addField(
		'fullname',
		array(
			'type' => 'text',
			'label' => 'ชื่อ-นามสกุลจริง',
			'class' => '-fill',
			'require' => true,
			'value' => $data->fullname,
			'placeholder' => 'ชื่อ นามสกุล',
		)
	);

	$form->addField(
		'phone',
		array(
			'type' => 'text',
			'label' => 'หมายเลขโทรศัพท์',
			'class' => '-fill',
			'require' => true,
			'maxlength' => 10,
			'value' => htmlspecialchars($data->phone),
			'placeholder' => 'ระบุเบอร์โทร 10 หลัก',
		)
	);

	$form->addField(
		'cid',
		array(
			'type' => 'text',
			'label' => 'หมายเลขประจำตัว 13 หลัก',
			'class' => '-fill',
			'require' => true,
			'maxlength' => 13,
			'value' => htmlspecialchars($data->cid),
			'placeholder' => 'ระบุหมายเลข 13 หลัก',
		)
	);

	$form->addField(
		'email',
		array(
			'type' => 'text',
			'label' => 'อีเมล์',
			'class' => '-fill',
			'require' => true,
			'maxlength' => 40,
			'value' => htmlspecialchars($data->email),
		)
	);

	$form->addField(
		'faculty',
		array(
			'type' => 'text',
			'label' => 'คณะที่ศึกษา/จบการศึกษา',
			'class' => '-fill',
			'maxlength' => 100,
			'value' => htmlspecialchars($data->faculty),
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
			'value' => htmlspecialchars($data->graduated),
			'placeholder' => 'ระบุชื่อมหาวิทยาลัย/วิทยาลัย/โรงเรียน',
		)
	);

	$form->addText('</section>');

	if ($getMode == 'edit') {
		$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'container' => '{class: "-sg-text-right"}',
			)
		);
	} else {
		$form->addText('<nav class="nav -page" style="padding: 16px 8px;"><a class="sg-action btn -primary -fill" href="'.url('project/'.$projectId.'/info.bank.check', array('mode' => 'edit')).'" data-rel="box"><i class="icon -material">edit</i><span>แก้ไขข้อมูล</span></a><br /><a class="sg-action btn -fill" href="'.url('project/'.$projectId.'/info.bank.check', array('mode' => 'correct')).'" data-rel="notify" data-done="reload"><i class="icon -material">done_all</i><span>ยืนยันข้อมูลถูกต้อง</span></a></nav>');
	}
	$ret .= $form->build();

	$ret .= '<style type="text/css">
	.form>section {box-shadow: 0 0 0 2px #ddd; margin: 4px 4px 32px 4px; background-color: #fff;}
	.form>section>h3 {padding: 8px; background-color: #eee;}
	.form *[readonly] {box-shadow: 0 0 0 1px #f5f5f5; color: #999;}
	</style>';

	head('<script type="text/javascript">
	/*
	function onWebViewComplete() {
		console.log("CALL onWebViewComplete FROM WEBVIEW")
		var options = {refreshOnBack: false}
		return options
	}
	*/
	function onWebViewBack() {
		var options = {processDomOnResume: "#project-app-message"}
		return options
	}
	</script>');
	//$ret .= print_o(post(), 'post()');
	//$ret .= print_o($data, '$data');
	return $ret;
}

function _project_info_bank_check_data($projectId) {
	$data = mydb::select(
		'SELECT
			p.`tpid`, t.`revid`, t.`uid`, t.`title` `projectTitle`
			, p.`ownertype`
			, p.`bankaccount`, p.`bankno`, p.`bankname`
			, b.`fldref` `status`, b.`flddata` `bankData`
			, pn.`psnid`, pn.`prename`, CONCAT(pn.`name`, " ", pn.`lname`) `fullname`, pn.`cid`, pn.`phone`
			, pn.`graduated`, pn.`faculty`
			, IFNULL(pn.`email`, u.`email`) `email`
			, tp.`tpid` `parentProjectId`
			, tp.`uid` `parentUid`
			, tp.`title` `parentTitle`
			, rev.`property` `topicProperty`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %topic_revisions% rev ON rev.`tpid` = t.`tpid` AND rev.`revid` = t.`revid`
			LEFT JOIN %users% u ON u.`uid` = t.`uid`
			LEFT JOIN %db_person% pn ON pn.`userid` = t.`uid`
			LEFT JOIN %topic% tp ON tp.`tpid` = t.`parent`
			LEFT JOIN %bigdata% b ON b.`keyname` = "project.info" AND b.`keyid` = p.`tpid`
		WHERE t.`tpid` = :tpid AND p.`ownertype` IN ( :ownerType )
		-- AND b.`fldref` IS NULL
		LIMIT 1',
		':tpid', $projectId,
		':ownerType', 'SET-STRING:'.implode(',', array(_PROJECT_OWNERTYPE_GRADUATE, _PROJECT_OWNERTYPE_STUDENT, _PROJECT_OWNERTYPE_PEOPLE))
	);

	$data = mydb::clearprop($data);

	// debugMsg(mydb()->_query);
	// debugMsg($data, '$data');
	return $data;
}
?>