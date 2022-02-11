<?php
/**
* Import data รายชื่อคนพิการเพิ่มใหม่
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $self
* @return String
*/

$debug = true;

function imed_admin_import_disabled($self) {
	$isSimulate = post('simulate') == 'Yes';
	$areaCode = post('areacode');
	$importSeries = post('importseries');
	$importUid = post('uid');
	$personType = post('persontype');
	$sourceData = post('data');
	$updateData = post('update');

	$deleteOldSeries = post('deleteseries') == 'Yes';
	$showQt = post('showqt') == 'Yes';
	$isDebug = post('debug');
	$dateCreatedU = date('U');

	$splitAddress = false;


	$ret .= '<div style="width: 100%;">';
	$ret .= '<header class="header"><h3>นำเข้าข้อมูลคนพิการของตำบล</h3></header>';

	$tableError = new Table();
	$tableError->caption='รายการนำเข้าผิดพลาด';

	$tableComplete = new Table();
	$tableComplete->caption='รายการนำเข้าสมบูรณ์';

	$tableQt = new Table();
	$tableQt->caption='รายการนำเข้าแบบสอบถาม';


	//ลำดับ	บัตรประจำตัวประชาชน	คำนำหน้าชื่อ	ชื่อ	สกุล	วันเดือนปี เกิด	อายุ	เพศ	ประเภทความพิการ	ลักษณะความพิการ	ที่อยู่ปัจจุบัน	ตำบล/แขวง	อำเภอ/เขต	จังหวัด	ที่อยู่ภูมิลำเนา	ตำบล/แขวง	อำเภอ/เขต	จังหวัด

	if ($personType && $areaCode && $sourceData) {
		foreach (mydb::select('SELECT * FROM %co_educate%')->items as $rs) {
			$educates[$rs->edu_desc] = $rs->edu_code;
		}

		$lines = explode("\n", $sourceData);
		$sep = "\t";

		foreach ($lines as $key=>$line) {
			unset($error);
			$person = array();
			$line = trim($line);
			if (empty($line)) continue;
			//				$line=preg_replace('/  /',' ',$line);
			//			$ret.=$line.'<br />';
			//				$row=explode($sep,$line);

			$row = str_getcsv($line,$sep);
			//$ret.=print_o($row,'$row');
			foreach ($row as $k=>$v) $row[$k] = trim($v);

			if ($row[0] == 'title') {
				unset($lines[$key]);
				continue;
			}

			$person['no'] = $row[0];
			$person['prename'] = $row[1];
			$person['name'] = $row[2];
			$person['lname'] = $row[3];
			$person['cid'] = preg_replace('/\W/','',$row[4]);
			$person['sex'] = $row[5];
			if (empty($person['sex'])) {
				if (in_array($person['prename'], array('นาย', 'ด.ช.'))) $person['sex'] = 'ชาย';
				else if (in_array($person['prename'], array('นาง', 'นางสาว', 'ด.ญ.'))) $person['sex'] = 'หญิง';
			}

			if ($isDebug) $ret .= '<b>PROCESS CID '.$person['cid'].'</b><br />';


			if ($isDebug) $ret .= print_o($row,'$row');



			//if ($row[6]) $person['birth'] = (date('Y') - $row[6]).'-01-01';
			//else if ($row[6]) $person['birth'] = $row[6].sg::convert_thai_date($row[6]);
			//else $person['birth'] = NULL;
			//$person['birth'] = (date('Y') - $row[6]).'-01-01';
			if ($row[6]) $person['birth'] = sg_date($row[6], 'Y-m-d');


			if ($splitAddress) {
				$addressReal = SG\explode_address($row[10].' ต.'.$row[11].' อ.'.$row[12].' จ.'.$row[13], $areaCode);
				//$ret .= print_o($addressReal, '$addressReal');

				$person['house'] = $addressReal['house'];
				$person['village'] = $addressReal['villageCode'];
				$person['tambon'] = $addressReal['tambonCode'];
				$person['ampur'] = $addressReal['ampurCode'];
				$person['changwat'] = $addressReal['changwatCode'];

				$addressReg = SG\explode_address($row[14].' ต.'.$row[15].' อ.'.$row[16].' จ.'.$row[17], $areaCode);

				$person['rhouse'] = $addressReg['house'];
				$person['rvillage'] = $addressReg['villageCode'];
				$person['rtambon'] = $addressReg['tambonCode'];
				$person['rampur'] = $addressReg['ampurCode'];
				$person['rchangwat'] = $addressReg['changwatCode'];
			} else {
				$addressReal = SG\explode_address($row[7]. 'ม.'.$row[8].' ต.'.$row[9].' อ.'.$row[10].' จ.'.$row[11], $areaCode);
				//$ret .= print_o($addressReal, '$addressReal');

				$person['house'] = $addressReal['house'];
				$person['village'] = $addressReal['villageCode'] ? intval($addressReal['villageCode']) : '';
				$person['tambon'] = $addressReal['tambonCode'];
				$person['ampur'] = $addressReal['ampurCode'];
				$person['changwat'] = $addressReal['changwatCode'];

				$addressReg = SG\explode_address($row[12]. 'ม.'.$row[13].' ต.'.$row[14].' อ.'.$row[15].' จ.'.$row[16], $areaCode);

				$person['rhouse'] = $addressReg['house'];
				$person['rvillage'] = $addressReg['villageCode'] ? intval($addressReg['villageCode']) : '';
				$person['rtambon'] = $addressReg['tambonCode'];
				$person['rampur'] = $addressReg['ampurCode'];
				$person['rchangwat'] = $addressReg['changwatCode'];
			}

			$person['areacode'] = $person['changwat'].$person['ampur'].$person['tambon'].sprintf('%02d', $person['village']);
			$person['hrareacode'] = $person['rchangwat'].$person['rampur'].$person['rtambon'].sprintf('%02d', $person['rvillage']);

			$person['phone'] = $row[17];
			$defectTypeText = $row[18];

			//$person['educate']=$educates[$row[13]];

			$person['uid'] = SG\getFirst($importUid, i()->uid);
			$person['created'] = $dateCreatedU;
			$person['importseries'] = $importSeries;


			//if ($isDebug) $ret .= print_o($person,'$person');

			if (empty($person['cid'])) {
				$ret .= '<p>NO CID '.$person['no'].'. ชื่อ = '.$person['name'].' นามสกุล = '.$person['lname'].'</p>';
				continue;
			}

			$psnId = mydb::select('SELECT `psnid` FROM %db_person% WHERE `cid` = :cid LIMIT 1', $person)->psnid;

			if ($psnId) {
				//$ret .= '<p>DUPLICATE CID '.$person['no'].'. '.$person['cid'].' '.$person['name'].' '.$person['lname'].'</p>';
				if ($updateData) {
					//$error .= 'UPDATE DUPLICATE CID '.$person['cid'];
					$stmt = 'UPDATE %db_person% SET
						`prename` = :prename
						, `name` = :name
						, `lname` = :lname
						, `birth` = :birth
						, `sex` = :sex
						, `areacode` = :areacode
						, `hrareacode` = :hrareacode
						, `house` = :house
						, `village` = :village
						, `tambon` = :tambon
						, `ampur` = :ampur
						, `changwat` = :changwat
						, `rhouse` = :rhouse
						, `rvillage` = :rvillage
						, `rtambon` = :rtambon
						, `rampur` = :rampur
						, `rchangwat` = :rchangwat
						WHERE `psnid` = :psnid
						LIMIT 1';

					if ($isSimulate) {
						$tableComplete->rows[] = $person;
					} else {
						mydb::query($stmt,$person, ':psnid', $psnId);

						if (mydb()->_error) {
							$error .= mydb()->_query.'<br />';
						} else {
							$tableComplete->rows[] = $person;
						}
					}
				} else {
					$error .= 'DUPLICATE CID '.$person['cid'];
				}
			} else {
				// Create new Person
				$stmt = 'INSERT INTO %db_person%
					(
						`uid`, `cid`, `prename`, `name`, `lname`
						, `birth`, `sex`
						, `areacode`, `hrareacode`
						, `house`, `village`, `tambon`, `ampur`, `changwat`
						, `rhouse`, `rvillage`, `rtambon`, `rampur`, `rchangwat`
						, `created`, `importseries`
					)
					VALUES
					(
						:uid, :cid, :prename, :name, :lname
						, :birth, :sex
						, :areacode, :hrareacode
						, :house, :village, :tambon, :ampur, :changwat
						, :rhouse, :rvillage, :rtambon, :rampur, :rchangwat
						, :created, :importseries
					)';

				if ($isSimulate) {
					$tableComplete->rows[] = $person;
					$psnId = 1;
				} else {
					mydb::query($stmt,$person);
					if (mydb()->_error) {
						$error .= mydb()->_query.'<br />';
						$psnId = NULL;
					} else {
						$psnId = mydb()->insert_id;
						$tableComplete->rows[] = $person;
					}
				}
			}

			if ($isDebug) $ret .= print_o($person,'$person');
			if ($isDebug) $ret .= print_o($imedCare,'$imedCare');
			if ($isDebug) $ret .= print_o($disableds,'$disableds');
			if ($isDebug) $ret .= '<hr />';


			if (empty($psnId)) continue;

			$imedCare = array();
			$imedCare['psnid'] = $psnId;
			$imedCare['uid'] = $person['uid'];
			$imedCare['careid'] = $personType;
			$imedCare['created'] = $dateCreatedU;

			if (!$isSimulate) {

				$stmt = 'INSERT INTO %imed_care%
					(`pid`, `careid`, `uid`, `created`)
					VALUES
					(:psnid, :careid, :uid, :created)
					ON DUPLICATE KEY UPDATE
					`careid` = :careid';

				mydb::query($stmt,$imedCare);

				if (mydb()->_error) $error .= mydb()->_query.'<br />';

				if ($personType == _IMED_CARE_DISABLED) {
					$disableds = array();
					$disableds['psnid'] = $psnId;
					$disableds['uid'] = $person['uid'];
					$disableds['register'] = 'จดทะเบียน';
					$disableds['created'] = $person['created'];

					//$disableds['regdate'] = $row[15]=='ไม่ระบุ'?NULL:sg::convert_thai_date($row[15]);

					$stmt = 'INSERT INTO %imed_disabled%
						(`pid`, `uid`, `register`, `created`)
						VALUES
						(:psnid, :uid, :register, :created)
						ON DUPLICATE KEY UPDATE
						`register` = :register';

					mydb::query($stmt,$disableds);

					if (mydb()->_error) $error .= mydb()->_query.'<br />';

					//$defectDetail = explode(',', $defectTypeText[9]);
					//ทางการเห็น,ทางการได้ยินหรือสื่อความหมาย,ทางการเคลื่อนไหวหรือทางร่างกาย,ทางจิตใจ,ทางสติปัญญา,ทางการเรียนรู้,ออทิสติก,พิการซ้อน
					foreach (explode(',',$defectTypeText) as $defectKey => $defectCode) {
						if (empty($defectCode)) continue;

						$defectValue = array('psnid' => $disableds['psnid']);
						$defectValue['defect'] = $defectCode;
						$defectValue['kind'] = $defectValue['detail'] = $defectDetail[$defectKey];

						if ($isDebug) $ret .= print_o($defectValue,'$defectValue');

						$stmt = 'INSERT INTO %imed_disabled_defect%
							(`pid`, `defect`, `kind`, `detail`)
							VALUES
							(:psnid, :defect, :kind, :detail)
							ON DUPLICATE KEY UPDATE
							`defect` = :defect';

						mydb::query($stmt,$defectValue);

						if (mydb()->_error) $error .= mydb()->_query.'<br />';
					}
				}
			}


			//$tables->rows[]=$row;
			//$tables->rows[]=$person;
			//$tables->rows[]=$disableds;

			if ($error) {
				$tableError->rows[] = $person;
				$tableError->rows[] = '<tr><td colspan="15"><span style="color:red;">'.$error.'</span></td></tr>';
			} else {
				$complete[] = $line;
				unset($lines[$key]);
			}
		}
		$post->data = implode("\n", $lines);
	}



	$form = new Form(NULL, url(q()), 'edit-info', 'sg-form');
	$form->addData('checkValid', true);

	$form->addField(
		'areacode',
		array(
			'type' => 'hidden',
			'name' => 'areacode',
			'value' => htmlspecialchars($areaCode),
		)
	);

	$form->addField(
		'importseries',
		array(
			'type' => 'text',
			'label' => 'Import Series',
			'require' => true,
			'value' => htmlspecialchars($importSeries),
			'posttext' => ' <span><label><input type="checkbox" name="simulate" value="Yes" '.($isSimulate?'checked="checked"':'').' /> Simulate </label></span>'
				. '<span><label><input type="checkbox" name="update" value="Yes" '.($updateData?'checked="checked"':'').' /> Update Person Info </label></span>'
				. '<span><label><input type="checkbox" name="deleteseries" value="Yes" '.($deleteOldSeries?'checked="checked"':'').' /> Delete old import series </label></span>'
				. '<label><input type="checkbox" name="showqt" value="Yes" '.($showQt?'checked="checked"':'').' /> แสดงผลแบบสอบถาม </label>'
				. '<label><input type="checkbox" name="debug" value="Yes" '.($isDebug?'checked="checked"':'').' /> Debug </label>',
		)
	);

	$form->addField(
		'uid',
		array(
			'type' => 'text',
			'label' => 'Import with userId',
			'value' => $importUid,
		)
	);

	$form->addField(
		'persontype',
		array(
			'type' => 'select',
			'label' => 'กลุ่มบุคคล',
			'class' => '-fill',
			'require' => true,
			'value' => $personType,
			'options' => array(
				'' => '== เลือกประเภท ==',
				_IMED_CARE_DISABLED => 'คนพิการ',
				_IMED_CARE_ELDER => 'ผู้สูงอายุ'
			),
		)
	);

	$form->addField(
		'areaname',
		array(
			'type' => 'text',
			'label' => 'ตำบล-อำเภอ-จังหวัด',
			'class' => 'sg-address -fill',
			'require' => true,
			'value' => htmlspecialchars($_POST['areaname']),
			'placeholder' => 'ระบุตำบล แล้วเลือกจากรายการที่แสดง',
			'attr'=>array('data-altfld'=>'edit-areacode'),
		)
	);


	$form->addField(
		'data',
		array(
			'type' => 'textarea',
			'label' => 'ข้อมูลสำหรับนำเข้า (CVS - แยกฟิลด์ด้วยเครื่องหมาย Tab )',
			'class' => '-fill',
			'rows' => 18,
			'value' => $post->data,
			'placeholder' => '[psnid],คำนำหน้าชื่อ,ชื่อ,สกุล,หมายเลขบัตรประชาชน,เพศ,วันเกิด (YYYY-MM-DD),ที่อยู่ปัจจุบัน,หมู่ที่,ตำบล,อำเภอ,จังหวัด,ที่อยู่ตามทะเบียนบ้าน,หมู่ที่,ตำบล,อำเภอ,จังหวัด,โทรศัพท์,ประเภทความพิการ',
			'description' => '[psnid],คำนำหน้าชื่อ,ชื่อ,สกุล,หมายเลขบัตรประชาชน,เพศ,วันเกิด (YYYY-MM-DD),ที่อยู่ปัจจุบัน,หมู่ที่,ตำบล,อำเภอ,จังหวัด,ที่อยู่ตามทะเบียนบ้าน,หมู่ที่,ตำบล,อำเภอ,จังหวัด,โทรศัพท์,ประเภทความพิการ',
		)
	);

	$form->addField(
		'submit',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">cloud_upload</i><span>{tr:IMPORT}</span>',
			'pretext' => '<a class="btn -link cancel" href="'.url('imed/admin/import/disableds').'" data-rel="#main"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	$ret .= '<div style="width: 100%; overflow: scroll;">';

	$tableError->thead = array_keys($person);
	$ret .= $tableError->build();

	$tableComplete->thead=array_keys(reset($tableComplete->rows));
	$ret .= $tableComplete->build();

	if ($showQt) {
		$tableQt->thead=array('pid','key','value','uid','dcreated');
		$ret .= $tableQt->build();
	}

	$ret .= '</div>';

	//$ret .= print_o(post(),'post()');

	$ret .= '</div>';

	return $ret;
}
?>