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

function project_info_address_check($self, $projectInfo) {
	// Data Model
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

	if (post('data')) {
		$post = (Object) post('data');

		$ret .= 'บันทึกข้อมูลเรียบร้อย';
		/*
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
		*/

		$post->projectId = $projectId;
		$post->psnid = $data->psnid;
		list($post->name, $post->lname) = sg::explode_name(' ', $post->fullname);
		$address = SG\explode_address($post->address, SUBSTR($post->areacode,0,6));
		$post->house = $address['house'];
		if ($address['village']) $post->areacode = SUBSTR($post->areacode, 0, 6).sprintf('%02d', $address['village']);

		mydb::query(
			'UPDATE %db_person% SET
			`prename` = :prename, `name` = :name, `lname` = :lname
			, `cid` = :cid
			, `phone` = :phone
			, `areacode` = :areacode
			, `house` = :house
			WHERE `psnid` = :psnid',
			$post
		);
		//$ret .= mydb()->_query.'<br />';

		/*
		mydb::query(
			'UPDATE %project%
			SET `bankaccount` = :bankaccount, `bankno` = :bankno, `bankname` = :bankname
			WHERE `tpid` = :projectId',
			$post
		);
		//$ret .= mydb()->_query.'<br />';
		*/

		//$ret .= print_o($bigData, '$bigData');
		//$ret .= print_o($post, '$post');
		return $ret;
	}



	// View Model
	$ret .= '<header class="header -box"><h3>'.$data->projectTitle.'</h3><nav class="nav" style="margin-right: 32px;"><ul class="ui-action"><li class="ui-item"><a class="sg-action btn -primary -fill" href="'.url('project/'.$projectId.'/info.address.check', array('mode' => 'edit')).'" data-rel="box"><i class="icon -material">edit</i><span>แก้ไขข้อมูล</span></a></li></ul></nav></header>';

	$form = new Form('data', url(q()), 'edit-child', 'sg-form');
	$form->addData('checkValid', true);
	if ($getMode != 'edit') $form->addConfig('readonly', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load:#project-app-message');

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
		'address',
		array(
			'label' => 'ที่อยู่ปัจจุบัน (บ้านเลขที่ อาคาร ซอย ถนน หมู่ที่)',
			'type' => 'text',
			'class' => 'sg-address -fill',
			'require' => true,
			'value' => $data->house ? $data->house.($data->village ? ' หมู่ที่ '.ltrim($data->village, '0') : '') : '',
			'attr' => array('data-altfld' => 'edit-data-areacode'),
			'placeholder' => '0 ม.0 ต.ทดสอบ แล้วเลือกจากรายการ',
		)
	);

	$form->addField(
		'changwat',
		array(
			'label' => 'จังหวัด:',
			'type' => 'select',
			'class' => 'sg-changwat -fill',
			'require' => true,
			'options' => array('' => '== เลือกจังหวัด ==')
				+ mydb::select(
					'SELECT `provid`, CONCAT("จังหวัด", `provname`) `provname` FROM %co_province% ORDER BY CONVERT(`provname` USING tis620) ASC; -- {key: "provid", value: "provname"}'
				)->items,
			'value' => $data->changwat,
			'attr' => array('data-altfld' => '#edit-data-areacode'),
			'container' => '{class: " -hide-label"}',
		)
	);

	$form->addField(
		'ampur',
		array(
			'label' => 'อำเภอ:',
			'type' => 'select',
			'class' => 'sg-ampur -fill',
			'require' => true,
			'options' => array('' => '== เลือกอำเภอ ==')
				+ ($data->changwat ?
						mydb::select(
							'SELECT SUBSTR(`distid`, 3, 2) `distid`, CONCAT("อำเภอ",`distname`) `distname` FROM %co_district% WHERE `distid` LIKE :changwat ORDER BY CONVERT(`distname` USING tis620) ASC; -- {key: "distid", value: "distname"}',
							':changwat', $data->changwat.'%'
						)->items
						:
						[]
					),
			'value' => $data->ampur,
			'attr' => array('data-altfld' => '#edit-data-areacode'),
			'container' => '{class: " -hide-label"}',
		)
	);

	$form->addField(
		'tambon',
		array(
			'label' => 'ตำบล:',
			'type' => 'select',
			'class' => 'sg-tambon -fill',
			'require' => true,
			'options' => array('' => '== เลือกตำบล ==')
				+ ($data->ampur ?
						mydb::select(
							'SELECT SUBSTR(`subdistid`, 5, 2) `subdistid`, CONCAT("ตำบล",`subdistname`) `subdistname` FROM %co_subdistrict% WHERE `subdistid` LIKE :changwat ORDER BY CONVERT(`subdistname` USING tis620) ASC; -- {key: "subdistid", value: "subdistname"}',
							':changwat', $data->changwat.$data->ampur.'%'
						)->items
						:
						[]
					),
			'value' => $data->tambon,
			'attr' => array('data-altfld' => '#edit-data-areacode'),
			'container' => '{class: " -hide-label"}',
		)
	);

	$form->addField('areacode', array('type' => 'hidden', 'value' => $data->areacode, 'require' => true));

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
		$form->addText('<nav class="nav -page -sg-text-right" style="padding: 16px 8px;"><a class="sg-action btn -primary -fill" href="'.url('project/'.$projectId.'/info.address.check', array('mode' => 'edit')).'" data-rel="box"><i class="icon -material">edit</i><span>แก้ไขข้อมูล</span></a></nav>');
	}

	$ret .= $form->build();

	//$ret .= print_o($data, $darta);

	$ret .= '<style type="text/css">
	.form>section {box-shadow: 0 0 0 2px #ddd; margin: 4px 4px 32px 4px; background-color: #fff;}
	.form>section>h3 {padding: 8px; background-color: #eee;}
	.form *[readonly] {box-shadow: 0 0 0 1px #f5f5f5; color: #999;}
	.form .-hide-label label {display: none;}
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
			, pn.`areacode`, pn.`house`
			, LEFT(pn.`areacode`, 2) `changwat`
			, SUBSTR(pn.`areacode`, 3, 2) `ampur`
			, SUBSTR(pn.`areacode`, 5, 2) `tambon`
			, SUBSTR(pn.`areacode`, 7, 2) `village`
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
		WHERE t.`tpid` = :projectId AND p.`ownertype` IN ( :ownerType )
		LIMIT 1',
		':projectId', $projectId,
		':ownerType', 'SET-STRING:'.implode(',', array(_PROJECT_OWNERTYPE_GRADUATE, _PROJECT_OWNERTYPE_STUDENT, _PROJECT_OWNERTYPE_PEOPLE))
	);

	$data = mydb::clearprop($data);

	// debugMsg(mydb()->_query);
	// debugMsg($data, '$data');
	return $data;
}
?>