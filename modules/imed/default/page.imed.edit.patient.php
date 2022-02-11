<?php
/**
* Update patient information
*
* @param Integer $psnId
* @param Array _REQUEST
* @return json
*/

$debug = true;

import('model:imed.visit');

function imed_edit_patient($self) {
	$values = post();
	$isDebugable = debug('inline');

	list($group, $part) = explode(':', $values['group']);
	$psnId = intval(trim(SG\getFirst($values['psnid'],$values['id'],$values['pid'])));
	$fieldUpdate = trim($values['fld']);
	$tranId = trim($values['tr']);
	$value = trim(strip_tags($values['value']));
	list($returnType,$formatReturn) = explode(':',$values['ret']);
	$action = $values['action'];
	$dataType = $values['type'];
	//		if ($value=='...' || trim($value)=='') return array('value'=>'...');

	$ret = [
		'tr' => $tranId,
		'value' => $retvalue=$value,
		'error' => '',
		'debug' => 'psnid = '.$psnId.' action='.$action.', group='.$group.', part='.$part.', pid='.$psnId.', fld='.$fieldUpdate.', tr='.$tranId.'<br />Value='.$value.'<br />',
	];

	if (!$action) return $ret;

	$ret['msg'] = 'บันทึกเรียบร้อย';
	$save = $values['action'] == 'save' && $psnId && $group && $fieldUpdate;


	if ($values['cancel']) $ret['error']='ยกเลิกการแก้ไข';
	else if ($values['action'] != 'save' || empty($psnId) || empty($group) || empty($fieldUpdate)) $ret['error']='Invalid parameter';




	$isDebugable = user_access('access debugging program');

	$psnInfo = imed_model::get_patient($psnId);

	if (is_admin('imed')) {
		// Have all right
	} else if ($group == 'service') {
		$stmt = 'SELECT * FROM %imed_service% WHERE seq = :seq LIMIT 1';
		$serviceRs = mydb::select($stmt, ':seq', $tranId);
		if ($serviceRs->uid != i()->uid) {
			$ret['error'] = 'Access denied';
		}
	} else if ($psnInfo->uid == i()->uid) {
		// OK, Creater has right to edit
	} else  if ($zones = imed_model::get_user_zone(i()->uid,'imed')) {
		// If has zone right, check if in my zone
		$right = imed_model::in_my_zone($zones,$psnInfo->changwat,$psnInfo->ampur,$psnInfo->tambon);
		if (!$right) $ret['error'] = 'ข้อมูลชุดนี้อยู่นอกพื้นที่การดูแลของท่าน หากข้อมูลนี้ไม่ถูกต้อง กรุณาแจ้งผู้ดูแลระบบ!!!!!';
		else if ($right->right != 'edit') $ret['error'] = 'ขออภัย ท่านไม่ได้รับสิทธิ์ในการแก้ไขข้อมูลนี้';
	} else if (!(user_access('create imed at home'))) {
		// If not right and not creatable, exit
		$ret['error']='Access denied';
	}


	if ($ret['error']) {
		$ret['msg'] = $ret['error'];
		return $ret;
	}




	if ($psnInfo->birth == '0000-00-00') $psnInfo->birth = '';
	$log_name = $psnId.':'.$psnInfo->name.' '.$psnInfo->lname;

	/*
	if (is_string($value)) {
		$value=trim(strip_tags($value));
		if (strrpos($value,'...')!=false) $value=substr($value,0,strrpos($value,'...'));
	}
	if ($dataType=='datepicker') {
		// Convert date from dd/mm/yyyy to yyyy-mm-dd
		list($dd,$mm,$yy)=explode('/',$value);
		if ($yy>2400) $yy=$yy-543;
		$value=sprintf('%04d',$yy).'-'.sprintf('%02d',$mm).'-'.sprintf('%02d',$dd);
		$ret['debug'].='Convert date '.$value.'<br />';
	}
	*/


	if ($returnType == 'date') { // Convert date from dd/mm/yyyy to yyyy-mm-dd
		$dateTableFormat = SG\getFirst($values['convert'],'Y-m-d');
		$value = sg_date($value,$dateTableFormat);
	} else if ($dataType == "money" || $dataType == "numeric") { // Remove none numeric charector
		$value=preg_replace('/[^0-9\.\-]/','',$value);
		$ret['debug'] .= ' => to numeric '.$value;
	}
	$ret['debug'] .= '<br />';


	$values['value'] = $value;
	$values['psnId'] = $psnId;
	$values['tranId'] = $tranId;
	unset($values['tr'],$values['fld']);

	$fieldList = explode(',', $fieldUpdate);
	foreach (explode(',', $fieldUpdate) as $item) $values[$item] = $values[$item];
	list($fristField) = explode(',',$fieldUpdate);
	$values[$fristField] = $value;


	// Update patient information

	$log_keyword = 'modify';

	switch ($group) {

		case 'property' :
			if ($part && $fieldUpdate && $tpid) property($part.':'.$fieldUpdate.':'.$psnId,$value);
			$ret['debug'].='<p>Update property</p>';
			break;

		case 'person' :
			if ($fieldUpdate=='name') {
				// Update name and lname
				list($name,$lname) = sg::explode_name(' ',$value);
				$fieldUpdate = 'name,lname';
				$values['name'] = $name;
				$values['lname'] = $lname;
			} else if ($fieldUpdate=='address') {
				// Update current address

				$address = SG\explode_address($value, post('areacode'));
				$values['house'] = $address['house'];
				$values['village'] = $address['village'];
				$values['tambon'] = $address['tambonCode'];
				$values['ampur'] = $address['ampurCode'];
				$values['changwat'] = $address['changwatCode'];
				$fieldUpdate = 'house,village,tambon,ampur,changwat';
				$ret['debug'].=print_o($address,'$address');

				/*

				list($address,$areacode)=explode('|', $value);
				if (preg_match('/(.*)(หมู่|หมู่ที่|ม\.)([0-9\s]+)\s+(.*)/',$address,$out) || preg_match('/(.*)(ตำบล|ต\.)(.*)/',$address,$out)) {
					$stmt='UPDATE %db_person% SET `house`=:house, `village`=:village WHERE `psnid`=:psnId LIMIT 1';
					//$tambon=$value;//['tambon'];
					$out[3]=trim($out[3]);
					$values['house']=trim($out[1]);
					$values['village']=(in_array($out[2],array('หมู่','หมู่ที่','ม.')) && is_numeric($out[3]))?$out[3]:'';
					if ($tambon) {
						$values['tambon']=substr($tambon,4,2);
						$values['ampur']=substr($tambon,2,2);
						$values['changwat']=substr($tambon,0,2);
						$stmt='UPDATE %db_person% SET `house`=:house, `village`=:village, `tambon`=:tambon, `ampur`=:ampur, `changwat`=:changwat WHERE `psnid`=:psnId LIMIT 1';
					}
					$log_message='แก้ไข: pid['.$log_name.'] ที่อยู่ปัจจุบัน ['.$psnInfo->address.'] เป็น ['.print_r($values,1).']';
				}
				*/
			} else if ($fieldUpdate=='raddress') {
				// Update register address

				$address = SG\explode_address($value, post('areacode'));
				$values['rhouse'] = $address['house'];
				$values['rvillage'] = $address['village'];
				$values['rtambon'] = $address['tambonCode'];
				$values['rampur'] = $address['ampurCode'];
				$values['rchangwat'] = $address['changwatCode'];
				$fieldUpdate = 'rhouse,rvillage,rtambon,rampur,rchangwat';
				$ret['debug'].=print_o($address,'$address');

				/*
				list($address,$tambon)=explode('|', $value);
				if (preg_match('/(.*)(หมู่|หมู่ที่|ม\.)([0-9\s]+)\s+(.*)/',$address,$out) || preg_match('/(.*)(ตำบล|ต\.)(.*)/',$address,$out)) {
					$stmt='UPDATE %db_person% SET `rhouse`=:house, `rvillage`=:village WHERE `psnid`=:psnId LIMIT 1';
					//$tambon=$value['tambon'];
					$out[3]=trim($out[3]);
					$values['house']=trim($out[1]);
					$values['village']=(in_array($out[2],array('หมู่','หมู่ที่','ม.')) && is_numeric($out[3]))?$out[3]:'';
					if ($tambon) {
						$values['tambon']=substr($tambon,4,2);
						$values['ampur']=substr($tambon,2,2);
						$values['changwat']=substr($tambon,0,2);
						$stmt='UPDATE %db_person% SET `rhouse`=:house, `rvillage`=:village, `rtambon`=:tambon, `rampur`=:ampur, `rchangwat`=:changwat WHERE `psnid`=:psnId LIMIT 1';
					}
					$log_message='แก้ไข: pid['.$log_name.'] ที่อยู่ตามทะเบียนบ้าน ['.$psnInfo->raddress.'] เป็น ['.print_r($values,1).']';
				}
				*/
			}

			// Update field in db_person
			if ($fieldUpdate) {
				$stmt = 'UPDATE %db_person% SET '.mydb::create_fieldupdate($fieldUpdate.',modify,umodify').' WHERE `psnid` = :psnId LIMIT 1';
			}

			$values['modify'] = date('U');
			$values['umodify'] = i()->uid;

			$log_message='แก้ไข: pid['.$log_name.'] '.$fieldUpdate.' ['.$rs->{$fieldUpdate}.'] เป็น ['.$value.']';

			//mydb::query('UPDATE %db_person% SET modify=:modify, umodify=:umodify WHERE psnid=:psnId LIMIT 1',':psnId',$psnId, ':modify',date('U'), ':umodify',i()->uid);
			break;

		case 'disabled' : // Update field in db_disabled
			if (!$psnInfo->disabled->pid) {
				mydb::query('INSERT INTO %imed_disabled% (`pid`, `uid`, `created`) VALUES (:psnId, :uid, :created)',':psnId',$psnInfo->psnId,':uid',i()->uid,':created',date('U'));
			}
			if ($fieldUpdate=='discharge' && $value<=0) $value=NULL;
			$stmt='UPDATE %imed_disabled% SET `'.$fieldUpdate.'`=:value WHERE `pid`=:psnId LIMIT 1';
			mydb::query('UPDATE %imed_disabled% SET modify=:modify, umodify=:umodify WHERE pid=:psnId LIMIT 1',':psnId',$psnId, ':modify',date('U'), ':umodify',i()->uid);
			$log_message='แก้ไข: pid['.$log_name.'] disabled field '.$fieldUpdate.' ['.$psnInfo->disabled->{$fieldUpdate}.'] เป็น ['.$value.']';
			break;

		case 'defect' :
			$defectList=array(1=>'DSBL.DEFECT.VISUAL', 2=>'DSBL.DEFECT.HEARING', 3=>'DSBL.DEFECT.MOVEMENT', 4=>'DSBL.DEFECT.MENTAL', 5=>'DSBL.DEFECT.INTELLECTUAL', 6=>'DSBL.DEFECT.LEARNING', 7=>'DSBL.DEFECT.AUTISTIC');
			if (in_array($fieldUpdate,$defectList)) {
				if (!$psnInfo->disabled->pid) {
					mydb::query('INSERT INTO %imed_disabled% (`pid`, `uid`, `created`) VALUES (:psnId, :uid, :created)',':psnId',$psnInfo->psnId,':uid',i()->uid,':created',date('U'));
				}
				if ($value) {
					$stmt='INSERT INTO %imed_disabled_defect% (`pid`, `defect`) VALUES (:psnId, :value) ON DUPLICATE KEY UPDATE `defect` = :value';
				} else {
					$defectId = array_search ($fieldUpdate, $defectList);
					// if ($defectId) $stmt='DELETE FROM %imed_disabled_defect% WHERE `pid`=:psnId AND `defect`='.$defectId.' LIMIT 1';
				}
			} else if ($tranId) {
				$values['defect']=intval($tranId);
				if (is_numeric($value)) $value=intval($value);
				$stmt='UPDATE %imed_disabled_defect% SET `'.$fieldUpdate.'`=:value WHERE `pid`=:psnId AND `defect`=:defect LIMIT 1';
			}
			mydb::query('UPDATE %imed_disabled% SET modify=:modify, umodify=:umodify WHERE pid=:psnId LIMIT 1',':psnId',$psnId, ':modify',date('U'), ':umodify',i()->uid);
			break;

		case 'carer' :
			if ($tranId) {
				if ($fieldUpdate=='created') $value=sg_date($value,'U');
				/*
				if ($fieldUpdate=='created') {
					list($dd,$mm,$yy)=explode('/',$value);
					if ($yy>2400) $yy=$yy-543;
					$value=sg_date(sprintf('%04d',$yy).'-'.sprintf('%02d',$mm).'-'.sprintf('%02d',$dd),'U');
				}
				*/
				$stmt='UPDATE %imed_tr% SET `'.$fieldUpdate.'`=:value WHERE `tr_id`=:tr_id LIMIT 1';
				$values['tr_id']=$tranId;
				mydb::query('UPDATE %imed_disabled% SET modify=:modify, umodify=:umodify WHERE pid=:psnId LIMIT 1',':psnId',$psnId, ':modify',date('U'), ':umodify',i()->uid);
				$log_message='แก้ไข: pid['.$log_name.'] carer field '.$fieldUpdate.' trid '.$tranId.' เป็น ['.$value.']';
			}
			break;

		case 'qt' :
			$stmt = 'INSERT INTO %imed_qt%
				( `qid`, `pid`, `part`, `value`, `ucreated`, `dcreated`)
				VALUES
				( :tranId, :psnId, :part, :value, :ucreated, :dcreated)
				ON DUPLICATE KEY UPDATE
				  `value` = :value
				, `umodify` = :umodify, `dmodify` = :dmodify';
			$values['part'] = $part;
			$values['ucreated'] = i()->uid;
			$values['dcreated'] = date('U');
			$values['umodify'] = i()->uid;
			$values['dmodify'] = date('U');
			//unset($values[$fieldUpdate]);
			break;

		case 'service' :
			$stmt = 'UPDATE %imed_service% SET '.mydb::create_fieldupdate($fieldUpdate).' WHERE `seq` = :tranId LIMIT 1';
			ImedVisitModel::firebaseChanged($psnId, $tranId);
			break;
	}

	// Save value into table
	if ($stmt) {
		mydb::query($stmt, $values);

		if (mydb()->_error) $ret['msg'] = 'ERROR : มีความผิดพลาดในการบันทึกข้อมูล'.($isDebugable ? '<div style="white-space:normal">'.mydb()->_query.'</div>' : '');
		if (in_array($group,array('qt')) && empty($tranId)) {
			$tranId = $ret['tr'] = mydb()->insert_id;
			$ret['debug'] .= 'TRANID = '.$tranId;
		}
		$ret['debug'] .= 'STMT : '.str_replace("\r", '<br />', $stmt).'<br />';
		$ret['debug'] .= 'QUERY : '.str_replace("\r", '<br />', mydb()->_query).'<br />';
		$ret['debug'] .= 'RETURN TRANID = '.$tranId.' ';
	}
	// Update log message
	if ($log_message) model::watch_log('imed',$log_keyword,$log_message);

	// Get updated patient information
	$psnInfo=imed_model::get_patient($psnId);

	// Set return value
	if ($fieldUpdate=='address') $ret['value']=$psnInfo->address;
	if ($fieldUpdate=='raddress') $ret['value']=$psnInfo->raddress;
	else if ($returnType=='text') {
		$ret['value']=str_replace("\n",'<br />',$value);
	} else if ($returnType=='html') {
		$ret['value']=sg_text2html($value);
	} else if (substr($returnType,0,4)=='date') {
		$ret['value']=sg_date($value,$format);
	} else if ($returnType=='money') {
		$ret['value']=number_format($value,2);
	}
	$ret['debug'].='Return type='.$returnType.' Return value = '.$ret['value'].'<br />';

	unset($psnInfo->_query,$psnInfo->disabled->_query);
	//$ret['debug'].=print_o($psnInfo,'$psnInfo');

	$ret['values'] = $values;

	$ret['debug'] .= print_o($values,'values');

	if (!$isDebugable) unset($ret['debug']);

	if (!_AJAX) $ret['location']=array('imed?pid='.$psnId);
	return $ret;
}
?>