<?php
/**
* Import data รายชื่อเกษตรกร
* Created 2020-05-15
* Modify  2020-05-15
*
* @param Object $self
* @return String
*/

$debug = true;

function org_agro_import_register($self) {
	$isSimulate = post('simulate') == 'Yes';
	$areaCode = post('areacode');
	$importSeries = post('importseries');
	$personType = post('persontype');
	$sourceData = post('data');
	$updateData = post('update');

	$deleteOldSeries = post('deleteseries') == 'Yes';
	$showQt = post('showqt') == 'Yes';
	$isDebug = post('debug');

	$splitAddress = false;


	$ret .= '<div style="width: 100%;">';
	$ret .= '<header class="header"><h3>นำเข้าข้อมูลเกษตรกรจากหน่วยงานที่รับลงทะเบียน</h3></header>';

	$tableError = new Table();
	$tableError->caption='รายการนำเข้าผิดพลาด';

	$tableComplete = new Table();
	$tableComplete->caption='รายการนำเข้าสมบูรณ์';

	$tableQt = new Table();
	$tableQt->caption='รายการนำเข้าแบบสอบถาม';


	//ที่	,คำนำหน้า,ชื่อ,สกุล,หมายเลขบัตรประชาชน,สถานที่อยู่อาศัย (เลขที่,หมู่ที่,ตำบล,อำเภอ,จังหวัด),ผลผลิต(ประเภทผลผลิต,ชื่อผลผลิต),สถานที่ตั้งแปลง(เลขที่,หมู่ที่,ตำบล,อำเภอ,จังหวัด),พิกัดแปลง,มาตรฐานสินค้า,พื้นที่ผลิต(ไร่-งาน-ตร.ว.),ตลาดที่จำหน่าย,สังกัดกลุ่ม,โทรศัพท์,ช่องทางการสื่อสารอื่นๆ


	if ($deleteOldSeries && $importSeries) {
		$stmt = 'DELETE FROM %agro_reg% WHERE `importseries` = :importseries';
		mydb::query($stmt, ':importseries', $importSeries);
		mydb::clear_autoid('%agro_reg%');
	}

	if ($importSeries && $sourceData) {
		$lines = explode("\n", $sourceData);
		$sep = "\t";

		foreach ($lines as $key=>$line) {
			unset($error);
			$person = array();
			$line = trim($line);
			if (empty($line)) continue;

			$row = str_getcsv($line,$sep);
			//$ret.=print_o($row,'$row');
			foreach ($row as $k=>$v) $row[$k] = trim($v);

			if ($row[0] == 'ที่') {
				unset($lines[$key]);
				continue;
			}

			$person['aid'] = SG\getFirst($row[0]);
			$person['prename'] = $row[1];
			$person['name'] = $row[2];
			$person['lname'] = $row[3];
			$person['cid'] = trim(preg_replace('/\W/','',$row[4]));
			$person['areacode'] = NULL;
			$person['house'] = $row[5];
			$person['village'] = $row[6];
			$person['tambon'] = $row[7];
			$person['ampur'] = $row[8];
			$person['changwat'] = $row[9];


			if ($isDebug) $ret .= print_o($row,'$row');


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
				$addressReal = SG\explode_address($row[5]. 'ม.'.$row[6].' ต.'.$row[7].' อ.'.$row[8].' จ.'.$row[9], $areaCode);
				//$ret .= print_o($addressReal, '$addressReal');

				$person['areacode'] = $addressReal['changwatCode']. $addressReal['ampurCode']. $addressReal['tambonCode']. $addressReal['villageCode'];

				$person['areacode'] = $addressReal['areaCode'];

				/*
				$addressReg = SG\explode_address($row[12]
					. ($row[13] ? 'ม.'.$row[13] : '')
					. ($row[14] ? ' ต.'.$row[14] : '')
					. ($row[15] ? ' อ.'.$row[15] : '')
					. ($row[16] ? ' จ.'.$row[16] : ''),
					$areaCode
				);
				*/

			}

			$person['landhouse'] = $row[12];
			$person['landvillage'] = $row[13];
			$person['landtambon'] = $row[14];
			$person['landampur'] = $row[15];
			$person['landchangwat'] = $row[16];

			$person['producttype'] = $row[10];
			$person['productname'] = $row[11];
			$person['landlocation'] = $row[17];
			$person['standard'] = $row[18];
			$person['rai'] = $row[19];
			$person['han'] = $row[20];
			$person['wa'] = $row[21];
			$person['market'] = $row[22];
			$person['ingroup'] = $row[23];
			$person['phone'] = $row[24];

			//$person['educate']=$educates[$row[13]];

			$person['orgid'] = NULL;
			$person['uid'] = i()->ok ? i()->uid : NULL;
			$person['created'] = date('U');
			$person['importseries'] = $importSeries;

			/*
			if (empty($person['cid'])) {
				$ret .= '<p>NO CID'.$person['no'].'. '.$person['name'].' '.$person['lname'].'</p>';
				continue;
			}

			$psnId = mydb::select('SELECT `psnid` FROM %db_person% WHERE `cid` = :cid LIMIT 1', $person)->psnid;
			*/


			// Create new record
			$stmt = 'INSERT INTO %agro_reg%
				(
					`orgid`, `uid`, `prename`, `name`, `lname`, `cid`
					, `areacode`
					, `house`, `village`, `tambon`, `ampur`, `changwat`
					, `producttype`, `productname`
					, `landhouse`, `landvillage`, `landtambon`, `landampur`, `landchangwat`
					, `landlocation`, `standard`, `rai`, `han`, `wa`
					, `market`, `ingroup`, `phone`
					, `created`, `importseries`
				)
				VALUES
				(
					:orgid, :uid, :prename, :name, :lname, :cid
					, :areacode
					, :house, :village, :tambon, :ampur, :changwat
					, :producttype, :productname
					, :landhouse, :landvillage, :landtambon, :landampur, :landchangwat
					, :landlocation, :standard, :rai, :han, :wa
					, :market, :ingroup, :phone
					, :created, :importseries
				)';

			if ($isSimulate) {
				$tableComplete->rows[] = $person;
				$psnId = 1;
			} else {
				mydb::query($stmt, $person);
				if (mydb()->_error) {
					$error .= mydb()->_query.'<br />';
					$psnId = NULL;
				} else {
					$psnId = mydb()->insert_id;
					$tableComplete->rows[] = $person;
				}
			}

			if ($isDebug) $ret .= print_o($person,'$person');

			//if (empty($psnId)) continue;

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





	// Show import form
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

	/*
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
	*/

	$form->addField(
		'data',
		array(
			'type' => 'textarea',
			'label' => 'ข้อมูลสำหรับนำเข้า (CVS - แยกฟิลด์ด้วยเครื่องหมาย Tab )',
			'class' => '-fill',
			'rows' => 18,
			'value' => $post->data,
		)
	);

	$form->addField(
		'submit',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">cloud_upload</i><span>{tr:UPLOAD}</span>',
			'pretext' => '<a class="btn -link cancel" href="'.url('org/agro/import/register').'" data-rel="#main"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
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