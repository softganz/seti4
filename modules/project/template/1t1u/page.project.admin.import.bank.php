<?php
/**
* Project :: Import Bank Account
* Created 2021-02-24
* Modify  2021-02-24
*
* @param Object $self
* @return String
*
* @usage project/admin/import/bank
*/

$debug = true;

function project_admin_import_bank($self) {
	$toolbar = new Toolbar($self, 'Project Administrator');
	$self->theme->sidebar = R::View('project.admin.menu','follow');

	$ret = '';

	$isSimulate = post('simulate') == 'Yes';
	$importAreacode = post('areacode');
	$importSeries = post('importseries');
	$importUid = post('uid');
	$importName = post('name');
	$personType = post('persontype');
	$sourceData = post('data');
	$updateData = post('update');

	$deleteOldSeries = post('deleteseries') == 'Yes';
	$showQt = post('showqt') == 'Yes';
	$isDebug = post('debug');
	$dateCreatedU = date('U');

	$splitAddress = false;


	$ret .= '<div style="width: 100%;">';
	$ret .= '<header class="header"><h3>นำเข้าข้อมูลโครงการ</h3></header>';

	$tableError = new Table();
	$tableError->caption='รายการนำเข้าผิดพลาด';

	$tableComplete = new Table();
	$tableComplete->caption='รายการนำเข้าสมบูรณ์';

	$tableUser = new Table();
	$tableUser->caption = 'รายการปรับปรุงสมาชิก';
	$tableQt = new Table();
	$tableQt->caption='รายการนำเข้าแบบสอบถาม';


	$dataLineFormat = 'คำนำหน้าชื่อ	ชื่อ-สกุล	เลขบัตรประชาชน	มหาวิทยาลัยหรือสถาบันที่จบการศึกษา	คณะหรือสาขาที่จบการศึกษา	พื้นที่ปฏิบัติงาน (ตำบล อำเภอ จังหวัด)	เบอร์โทรศัพท์	อีเมล์	เลขที่บัญชี (ผูกพร้อมเพย์เลข 13 หลัก)	ธนาคาร';

	if ($sourceData) {

		$lines = explode("\n", $sourceData);
		$sep = "\t";

		foreach ($lines as $key=>$line) {
			unset($error);
			$line = trim($line);
			if (empty($line)) continue;

			$row = str_getcsv($line,$sep);
			foreach ($row as $k=>$v) $row[$k] = trim($v);

			//$ret .= print_o($row,'$row');

			if ($row[0] == 'title') {
				unset($lines[$key]);
				continue;
			}

			// If no title
			//if (empty($row[5])) continue;

			$preName = $row[0];
			$fullName = preg_replace('/\s+/',' ', $row[1]);
			$cid = preg_replace('/\s+/','', $row[2]);
			$faculty = $row[3];
			$graduated = $row[4];
			$userName = preg_replace('/\s+/','', $row[6]);
			$email = strtolower($row[7]);
			$bankAccount = $preName.$fullName;
			$bankNo = preg_replace('/\s+/','', $row[8]);
			$bankName = $row[9];

			$userInfo = mydb::select(
				'SELECT
				u.`uid`, u.`username`, u.`name`
				, t.`tpid` `projectId`, t.`title` `projectTitle`, p.`ownertype` `ownerType`
				, tp.`parent`, tp.`uid` `parentUid`
				, pn.`psnid`, pn.`userid`, pn.`cid`
				FROM %users% u
					LEFT JOIN %topic% t ON t.`uid` = u.`uid` AND t.`type` = "project"
					LEFT JOIN %project% p ON p.`tpid` = t.`tpid` AND `ownertype` IN ("graduate", "student", "people")
					LEFT JOIN %topic% tp ON tp.`tpid` = t.`parent`
					LEFT JOIN %db_person% pn ON pn.`userid` = u.`uid`
				WHERE `username` = :userName
				LIMIT 1',
				':userName', $userName
			);
			if (!($userInfo->uid && $userInfo->projectId)) continue;
			mydb::clearprop($userInfo);

			$projectId = $userInfo->projectId;

			$isCidExists = mydb::select('SELECT `psnid` FROM %db_person% WHERE `cid` = :cid LIMIT 1', ':cid', $cid)->psnid;

			//$ret .= print_o($userInfo, '$userInfo');
			//$ret .= print_o($projectInfo, '$projectInfo');

			$psnData = new stdClass();
			$psnResult = new stdClass();
			$topicProperty = NULL;
			if ($isCidExists) {
				$psnResult->psnid = $isCidExists;
			} else if (empty($userInfo->cid) && $cid) {
				if ($isSimulate) {
					$ret .= 'UPDATE CID '.$cid.'<br />';
				} else {
					mydb::query('UPDATE %db_person% SET `cid` = :cid WHERE `psnid` = :psnid', ':psnid', $userInfo->psnid, ':cid', $cid);
					$ret .= mydb()->_query.'<br />';
				}
			} else if ($fullName) {
				$psnData->psnid = NULL;
				$psnData->uid = $userInfo->parentUid;
				$psnData->userId = $userInfo->uid;
				$psnData->prename = $preName;
				list($psnData->firstname, $psnData->lastname) = sg::explode_name(' ', $fullName);
				$psnData->cid = $cid;
				$psnData->phone = $userName;
				$psnData->email = $email;
				$psnData->graduated = $graduated;
				$psnData->faculty = $faculty;

				if ($isSimulate) {
					$ret .= 'UPDATE PERSON<br />';
				} else {
					$psnResult = R::Model('person.save', $psnData);
					//$ret .= print_o($psnResult, '$psnResult');
					$ret .= print_o($psnData, '$psnData');
				}
			}

			if ($isDebug) {
				$ret .= print_o($userInfo,'$userInfo');
				$ret .= print_o($psnResult,'$psnResult');
			}

			//$ret .= '$bankNo = '.$bankNo.'<br />';
			if ($bankNo) {
				if ($isSimulate) {
					$ret .= 'UPDATE BANK ACCOUNT<br />';
				} else {
					mydb::query(
						'UPDATE %project% SET
						`bankaccount` = :bankAccount
						, `bankname` = :bankName
						, `bankno` = :bankNo
						WHERE `tpid` = :projectId
						LIMIT 1
						',
						':projectId', $projectId,
						':bankAccount', $bankAccount,
						':bankNo', $bankNo,
						':bankName', $bankName
					);

					$ret .= mydb()->_query.'<br />';
				}
			}

			if (!$isSimulate) {
				//$result = R::Model('project.create', $project, '{debug: true}');
				if ($isDebug) $ret .= print_o($userInfo, '$userInfo').'<hr />';
			}

			if ($error) {
				$tableError->rows[] = $psnData;
				$tableError->rows[] = '<tr><td colspan="15"><span style="color:red;">'.$error.'</span></td></tr>';
			} else {
				$tableUser->rows[] = (Array) $userInfo;
				$tableComplete->rows[] = (Array) $psnData;
				$complete[] = $line;
				unset($lines[$key]);
			}
		}
		$post->data = implode("\n", $lines);
	}



	$form = new Form(NULL, url(q()), 'edit-info', 'sg-form');
	$form->addData('checkValid', true);

	$form->addField('uid', array('type' => 'hidden', 'name' => 'uid', 'id' => 'uid', 'value' => $importUid));
	$form->addField('areacode', array('type' => 'hidden', 'name' => 'areacode', 'value' => htmlspecialchars($importAreacode)));

	$form->addField(
		'importseries',
		array(
			'type' => 'text',
			'label' => 'Import Series',
			'require' => false,
			'value' => htmlspecialchars($importSeries),
			'posttext' => ' <span><label><input type="checkbox" name="simulate" value="Yes" '.($isSimulate?'checked="checked"':'').' /> Simulate </label></span>'
				//. '<span><label><input type="checkbox" name="deleteseries" value="Yes" '.($deleteOldSeries?'checked="checked"':'').' /> Delete old import series </label></span>'
				. '<span><label><input type="checkbox" name="debug" value="Yes" '.($isDebug?'checked="checked"':'').' /> Debug </label><span>',
		)
	);

	$form->addField(
		'data',
		array(
			'type' => 'textarea',
			'label' => 'ข้อมูลสำหรับนำเข้า (แยกฟิลด์ด้วยเครื่องหมาย Tab )',
			'class' => '-fill',
			'rows' => 18,
			'value' => $post->data,
			'placeholder' => $dataLineFormat,
		)
	);

	$form->addField(
		'submit',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">cloud_upload</i><span>{tr:IMPORT}</span>',
			'pretext' => '<a class="btn -link cancel" href="'.url('project/admin/import/bank').'" data-rel="#main"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	$ret .= '<div style="width: 100%; overflow: scroll;">';

	$tableError->thead = array_keys($project);
	$ret .= $tableError->build();

	$tableUser->thead = array_keys(reset($tableUser->rows));
	array_shift($tableUser->thead);
	$ret .= $tableUser->build();

	$tableComplete->thead = array_keys(reset($tableComplete->rows));
	$ret .= $tableComplete->build();

	$ret .= '</div>';

	//$ret .= print_o(post(),'post()');

	$ret .= '</div>';

	return $ret;
}
?>
