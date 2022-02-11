<?php
/**
* Poor System
*
* @param Object $self
* @return String
*/

function imed_poorhome_edit($self,$poorId) {
	$self->theme->title='แก้ไขแบบสำรวจ';



	$post=post();
	list($group,$part)=explode(':',$post['group']);
	$fld=trim($post['fld']);
	$period=$post['period'];
	$tr=trim($post['tr']);
	$value=$post['value'];
	$return=$post['ret'];
	$action=$post['action'];
	$dataType=$post['type'];
	//		if ($value=='...' || trim($value)=='') return array('value'=>'...');

	$ret['tr']=$tr;
	$ret['value']=$retvalue=$value;
	$ret['error']='';
	$ret['debug'].='action='.$action.', group='.$group.', part='.$part.', pid='.$poorId.', fld='.$fld.', tr='.$tr.'<br />';
	$ret['debug'].='Value='.$value.'<br />';

	if (!$action) return $ret;

	$ret['msg']='บันทึกเรียบร้อย';
	$save=$post['action']=='save' && $poorId && $group && $fld;
	$values=NULL;

	if ($action!='save') return $ret;

	$rs=__imed_poor_get($poorId);

	if ($post['cancel']) $ret['error']='ยกเลิกการแก้ไข';
	if ($rs->uid==i()->uid) {
	} else  if ($zones=imed_model::get_user_zone(i()->uid,'imed')) {
		$right=imed_model::in_my_zone($zones,$rs->changwat,$rs->ampur,$rs->tambon);
		if (!$right) $ret['error']='ข้อมูลชุดนี้อยู่นอกพื้นที่การดูแลของท่าน หากข้อมูลนี้ไม่ถูกต้อง กรุณาแจ้งผู้ดูแลระบบ';
		else if ($right->right!='edit') $ret['error']='ขออภัย ท่านไม่ได้รับสิทธิ์ในการแก้ไขข้อมูลนี้';
	} else if (!(user_access('create imed at home'))) $ret['error']='Access denied';

	if ($rs->birth=='0000-00-00') $rs->birth='';
	$log_name=$poorId.':'.$rs->name.' '.$rs->lname;

	if (empty($poorId) || empty($group) || empty($fld)) $ret['error']='Invalid parameter';
	if (!$save) $ret['error']='Invalid parameter';
	if ($ret['error']) {
		$ret['msg']=$ret['error'];
		return $ret;
	}

	$values=array();
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
	} else if ($dataType=='money') {
		$value=preg_replace('/[^0-9\.\-]/','',$value);
	}

	// Update imed poor transaction
	$log_keyword='modify';
	switch ($group) {

		case 'poor' : // Update field in poor
			if ($fld=='address') {
				// Update address
				$address = SG\explode_address($value, post('areacode'));
				$values['house'] = $address['house'];
				$values['village'] = $address['village'];
				$values['tambon'] = $address['tambonCode'];
				$values['ampur'] = $address['ampurCode'];
				$values['changwat'] = $address['changwatCode'];

				$fieldUpdate = 'house,village,tambon,ampur,changwat';

				$ret['debug'].=print_o($address,'$address');
				$stmt = 'UPDATE %poor% SET '.mydb::create_fieldupdate($fieldUpdate).' WHERE `poorid` = :pid LIMIT 1';

				/*
				list($address,$tambon)=explode('|', $value);
				if (preg_match('/(.*)(หมู่|หมู่ที่|ม\.)([0-9\s]+)\s+(.*)/',$address,$out) || preg_match('/(.*)(ตำบล|ต\.)(.*)/',$address,$out)) {
					$stmt='UPDATE %db_person% SET `house`=:house, `village`=:village WHERE `psnid`=:pid LIMIT 1';
					//$tambon=$value;//['tambon'];
					$out[3]=trim($out[3]);
					$values['house']=trim($out[1]);
					$values['village']=(in_array($out[2],array('หมู่','หมู่ที่','ม.')) && is_numeric($out[3]))?$out[3]:'';
					if ($tambon) {
						$values['tambon']=substr($tambon,4,2);
						$values['ampur']=substr($tambon,2,2);
						$values['changwat']=substr($tambon,0,2);
						$stmt='UPDATE %poor% SET `house`=:house, `village`=:village, `tambon`=:tambon, `ampur`=:ampur, `changwat`=:changwat WHERE `poorid`=:pid LIMIT 1';
					}
					$log_message='แก้ไข: pid['.$log_name.'] ที่อยู่ปัจจุบัน ['.$rs->address.'] เป็น ['.print_r($values,1).']';
				}
				*/
			} else {
				$stmt='UPDATE %poor% SET `'.$fld.'`=:value WHERE `poorid`=:pid LIMIT 1';
				$log_message='แก้ไข: poor['.$log_name.'] field '.$fld.' ['.$rs->{$fld}.'] เป็น ['.$value.']';
			}
			break;

		case 'person' :
			if (in_array($fld,array('birth'))) {
				$stmt='UPDATE %db_person% SET `birth`=:value WHERE `psnid`=:trid LIMIT 1';
				$log_message='แก้ไข: pid['.$log_name.'] วันเกิด ['.$rs->birth.'] เป็น ['.$value.']';
			} else if ($fld=='name') {
				// Update name and lname
				list($name,$lname)=sg::explode_name(' ',$value);
				$stmt='UPDATE %db_person% SET `name`=:name, `lname`=:lname WHERE `psnid`=:trid LIMIT 1';
				$values['name']=$name;
				$values['lname']=$lname;
				$log_message='แก้ไข: pid['.$log_name.'] ชื่อ ['.$rs->name.' '.$rs->lname.'] เป็น ['.$value.']';
			} else {
				// Update field in db_person
				$stmt='UPDATE %db_person% SET `'.$fld.'`=:value WHERE `psnid`=:trid LIMIT 1';
				$log_message='แก้ไข: pid['.$log_name.'] '.$fld.' ['.$rs->{$fld}.'] เป็น ['.$value.']';
			}
			mydb::query('UPDATE %db_person% SET modify=:modify, umodify=:umodify WHERE psnid=:pid LIMIT 1',':pid',$tr, ':modify',date('U'), ':umodify',i()->uid);
			break;

		case 'poormember' :
			$stmt='UPDATE %poormember% SET `'.$fld.'`=:value WHERE `poorid`=:pid AND `psnid`=:trid LIMIT 1';
			break;

		case 'photo' :
			$stmt='UPDATE %topic_files% SET `'.$fld.'`=:value WHERE `fid`=:trid LIMIT 1';
			break;

		case 'qt' :
			if ($tr) {
				$stmt='UPDATE %imed_qt% SET `value`=:value, `umodify`=:umodify, `dmodify`=:dmodify WHERE `qid`=:trid LIMIT 1';
				$values['umodify']=i()->ok?i()->uid:'func.NULL';
				$values['dmodify']=date('U');
			} else {
				$stmt='INSERT INTO %imed_qt% SET `pid`=:pid, `part`=:part, `value`=:value, `ucreated`=:ucreated, `dcreated`=:dcreated';
				$values['part']=$fld;
				$values['ucreated']=i()->ok?i()->uid:'func.NULL';
				$values['dcreated']=date('U');
			}
			break;

		case 'tr' :
			if ($tr) {
				if ($fld=='phototitle') {
					$stmt='UPDATE %topic_files% SET `title`=:value WHERE `fid`=:trid LIMIT 1';
				} else {
					$values['modified']=date('U');
					$values['modifyby']=i()->ok?i()->uid:'func.NULL';
					$stmt='UPDATE %project_tr% SET `'.$fld.'`=:value, `modified`=:modified, `modifyby`=:modifyby WHERE `trid`=:trid LIMIT 1';
				}
			} else {
				$values['tpid']=$tpid;
				$values['formid']=$group;
				$values['period']=SG\getFirst($period,'func.NULL');
				$values['part']=SG\getFirst($part,'func.NULL');
				$values['calid']=SG\getFirst($calid,'func.NULL');
				$values['uid']=i()->ok?i()->uid:'func.NULL';
				$values['created']=date('U');
				$stmt='INSERT INTO %project_tr% SET `tpid`=:tpid, `formid`=:formid, `period`=:period, `part`=:part,`calid`=:calid, `uid`=:uid, `created`=:created, `'.$fld.'`=:value';
			}
			break;
	}

	// Save value into table
	if ($stmt) {
		mydb::query($stmt,':pid',$poorId,':trid',$tr,':value',$value,$values);
		if (in_array($group,array('qt')) && empty($tr)) {
			$tr=$ret['tr']=mydb()->insert_id;
			$ret['debug'].='tr='.$tr;
		}
		$ret['debug'].='stmt : '.str_replace("\r", '<br />', $stmt).'<br />';
		$ret['debug'].='Query : '.str_replace("\r", '<br />', mydb()->_query).'<br />';
	}
	// Update log message
	if ($log_message) model::watch_log('imed',$log_keyword,$log_message);

	// Get updated patient information
	$rs=__imed_poor_get($poorId);

	// Set return value
	if ($fld=='address') $ret['value']=$rs->address;
	else if ($return=='text') {
		$ret['value']=str_replace("\n",'<br />',$value);
	} else if ($return=='html') {
		$ret['value']=sg_text2html($value);
	} else if (substr($return,0,4)=='date') {
		list($return,$format)=explode(':',$return);
		$ret['value']=sg_date($value,$format);
	} else if ($return=='money') {
		$ret['value']=number_format($value,2);
	}
	$ret['debug'].='Return type='.$return.' Return value = '.$ret['value'].'<br />';

	unset($rs->_query,$rs->disabled->_query);

	$ret['debug'].=print_o($post,'post');
	if (!_AJAX) $ret['location']=array('imed?pid='.$poorId);
	return $ret;
}

function __imed_poor_get($poorId) {
	$stmt='SELECT
				p.*
				, COUNT(m.`poorid`) `members`
				, GROUP_CONCAT(IF(m.`reltohouseholder`="เจ้าบ้าน",CONCAT(psn.`prename`," ",psn.`name`," ",psn.`lname`),"") SEPARATOR "") householderName
				, cosub.`subdistname` subdistname
				, codist.`distname` distname
				, copv.`provname` provname
				FROM %poor% p
					LEFT JOIN %poormember% m USING(`poorid`)
					LEFT JOIN %db_person% psn USING(`psnid`)
					LEFT JOIN %co_province% copv ON p.`changwat`=copv.`provid`
					LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
					LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
					LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
				WHERE `poorid`=:poorid
				GROUP BY `poorid`
				ORDER BY `poorid` ASC
				LIMIT 1';
	$rs=mydb::select($stmt,':poorid',$poorId);
	if (!$rs->_empty) $rs->address=SG\implode_address($rs);
	return $rs;
}

?>