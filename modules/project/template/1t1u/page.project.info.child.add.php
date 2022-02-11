<?php
/**
* Project :: View Follow Information
* Created 2021-01-27
* Modify  2021-02-02
*
* @param Object $self
* @param Object $projectInfo
* @return String
*
* @usage project/app/follow/{id}
*/

$debug = true;

function project_info_child_add($self, $projectInfo) {
	// Data model
	if (!($projectId = $projectInfo->projectId)) return message('error', 'PROCESS ERROR');

	$isEdit = $projectInfo->right->isAdmin || $projectInfo->right->isOwner;

	if (!$isEdit) return message('error', 'Access Denied');

	$ret = '';

	if (($data = (Object) post('data'))->name) {
		$data->name = preg_replace('/\s+/', ' ', $data->name);
		$data->phone = preg_replace('/[^0-9]/', '', $data->phone);
		$data->cid = preg_replace('/[^0-9]/', '', $data->cid);
		$data->email = strtolower(preg_replace('/\s/', '', $data->email));
		if (
			($data->phone && strlen($data->phone) === 10)
			&& ($data->cid && strlen($data->cid) === 13)
			&& $data->email && $data->budget) {

			$fullName = trim($data->prename.$data->name);
			$userData = new stdClass();
			$userData->username = $data->phone;
			$userData->password = $data->cid;
			$userData->name = $fullName;
			$userData->phone = $data->phone;
			$userData->email = $data->email;
			$userData->admin_remark = 'Create by '.i()->name.'('.i()->uid.')';

			$userResult = UserModel::create($userData);

			if (!$userResult->uid) {
				$error = 'ไม่สามารถสร้างสมาชิกได้'.print_o($userData, '$userData').print_o($userResult, '$userResult');
			} else {
				$projectData = new stdClass();
				$projectData->title = $fullName;
				$projectData->parent = $projectId;
				$projectData->orgid = $projectInfo->orgid;
				$projectData->uid = $userResult->uid;
				$projectData->areacode = $projectInfo->info->areacode;
				$projectData->changwat = substr($projectInfo->info->areacode, 0, 2);
				$projectData->ampur = substr($projectInfo->info->areacode, 2, 2);
				$projectData->tambon = substr($projectInfo->info->areacode, 4, 2);
				$projectData->pryear = '2021';
				$projectData->date_approve = '2021-02-01';
				$projectData->date_from = '2021-02-01';
				$projectData->date_end = '2022-01-31';
				$projectData->budget = sg_strip_money($data->budget);

				$projectResult = R::Model('project.create', $projectData);

				mydb::query('UPDATE %project% SET `ownertype` = :ownertype WHERE `tpid` = :projectId LIMIT 1', ':projectId', $projectResult->projectId, ':ownertype', $data->childType);

				R::Model('project.employee.period.create', R::Model('project.get', $projectResult->projectId));

				$isCidExists = mydb::select('SELECT `cid` FROM %db_person% WHERE `cid` = :cid LIMIT 1', $data)->cid;
				if (!$isCidExists) {
					$psnData = new stdClass();
					$psnData->psnid = NULL;
					$psnData->userId = $userResult->uid;
					$psnData->prename = $data->prename;
					list($psnData->firstname, $psnData->lastname) = sg::explode_name(' ', $data->name);
					$psnData->cid = $data->cid;
					$psnData->phone = $data->phone;
					$psnData->email = $data->email;
					$psnData->address = $data->address;
					$psnData->areacode = $data->areacode;
					$psnData->graduated = SG\getFirst('graduated');
					$psnData->faculty = SG\getFirst('faculty');
					//$ret .= print_o($psnData, '$psnData');

					$psnResult = R::Model('person.save', $psnData);
					//$ret .= print_o($psnResult, '$psnResult');

					$topicProperty = '{psnId: '.$psnResult->psnid.'}';
					mydb::query('UPDATE %topic_revisions% SET `property` = :property WHERE `tpid` = :projectId LIMIT 1', ':projectId', $projectResult->projectId, ':property', $topicProperty);
					//$ret .= mydb()->_query;
					//$ret .= print_o($psnResult, '$psnResult');
					//$ret .= print_o($psnData, '$psnData');
				}

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
			}
		} else {
			$error = 'ข้อมูลไม่ถูกต้อง';
		}
		//debugMsg($data, '$data');
	}

	if ($error) {
		http_response_code(_HTTP_ERROR_NOT_ACCEPTABLE);
		return $error;
	}




	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>เพิ่มผู้รับจ้าง</h3></header>';

	$dataLineFormat = 'รหัสองค์กร	ประเภทโครงการ	รหัสพื้นที่	uid	รหัสชุดโครงการ	ชื่อโครงการ	ปี	เริ่ม	สิ้นสุด	อนุมัติ	งบ';

	$form = new Form('data', url(q()), 'edit-child', 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load');

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
		'name',
		array(
			'type' => 'text',
			'label' => 'ชื่อจริงของผู้รับจ้าง',
			'class' => '-fill',
			'require' => true,
			'value' => $data->name,
			'placeholder' => 'ชื่อ นามสกุล',
			'description' => 'ชื่อของผู้รับจ้างจะนำมาใช้เป็นชื่อจริงของสมาชิก',
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
			'description' => 'หมายเลขโทรศัพท์ป้อนเฉพาะตัวเลขเท่านั้น <b>จะนำมาสร้างเป็น username สำหรับสมาชิก</b>',
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
			'placeholder' => '0000000000000',
			'description' => 'หมายเลขประจำตัว 13 หลัก <b>จะนำมาใช้เป็น รหัสผ่าน</b> สำหรับผู้รับจ้าง',
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
			'value' => $data->childType,
		)
	);

	$form->addField(
		'budget',
		array(
			'type' => 'text',
			'label' => 'ค่าจ้างทั้งหมด<span style="color:red">ตลอดทั้งโครงการ (บาท)</span>',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($data->budget),
			'placeholder' => '0.00',
		)
	);

	$form->addField('areacode', array('type' => 'hidden', 'value' => $data->areacode));

	$form->addField(
		'address',
		array(
			'label' => 'ที่อยู่ปัจจุบัน (บ้านเลขที่ อาคาร ซอย ถนน หมู่ที่)',
			'type' => 'text',
			'class' => 'sg-address -fill',
			'value' => $data->house ? $data->house.($data->village ? ' ม.'.$data->village : '') : $data->address,
			'attr' => array('data-altfld' => 'edit-data-areacode'),
			'placeholder' => '0 ม.0 ต.ทดสอบ แล้วเลือกจากรายการ',
		)
	);

	$provinceOptions = array();

	$stmt = 'SELECT * FROM %co_province% ORDER BY CONVERT(`provname` USING tis620) ASC';
	foreach (mydb::select($stmt)->items as $rs) $provinceOptions[$rs->provid] = $rs->provname;


	$form->addField(
		'changwat',
		array(
			'label' => 'จังหวัด:',
			'type' => 'select',
			'class' => 'sg-changwat -fill',
			'options' => array('' => '== เลือกจังหวัด ==') + $provinceOptions,
			'value' => $data->changwat,
			'containerclass' => '-inlineblock',
			'attr' => array('data-altfld' => '#edit-data-areacode'),
		)
	);

	$form->addField(
		'ampur',
		array(
			'label' => 'อำเภอ:',
			'type' => 'select',
			'class' => 'sg-ampur -fill',
			'options' => array('' => '== เลือกอำเภอ =='),
			'containerclass' => '-inlineblock',
			'value' => $data->ampur,
			'attr' => array('data-altfld' => '#edit-data-areacode'),
		)
	);

	$form->addField(
		'tambon',
		array(
			'label' => 'ตำบล:',
			'type' => 'select',
			'class' => 'sg-tambon -fill',
			'options' => array('' => '== เลือกตำบล =='),
			'value' => $data->tambon,
			'containerclass' => '-inlineblock',
			'attr' => array('data-altfld' => '#edit-data-areacode'),
		)
	);
	$form->addField(
		'submit',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>เพิ่มผู้รับจ้าง</span>',
			'pretext' => '<a class="sg-action btn -link cancel" href="javascript:void(0)" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	//$ret .= print_o($projectInfo, '$projectInfo');

	$ret .= '<style type="text/css">
	.sg-form .-error {padding: 4px 0; display: block;}
	</style>';

	$ret .= '<script type="text/javascript">
	$("#edit-child .form-text").keyup(function() {
		let $this = $(this)
		let id = $this.attr("id")
		let $detailEle = $this.closest("div").children(".-error")
		let apiUrl = "'.url('api/user').'"
		if (id == "edit-data-phone") {
			let para = {username: $this.val(), debug: "none"}
			$.get(apiUrl, para, function(data) {
				$detailEle.text(data.length ? "หมายเลขโทรศัพท์นี้มีผู้อื่นใช้งานในระบบแล้ว" : "")
				//console.log(data)
			},"json")
		}
		if (id == "edit-data-email") {
			let para = {email: $this.val(), debug: "none"}
			$.get(apiUrl, para, function(data) {
				$detailEle.text(data.length ? "อีเมล์นี้มีผู้อื่นใช้งานในระบบแล้ว" : "")
				//console.log(data)
			},"json")
		}
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
