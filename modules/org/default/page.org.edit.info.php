<?php
/**
 * Edit follow form
 *
 * @param Integer $pid
 * @return XML and die
 */
function org_edit_info($self, $orgId = NULL) {
	$post = post();
	$isDebugable = debug('inline');

	if ($orgId) $post['keyid'] = $orgId;
	list($group,$part) = explode(':',$post['group']);
	$pid = intval(trim(SG\getFirst($post['id'],$post['pid'])));
	$fieldUpdate = trim($post['fld']);
	$tranId = trim($post['tr']);
	$value = $post['value'];
	list($returnType,$formatReturn) = explode(':',$post['ret']);
	$action = $post['action'];
	$dataType = $post['type'];

	$ret['tr'] = $tranId;
	$ret['value'] = $retvalue = $value;
	$ret['error'] = '';
	$ret['debug'] = 'action='.$action.', group='.$group.', orgId = '.$orgId.' fld='.$fieldUpdate.', tr='.$tranId.'<br />Value = '.$value;
	//$ret['debug'].= '0 = '.mydb()->_query.'<br />';
	//return $ret;
	if (!$action) return $ret;

	$ret['msg']='บันทึกเรียบร้อย';
	$save=$post['action']=='save' && $pid && $group && $fieldUpdate;
	$values=NULL;

	if ($returnType=='date') { // Convert date from dd/mm/yyyy to yyyy-mm-dd
		$dateTableFormat = SG\getFirst($post['convert'],'Y-m-d');
		$value = sg_date($value,$dateTableFormat);
	} else if ($dataType=="money" || $dataType=="numeric") { // Remove none numeric charector
		$value=preg_replace('/[^0-9\.\-]/','',$value);
		$ret['debug'].=' => to numeric '.$value;
	}
	$ret['debug'].='<br />';

	/*
	$rs=imed_model::get_partient($pid);

	if ($post['cancel']) $ret['error']='ยกเลิกการแก้ไข';
	if ($rs->uid==i()->uid) {
	} else  if ($zones=imed_model::get_user_zone(i()->uid,'imed')) {
		$right=imed_model::in_my_zone($zones,$rs->changwat,$rs->ampur,$rs->tambon);
		if (!$right) $ret['error']='ข้อมูลชุดนี้อยู่นอกพื้นที่การดูแลของท่าน หากข้อมูลนี้ไม่ถูกต้อง กรุณาแจ้งผู้ดูแลระบบ';
		else if ($right->right!='edit') $ret['error']='ขออภัย ท่านไม่ได้รับสิทธิ์ในการแก้ไขข้อมูลนี้';
	} else if (!(user_access('create imed at home'))) $ret['error']='Access denied';
	*/

	if ($action=='save') {
		if (empty($group) || empty($fieldUpdate)) $ret['error']='Invalid parameter';
		if ($ret['error']) {
			$ret['msg']=$ret['error'];
			return $ret;
		}

		if (is_string($value)) {
			$value=trim(strip_tags($value));
			if (strrpos($value,'...')!=false) $value=substr($value,0,strrpos($value,'...'));
		}

		$values = post();
		unset($values['group']);
		$values['orgId'] = $orgId;
		$values['part'] = $part;
		$values['value'] = $value;
		$values['tranId'] = $tranId;
		unset($values['tr'],$values['fld']);

		$fieldList = explode(',', $fieldUpdate);
		foreach (explode(',', $fieldUpdate) as $item) $values[$item] = $values[$item];
		list($fristField) = explode(',',$fieldUpdate);
		$values[$fristField] = $value;


		// Update project transaction
		$log_keyword='modify';
		switch ($group) {

			case 'property' :
				if ($part && $fieldUpdate && $tpid) property($part.':'.$fieldUpdate.':'.$pid,$value);
				$ret['debug'].='<p>Update property</p>';
				break;

			case 'org' :
				if ($values['house']) {
					$address = SG\explode_address($values['house'], $values['areacode']);
					$values['house'] = $address['house'];
					$ret['value'] = SG\implode_address($address);
					$ret['debug'] .= print_o($address,'$address');
					$ret['debug'] .= print_o($values,'$values');
				}
				$stmt = 'UPDATE %db_org% SET '.mydb::create_fieldupdate($fieldUpdate).' WHERE `orgid` = :tranId LIMIT 1';
				$log_message='แก้ไข: orgid['.$log_name.'] '.$fieldUpdate.' ['.$rs->{$fieldUpdate}.'] เป็น ['.$value.']';
				break;

			case 'person' :
				if ($fieldUpdate == 'name') {
					// Update name and lname
					list($name,$lname) = sg::explode_name(' ',$value);
					$fieldUpdate = 'name,lname';
					$values['name'] = $name;
					$values['lname'] = $lname;
				} else if ($fieldUpdate == 'address') {
					$address = SG\explode_address($value, post('areacode'));
					$values['house'] = $address['house'];
					$values['village'] = $address['village'];
					$values['tambon'] = $address['tambonCode'];
					$values['ampur'] = $address['ampurCode'];
					$values['changwat'] = $address['changwatCode'];
					$fieldUpdate = 'house,village,tambon,ampur,changwat';
				}

				/*
				} else if ($fieldUpdate=='latlng') {
					// Update latlng
					$latlng='func.PointFromText("POINT('.preg_replace('/,/',' ',$value).')")';
					if ($rs->gis) {
						mydb::query('UPDATE %gis% SET `latlng`=:latlng WHERE `gis`=:gis LIMIT 1',':gis',$rs->gis,':latlng',$latlng);
						$gis=$rs->gis;
					} else {
						mydb::query('INSERT INTO %gis% SET `table`=:table,`latlng`=:latlng',':table','sgz_db_person',':latlng',$latlng);
						$gis=mydb()->insert_id;
					}
					mydb::query('INSERT INTO %imed_partient_gis% SET `pid`=:tranId, `uid`=:uid, `latlng`=:latlng, `created`=:created',':tranId',$pid,':uid',SG\getFirst(i()->uid,'func.NULL'),':latlng',$latlng,':created',date('U'));
					$values['gis']=$gis;
					$stmt='UPDATE %db_person% SET `gis`=:gis WHERE `psnid`=:tranId LIMIT 1';
					$log_message='แก้ไข: pid['.$log_name.'] GIS ['.$rs->latlng.'] เป็น ['.$value.']';
				}
				*/

				// Update field in db_person
				$stmt = 'UPDATE %db_person% SET '.mydb::create_fieldupdate($fieldUpdate.',modify,umodify').' WHERE `psnid` = :tranId LIMIT 1';

				$values['modify'] = date('U');
				$values['umodify'] = i()->uid;

				$log_message='แก้ไข: pid['.$log_name.'] '.$fieldUpdate.' ['.$rs->{$fieldUpdate}.'] เป็น ['.$value.']';

				break;

			case 'bigdata' :
				$stmt = 'INSERT INTO %bigdata%
					(`bigid`, `keyname`, `keyid`, `fldname`, `flddata`, `ucreated`, `created`)
					VALUES
					(:tranId, :keyname, :keyid, :fldname, :flddata, :ucreated, :dcreated)
					ON DUPLICATE KEY UPDATE
					`flddata`=:value, `modified`=:modified, `umodified`=:umodified';
				$values['keyname']=$part;
				$values['fldname']=$fieldUpdate;
				$values['flddata']=$value;
				$values['ucreated']=i()->uid;
				$values['dcreated']=date('U');
				$values['umodified']=i()->uid;
				$values['modified']=date('U');
				break;

			case 'doing' :
				$stmt = 'UPDATE %org_doings% SET '.mydb::create_fieldupdate($fieldUpdate).' WHERE `doid` = :tranId LIMIT 1';
				break;

			case 'map' :
				if ($fieldUpdate == 'orgname') {
					// Add orgid or new orgname
					if (empty($values['reforg']) && $values['orgname']) {
						mydb::query('INSERT INTO %db_org% (`name`, `uid`, `created`) VALUES (:name, :uid, :created)', ':name', $values['orgname'], ':uid',i()->uid, ':created', date('U'));
						$values['reforg'] = mydb()->insert_id;
					}
					if ($values['reforg']) {
						$stmt='INSERT INTO %bigdata%
										(`keyname`, `keyid`, `fldname`, `fldref`, `ucreated`, `created`)
										VALUES
										(:keyname, :keyid, :fldname, :fldref, :ucreated, :dcreated)';
						$values['keyname']='map';
						$values['keyid']=$tranId;
						$values['fldname']='orgid';
						$values['fldref']=$values['reforg'];
						$values['ucreated']=i()->uid;
						$values['dcreated']=date('U');
						$values['umodified']=i()->uid;
						$values['modified']=date('U');
						//mydb::query($stmt, $bigValue);
					}
				} else {
					if ($fieldUpdate == 'sector') {
						list($values['sector']) = explode(':',$values['sector']);
					}
					$stmt = 'UPDATE %map_networks% SET '.mydb::create_fieldupdate($fieldUpdate).' WHERE `mapid` = :tranId LIMIT 1';
					$log_message = 'แก้ไข: map_networks['.$tranId.'] '.$fieldUpdate.' ['.$rs->{$fieldUpdate}.'] เป็น ['.$value.']';
				}
				break;

			case 'subject':
				if ($value) {
					$stmt = 'INSERT INTO %org_subject% (`orgId`, `subject`) VALUES (:orgId, :part) ON DUPLICATE KEY UPDATE `subject` = :part';
				} else {
					$stmt = 'DELETE FROM %org_subject% WHERE `orgId` = :orgId AND `subject` = :part LIMIT 1';
				}
				break;
		}

		// Save value into table
		if ($stmt) {
			mydb::query($stmt, $values);
			if (in_array($group,array('qt','bigdata')) && empty($tranId)) {
				$tranId = $ret['tr'] = mydb()->insert_id;
			}
			$ret['debug'] .= 'STMT &nbsp;&nbsp;&nbsp;: '.str_replace("\r", '<br />', $stmt).'<br />';
			$ret['debug'] .= 'QUERY : '.str_replace("\r", '<br />', mydb()->_query).'<br />';
			$ret['debug'] .= 'RETURN TRANID = '.$tranId.' ';
		}
		// Update log message
		if ($log_message) model::watch_log('org',$log_keyword,$log_message);

		// Get updated partient information
		//$rs=imed_model::get_partient($pid);

		// Set return value
		//if ($fieldUpdate=='address') $ret['value']=$rs->address;
		//if ($fieldUpdate=='raddress') $ret['value']=$rs->raddress;
		if ($returnType=='text') {
			$ret['value']=str_replace("\n",'<br />',$value);
		} else if ($returnType=='html') {
			$ret['value']=sg_text2html($value);
		} else if ($returnType=='date') {
			$ret['value']=sg_date($value,$formatReturn);
		} else if ($returnType=='money') {
			$ret['value']=number_format($value,2);
		}
		$ret['debug'].='RETURN TYPE = '.$returnType.' RETURN VALUE = '.$ret['value'].'<br />';

		unset($rs->_query,$rs->disabled->_query);
		//$ret['debug'].=print_o($rs,'$rs');
	}

	$ret['values'] = $values;
	$ret['debug'] .= print_o($values,'values');

	if (!$isDebugable) unset($ret['debug']);

	if (!_AJAX) $ret['location'] = array('org');
	return $ret;
}

?>