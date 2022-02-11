<?php
/**
 * Edit disabled information
 *
 * @param Integer $pid
 * @return XML and die
 */
function saveup_member_edit($self) {
	$mid=$_REQUEST['id'];
	$fld=$_REQUEST['fld'];
	$value=$_REQUEST['value'];
	if ($value=='...') return $value;

	if (empty($mid) || empty($fld)) return message('error','Invalid parameter');
	if (!user_access('administer disableds,create disabled db')) return message('error','Access denied');

	$rs=saveup_model::get_user_detail($mid);

	if ($rs->birth=='0000-00-00') $rs->birth='';

	R::View('saveup.toolbar',$self,$rs->mid.' : '.$rs->firstname.' '.$rs->lastname.' - รายละเอียดสมาชิก','member',$rs);

	if ($_POST['cancel']) {
		location('saveup/member/view/'.$mid);
	} else if (($_POST || $save) && $mid && $fld) {
		if (is_string($value)) $value=trim(strip_tags($value));
		unset($stmt);

		if ($fld=='budget') $value=preg_replace('/[^0-9\.]/i','',$value);
		else if (in_array($fld,array('birth'))) {
			list($dd,$mm,$yy)=explode('/',$value);
			$value=$yy.'-'.$mm.'-'.$dd;
//				$stmt='UPDATE %db_person% SET `birth`=:value WHERE `aid`=:pid LIMIT 1';
		} else if ($fld=='name') {
			// Update name and lname
			list($name,$lname)=sg::explode_name(' ',$value);
//				$stmt='UPDATE %db_person% SET `name`=:name, `lname`=:lname WHERE `aid`=:pid LIMIT 1';
			$values->name=$name;
			$values->lname=$lname;
		} else if (in_array($fld,array('cid', 'sex', 'phone', 'email', 'educate', 'occupa', 'aptitude', 'interest'))) {
			// Update field in db_person
//				$stmt='UPDATE %db_person% SET `'.$fld.'`=:value WHERE `aid`=:pid LIMIT 1';
		} else if (in_array($fld,array('birth'))) {
			// Update birth field in db_person
			if (in_array(substr($value,0,2),array(24,25))) $value=(substr($value,0,4)-543).substr($value,4);
//				$stmt='UPDATE %db_person% SET `'.$fld.'`=:value WHERE `aid`=:pid LIMIT 1';
		} else if (in_array($fld,array('remark', 'healthright', 'bodyequip', 'comunicate', 'helper', 'problem', 'discharge', 'relateorg'))) {
			// Update field in db_disabled
//				$stmt='UPDATE %imed_disabled% SET `'.$fld.'`=:value WHERE `pid`=:pid LIMIT 1';
		} else if ($fld=='address') {
			// Update address
			if (preg_match('/(.*)(ตำบล|ต\.)(.*)/',$value['address'],$out)) {
//					$stmt='UPDATE %db_person% SET `house`=:house, `tambon`=:tambon, `ampur`=:ampur, `changwat`=:changwat WHERE `aid`=:pid LIMIT 1';
				$tambon=$value['tambon'];
				$values->house=trim($out[1]);
				$values->tambon=substr($tambon,4,2);
				$values->ampur=substr($tambon,2,2);
				$values->changwat=substr($tambon,0,2);
				$value='';
			}
		} else if ($fld=='latlng') {
			$latlng='func.PointFromText("POINT('.preg_replace('/,/',' ',$value).')")';
			if ($rs->gis) {
				mydb::query('UPDATE %gis% SET `latlng`=:latlng WHERE `gis`=:gis LIMIT 1',':gis',$rs->gis,':latlng',$latlng);
			} else {
				mydb::query('INSERT INTO %gis% SET `table`=:table,`latlng`=:latlng',':table','sgz_saveup_member',':latlng',$latlng);
				$gis=mydb()->insert_id;
				$stmt='UPDATE %saveup_member% SET `gis`='.$gis.' WHERE `mid`=:mid LIMIT 1';
			}
		} else if ($fld=='defect') {
//				$stmt='INSERT INTO %db_disabled_defect% (`pid`, `defect`) VALUES (:pid, :value)';
		} else if (preg_match('/([0-9])\-(.*)/',$fld,$out)) {
			$values->defect=intval($out[1]);
			$fld=$out[2];
			if (is_numeric($value)) $value=intval($value);
//				$stmt='UPDATE %db_disabled_defect% SET `'.$fld.'`=:value WHERE `pid`=:pid AND `defect`=:defect LIMIT 1';
		}
		// Save value into table
		if ($stmt) {
			mydb::query($stmt,':mid',$mid,':value',$value,$values);
//				$ret.=mydb()->_query;
		}

		$rs=saveup_model::get_user_detail($mid);
		if ($fld=='name') $ret.=$rs->name.' '.$rs->lname;
		else if ($fld=='occupa') $ret.=$rs->occu_desc;
		else if ($fld=='educate') $ret.=$rs->edu_desc;
		else if ($fld=='birth') $ret.=sg_date($value,'ว ดดด ปปปป');
		else if (in_array($fld,array('remark'))) $ret.=trim(sg_text2html($value));
		else if ($values->defect) {
			$ret.=$rs->defect[$values->defect]->{$fld};
		} else $ret.=$rs->{$fld};

		return location('saveup/member/view/'.$mid);
	} else {
		// Generate form
		$form = new Form('disabled', url(q(),'id='.$pid.'&fld='.$fld), 'edit-disabled');

		if ($fld=='address') {
			$form->fld->type='text';
			$form->fld->size=30;
			$form->tambon->type='hidden';
			$form->tambon->name='tambon';
			$form->tambon->value=$rs->changwat.$rs->ampur.$rs->tambon;
			// $rs->address=$rs->house.($rs->soi?' ซอย'.$rs->soi:'').($rs->road?' ถนน'.$rs->road:'').($rs->village?' หมู่ที่ '.$rs->village:'').($rs->villname?' บ้าน'.$rs->villname:'').' ตำบล'.$rs->subdistname.' อำเภอ'.$rs->distname.' จังหวัด'.$rs->provname.($rs->zip?' รหัสไปรษณีย์ '.$rs->zip:'');
		}
		$form->fld->label=$fld_title[$fld];
		$form->fld->name=$fld;
		$form->fld->value=htmlspecialchars($defect?$rs->defect[$defect]->{$fld_defect}:$rs->{$fld});

		$form->submit->type='submit';
		$form->submit->items->save=tr('Save');

		$ret .= $form->build();
		return $ret;

//			return json_encode($ret);
	}
}
?>