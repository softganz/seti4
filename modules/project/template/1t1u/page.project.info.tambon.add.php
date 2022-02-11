<?php
/**
* Project :: Add Follow Of Tambon Information
* Created 2021-02-16
* Modify  2021-02-16
*
* @param Object $self
* @param Object $projectInfo
* @return String
*
* @usage project/{id}/info.tambon.add
*/

$debug = true;

function project_info_tambon_add($self, $projectInfo) {
	// Data model
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$isEdit = $projectInfo->right->isAdmin || $projectInfo->right->isOwner;

	if (!$isEdit) return message('error', 'Access Denied');

	$orgShortName = strtolower(mydb::select('SELECT `enshortname` FROM %db_org% WHERE `orgid` = :orgid LIMIT 1', ':orgid', $projectInfo->orgid)->enshortname);

	if (!$orgShortName) return message('error', 'หน่วยงานรับผิดชอบยังไม่ได้กำหนดชื่อย่อ ('.$projectInfo->orgid.')');

	$ret = '';

	if (($data = (Object) post('data'))->changwat) {
		$isDubUser = R::Model('user.get', array('username' => $data->areacode));
		debugMsg($isDubUser, '$isDubUser');

		$stmt = 'SELECT cos.`subdistid`, CONCAT("ต.",cos.`subdistname`," อ.",cod.`distname`," จ.",cop.`provname`) `name`
			FROM %co_subdistrict% cos
				LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(cos.`subdistid`, 4)
				LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(cos.`subdistid`, 2)
			WHERE `subdistid` = :tambon
			LIMIT 1';
		$data->name = $tambonName = mydb::select($stmt, ':tambon', $data->areacode)->name;
		//debugMsg($tambonName, '$tambonName');

		if (strlen($data->areacode) != 6) $error = 'พื้นที่ดำเนินการไม่ถูกต้อง';
		else if (!$tambonName) $error = 'ไม่มีพื้นที่อยู่ในระบบ';
		else if ($isDubUser) $error = 'สมาชิกนี้มีในระบบแล้ว';


		if (!$error) {
			$userData = new stdClass();
			$userData->username = $data->username;
			$userData->password = $data->password;
			$userData->name = $tambonName;
			$userData->admin_remark = 'Create by '.i()->name.'('.i()->uid.')';
			$userResult = R::Model('user.create', $userData);

			if ($userResult->uid) {
				$projectData = new stdClass();
				$projectData->title = $data->name;
				$projectData->projectset = $projectId;
				$projectData->orgid = $projectInfo->orgid;
				$projectData->uid = $userResult->uid;
				$projectData->prtype = 'ชุดโครงการ';
				$projectData->ischild = 1;
				$projectData->areacode = $data->areacode;
				$projectData->changwat = substr($data->areacode, 0, 2);
				$projectData->ampur = substr($data->areacode, 2, 2);
				$projectData->tambon = substr($data->areacode, 4, 2);
				$projectData->pryear = '2021';
				$projectData->date_approve = '2021-02-01';
				$projectData->date_from = '2021-02-01';
				$projectData->date_end = '2022-01-31';
				$projectData->budget = sg_strip_money($data->budget);

				$projectResult = R::Model('project.create', $projectData);

				mydb::query('UPDATE %project% SET `ownertype` = :ownertype WHERE `tpid` = :projectId LIMIT 1', ':projectId', $projectResult->projectId, ':ownertype', $data->childType);

				R::Model(
					'watchdog.log',
					'project',
					'User & project add',
					'User '.i()->uid.' was create user ('.$userResult->uid.') and project ('.$projectResult->tpid.').',
					i()->uid,
					$projectResult->tpid
				);
				//$ret .= print_o($userResult, '$userResult');
				//$ret .= print_o($projectResult, '$projectResult');
				//$ret .= print_o($data, '$data');
				return $ret;
			} else {
				$error = 'การสร้างสมาชิกผิดพลาด';				
			}
		}
		//debugMsg($data, '$data');
		//http_response_code(_HTTP_ERROR_NOT_ACCEPTABLE);
		//return $error.$ret;
	}

	if ($error) {
		http_response_code(_HTTP_ERROR_NOT_ACCEPTABLE);
		return $error;
	}





	$form = new Form('data', url(q()), 'edit-child', 'sg-form');
	$form->addData('checkvalid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load');

	$form->header(_HEADER_BACK.'<h3>เพิ่มโครงการตำบล</h3>', '{class: "-hidden"}');

	$form->addField('childType', array('type' => 'hidden', 'value' => 'tambon'));
	$form->addField('areacode', array('type' => 'hidden', 'label' => 'พื้นที่ดำเนินการ', 'require' => true, 'value' => $data->areacode));

	$provinceOptions = R::Model('changwat.get', NULL, '{region: "changwat"}');

	$form->addText('<section><h3>ข้อมูลโครงการ</h3>');

	$form->addField(
		'budget',
		array(
			'type' => 'text',
			'label' => 'งบประมาณ<span style="color:red">ตลอดทั้งโครงการ (บาท)</span>',
			'class' => '-money -fill',
			'require' => true,
			'value' => htmlspecialchars(SG\getFirst($data->budget, '800,000.00')),
			'placeholder' => '0.00',
		)
	);

	$form->addField(
		'changwat',
		array(
			'label' => 'พื้นที่ดำเนินการ:',
			'type' => 'select',
			'class' => 'sg-changwat -fill',
			'require' => true,
			'options' => array('' => '== เลือกจังหวัด ==') + $provinceOptions,
			'value' => $data->changwat,
			//'containerclass' => '-inlineblock',
			//'attr' => array('data-altfld' => '#edit-data-areacode'),
		)
	);

	$form->addField(
		'ampur',
		array(
			//'label' => 'อำเภอ:',
			'type' => 'select',
			'class' => 'sg-ampur -fill -hidden',
			'require' => true,
			'options' => array('' => '== เลือกอำเภอ =='),
			//'containerclass' => '-inlineblock',
			'value' => $data->ampur,
			//'attr' => array('data-altfld' => '#edit-data-areacode'),
		)
	);

	$form->addField(
		'tambon',
		array(
			//'label' => 'ตำบล:',
			'type' => 'select',
			'class' => 'sg-tambon -fill -hidden',
			'require' => true,
			'options' => array('' => '== เลือกตำบล =='),
			'value' => $data->tambon,
			//'containerclass' => '-inlineblock',
			'attr' => array('data-altfld' => '#edit-data-areacode'),
			'posttext' => '<span class="-error"></span>',
		)
	);

	$form->addText('</section>');


	$form->addText('<section><h3>ข้อมูลผู้รับผิดชอบโครงการ</h3>');

	$form->addField(
		'name',
		array(
			'type' => 'text',
			'label' => 'ชื่อจริง-นามสกุล',
			'class' => '-fill',
			'require' => true,
			'value' => $data->name,
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
			'placeholder' => '0000000000',
			'posttext' => '<span class="-error"></span>',
			'description' => 'หมายเลขโทรศัพท์ป้อนเฉพาะตัวเลขเท่านั้น',
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
			'posttext' => '<span class="-error"></span>',
			'placeholder' => 'name@example.com',
			'description' => 'สมาชิกสามารถ <b>ใช้อีเมล์แทน username ในการเข้าสู่ระบบได้</b>',
		)
	);

	$form->addField(
		'username',
		array(
			'type' => 'text',
			'label' => 'ชื่อสมาชิก (Username) สำหรับเข้าสู่ระบบ',
			'class' => '-fill',
			'require' => true,
			'readonly' => true,
			'placeholder' => 'ชื่อสมาชิกสำหรับเข้าสู่ระบบ',
		)
	);

	$form->addField(
		'password',
		array(
			'type' => 'text',
			'label' => 'รหัสผ่าน (Password) สำหรับเข้าสู่ระบบ',
			'class' => '-fill',
			'placeholder' => 'รหัสผ่านสำหรับเข้าสู่ระบบ',
		)
	);

	$form->addText('</section>');

	$form->addField(
		'submit',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>เพิ่มโครงการตำบล</span>',
			'pretext' => '<a class="sg-action btn -link cancel" href="javascript:void(0)" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$form->addText('<p>หมายเหตุ: ระบบจะสร้างสมาชิกโดยใช้รหัสตำบลเป็นชื่อสมาชิกและรหัสผ่าน หลังจากผู้บริหารโครงการตำบล Sign In เข้าสู่ระบบสมาชิกเรียบร้อยแล้ว ให้ทำการเปลี่ยนรหัสผ่านเพื่อความปลอดภัยจากผู้ที่ไม่ได้รับอนุญาต</p>');

	$ret .= $form->build();

	//$ret .= print_o($projectInfo, '$projectInfo');

	$ret .= '<style type="text/css">
	.sg-form .-error {padding: 4px 0; display: block;}
	.form>section {margin: 0 4px 16px 4px; padding: 8px; border: 2px #ddd solid; border-radius: 8px; background-color: #fff;}
	</style>';

	$ret .= '<script type="text/javascript">
	$("#edit-data-changwat").change(function() {
		$("#edit-data-username").val("")
		$("#edit-data-password").val("")
		$(".-error").text("").hide()
	});
	$("#edit-data-ampur").change(function() {
		$("#edit-data-username").val("")
		$("#edit-data-password").val("")				
		$(".-error").text("").hide()
	});
	$("#edit-data-tambon").change(function() {
		let orgShortName = "'.$orgShortName.'"
		let $this = $(this)
		let id = $this.attr("id")
		let $detailEle = $this.closest("div").children(".-error")
		let areacode = $("#edit-data-changwat").val() + $("#edit-data-ampur").val() + $("#edit-data-tambon").val()
		console.log("Areacode Change", areacode)
		let apiUrl = "'.url('api/user').'"
		let para = {username: areacode, debug: "none"}
		$.get(apiUrl, para, function(data) {
			if (data.length) {
				$detailEle.text("ตำบลนี้ผู้อื่นใช้งานในระบบแล้ว").show()
			} else {
				$("#edit-data-username").val(orgShortName + areacode)
				$("#edit-data-password").val(areacode)
				$detailEle.text("").hide()
			}
			console.log(data)
		},"json")
	});
	</script>';

	head('<script type="text/javascript">
	function onWebViewComplete() {
		return {refresh: false, refreshOnBack: true}
	}
	</script>');
	return $ret;
}
?>