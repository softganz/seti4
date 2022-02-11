<?php
function project_assessor_edit($self,$psnid=NULL) {
	$post=post();
	list($group,$part)=explode(':',$post['group']);
	$fld=trim($post['fld']);
	$tr=trim($post['tr']);
	$value=$post['value'];
	$return=$post['ret'];
	$action=$post['action'];
	$dataType=$post['type'];

	$ret['tr']=$tr;
	$ret['value']=$retvalue=$value;
	$ret['error']='';
	$ret['debug'].='action='.$action.', psnid='.$psnid.', group='.$group.', fld='.$fld.', tr='.$tr.'<br />';
	$ret['debug'].='Value='.$value;

	if (!$action) return $ret;

	$ret['msg']='บันทึกเรียบร้อย';
	$save=$post['action']=='save' && $psnid && $group && $fld;
	$values=NULL;

	if ($dataType=='datepicker') { // Convert date from dd/mm/yyyy to yyyy-mm-dd
		list($dd,$mm,$yy)=explode('/',$value);
		if ($yy>2400) $yy=$yy-543;
		$value=sprintf('%04d',$yy).'-'.sprintf('%02d',$mm).'-'.sprintf('%02d',$dd);
		$ret['debug'].='Convert='.$post['convert'];
		if ($post['convert']) $value=sg_date($value.' 00:00:00',$post['convert']);
		$ret['debug'].=' => to date '.$value;
	} else if ($dataType=="money" || $dataType=="numeric") { // Remove none numeric charector
		$value=preg_replace('/[^0-9\.\-]/','',$value);
		$ret['debug'].=' => to numeric '.$value;
	}
	$ret['debug'].='<br />';

	$isEdit=false;
	if ($psnid && i()->ok) {
		$psnInfo=R::Model('person.get',$psnid);
		$assessorUid=mydb::select('SELECT `uid` FROM %person_group% WHERE `groupname`="assessor" AND `psnid`=:psnid LIMIT 1',':psnid',$psnid)->uid;
		$ret['debug'].='$assessorUid='.$assessorUid;
		if (user_access('access administrator pages,administer projects,administrator orgs') || $psnInfo->uid==$psnid || $assessorUid==i()->uid) {
			$isEdit=true;
		}
	}

	/*
	$rs=imed_model::get_partient($psnid);

	if ($post['cancel']) $ret['error']='ยกเลิกการแก้ไข';
	if ($rs->uid==i()->uid) {
	} else  if ($zones=imed_model::get_user_zone(i()->uid,'imed')) {
		$right=imed_model::in_my_zone($zones,$rs->changwat,$rs->ampur,$rs->tambon);
		if (!$right) $ret['error']='ข้อมูลชุดนี้อยู่นอกพื้นที่การดูแลของท่าน หากข้อมูลนี้ไม่ถูกต้อง กรุณาแจ้งผู้ดูแลระบบ';
		else if ($right->right!='edit') $ret['error']='ขออภัย ท่านไม่ได้รับสิทธิ์ในการแก้ไขข้อมูลนี้';
	} else if (!(user_access('create imed at home'))) $ret['error']='Access denied';
	*/

	if ($action=='save') {
		if (empty($group) || empty($fld)) $ret['error']='Invalid parameter';
		if ($ret['error']) {
			$ret['msg']=$ret['error'];
			return $ret;
		}

		$values=array();
		if (is_string($value)) {
			$value=trim(strip_tags($value));
			if (strrpos($value,'...')!=false) $value=substr($value,0,strrpos($value,'...'));
		}


		// Update project transaction
		$log_keyword='modify';
		switch ($group) {

			case 'person' :
				$values['psnid']=$psnid;
				if ($fld=='name') {
					// Update name and lname
					list($name,$lname)=sg::explode_name(' ',$value);
					$stmt='UPDATE %db_person% SET `name`=:name, `lname`=:lname WHERE `psnid`=:psnid LIMIT 1';
					$values['name']=$name;
					$values['lname']=$lname;
					$log_message='แก้ไข: psnid['.$log_name.'] ชื่อ ['.$rs->name.' '.$rs->lname.'] เป็น ['.$value.']';
				} else if ($fld=='address') {
					// Update address
					list($address,$tambon)=explode('|', $value);
					$addr=SG\explode_address($address);
					$values['house']=$addr['house'];
					$values['village']=$addr['village'];
					$values['zip']=$addr['zip'];
					if ($tambon) {
						$values['tambon']=substr($tambon,4,2);
						$values['ampur']=substr($tambon,2,2);
						$values['changwat']=substr($tambon,0,2);
						$stmt='UPDATE %db_person% SET `house`=:house, `village`=:village, `tambon`=:tambon, `ampur`=:ampur, `changwat`=:changwat WHERE `psnid`=:psnid LIMIT 1';
					} else {
						$stmt='UPDATE %db_person% SET `house`=:house, `village`=:village WHERE `psnid`=:psnid LIMIT 1';
					}
				} else if ($fld=='latlng') {
					// Update latlng
					$latlng='func.PointFromText("POINT('.preg_replace('/,/',' ',$value).')")';
					if ($rs->gis) {
						mydb::query('UPDATE %gis% SET `latlng`=:latlng WHERE `gis`=:gis LIMIT 1',':gis',$rs->gis,':latlng',$latlng);
						$gis=$rs->gis;
					} else {
						mydb::query('INSERT INTO %gis% SET `table`=:table,`latlng`=:latlng',':table','sgz_db_person',':latlng',$latlng);
						$gis=mydb()->insert_id;
					}
					$values['gis']=$gis;
					$stmt='UPDATE %db_person% SET `gis`=:gis WHERE `psnid`=:psnid LIMIT 1';
					$log_message='แก้ไข: psnid['.$log_name.'] GIS ['.$rs->latlng.'] เป็น ['.$value.']';
				} else {
					// Update field in db_person
					$stmt='UPDATE %db_person% SET `'.$fld.'`=:value WHERE `psnid`=:psnid LIMIT 1';
					$log_message='แก้ไข: psnid['.$log_name.'] '.$fld.' ['.$rs->{$fld}.'] เป็น ['.$value.']';
				}
				mydb::query('UPDATE %db_person% SET modify=:modify, umodify=:umodify WHERE `psnid`=:psnid LIMIT 1',':psnid',$psnid, ':modify',date('U'), ':umodify',i()->uid);
				break;

		}

		// Save value into table
		if ($stmt) {
			mydb::query($stmt,':tr',$tr,':value',$value,$values);
			if (in_array($group,array('qt','bigdata')) && empty($tr)) {
				$tr=$ret['tr']=mydb()->insert_id;
				$ret['debug'].='Return tr='.$tr;
			}
			$ret['debug'].='stmt : '.str_replace("\r", '<br />', $stmt).'<br />';
			$ret['debug'].='Query : '.str_replace("\r", '<br />', mydb()->_query).'<br />';
		}
		// Update log message
		if ($log_message) model::watch_log('org',$log_keyword,$log_message);

		// Get updated partient information
		//$rs=imed_model::get_partient($psnid);

		// Set return value
		//if ($fld=='address') $ret['value']=$rs->address;
		//if ($fld=='raddress') $ret['value']=$rs->raddress;
		if ($return=='text') {
			$ret['value']=str_replace("\n",'<br />',$value);
		} else if ($return=='html') {
			$ret['value']=sg_text2html($value);
		} else if (substr($return,0,4)=='date') {
			list($return,$format)=explode(':',$return);
			$ret['value']=sg_date($value,$format);
		} else if ($return=='money') {
			$ret['value']=number_format($value,2);
		} else if ($return=='address') {
			$ret['value']=$address;
		}
		$ret['debug'].='Return type='.$return.' Return value = '.$ret['value'].'<br />';

		unset($rs->_query,$rs->disabled->_query);
		//$ret['debug'].=print_o($rs,'$rs');
	}

	$ret['debug'].=print_o($post,'post');
	if (!_AJAX) $ret['location']=array('project/assessor/'.$psnid);
	return $ret;
}
?>