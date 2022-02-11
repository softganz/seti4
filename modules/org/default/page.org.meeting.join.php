<?php
/**
*  Add new meeting
*
* @param $_POST
* @return String
*/
function org_meeting_join($self, $orgId, $doid, $rs = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	if (post('id')) {
		$post->psnid=post('id');
		$post=R::Model('org.meeting.member.add', $doid,'Attendee',$post);
	} else if (post('addname')) {
		$ret.=R::View('org.meeting.member.new', post('addname'));
	} else if (post('person')) {
		//$ret.=$this->__new_member_form();
		$post=R::Model('org.meeting.member.add', $doid,'Attendee',(object)post('person'));
		//if (i()->username == 'softganz') $ret.=print_o($post,'$post');
	} else if (post('remove')) {
		$psnid=post('remove');
		// Remove jointype only
		mydb::query('UPDATE %org_dos% SET `isjoin`=0 WHERE `doid`=:doid AND `psnid`=:psnid LIMIT 1',':doid',$doid, ':psnid',$psnid);
		$isWalkIn=mydb::select('SELECT `regtype` FROM %org_dos% WHERE `doid`=:doid AND `psnid`=:psnid AND `regtype`="Walk In" LIMIT 1',':doid',$doid, ':psnid',$psnid)->regtype;
		if ($isWalkIn) {
			// Remove record
			mydb::query('DELETE FROM %org_dos% WHERE `doid`=:doid AND `psnid`=:psnid LIMIT 1',':doid',$doid, ':psnid',$psnid);
			$orgid=mydb::select('SELECT `orgid` FROM %org_doings% WHERE `doid`=:doid LIMIT 1',':doid',$doid)->orgid;
			$isHaveMetting=mydb::select('SELECT `psnid` FROM %org_dos% do LEFT JOIN %org_doings% d USING(`doid`) WHERE `psnid`=:psnid AND `orgid`=:orgid LIMIT 1',':psnid',$psnid, ':orgid',$orgid)->psnid;
			//$ret.='Remain metting ='.$isHaveMetting.' : '.mydb()->_query;
			if (!$isHaveMetting) {
				mydb::query('DELETE FROM %org_mjoin% WHERE `orgid`=:orgid AND `psnid`=:psnid LIMIT 1',':psnid',$psnid, ':orgid',$orgid);
			}
		}
	} else if (post('action')=='addinvite' && $doid) {
		mydb::query('UPDATE %org_dos% SET `isJoin`=1 WHERE `doid`=:doid',':doid',$doid);
	}

	if (empty($rs)) $rs=R::Model('org.doing.get',$doid,'{debug:false}');

	$isEdit=org_model::is_edit($rs->orgid,$rs->uid);

	if ($isEdit && !$rs->joins) {
		$ret.='<p><a class="sg-action btn" href="'.url('org/'.$orgId.'/meeting.join/'.$doid,array('action'=>'addinvite')).'" data-rel="#org-join-list" data-confirm="ต้องการเพิ่มรายชื่อผู้เข้าร่วมจากรายชื่อเชิญเข้าร่วมทั้งหมด กรุณายืนยัน?">เพิ่มรายชื่อผู้เข้าร่วมจากรายชื่อเชิญเข้าร่วมทั้งหมด</a></p>';
	}
	$tables = new Table();
	$tables->caption = 'รายชื่อผู้เข้าร่วมกิจกรรม';
	$tables->thead['no']='';
	$tables->thead['name -nowrap']='ชื่อ - นามสกุล';
	$tables->thead['joins -amt -nowrap']='<span title="จำนวนครั้งที่เคยเข้าร่วมกิจกรรม">เข้าร่วม</span>';
	$tables->thead[]='ที่อยู่';
	$tables->thead[]='โทรศัพท์';
	$tables->thead['center -regtype -nowrap']='';
	$tables->thead['center -jointype -nowrap']='';
	$tables->thead['icons']='';

	foreach ($rs->members as $item) {
		if ($item->isjoin <= 0) continue;
		unset($row);
		if ($item->uid!=i()->uid) unset($item->house,$item->phone);
		$row[]=++$no;
		$row[]='<a class="sg-action" href="'.url('org/member/'.$item->psnid).'" data-rel="box" data-width="640">'.trim($item->prename.' '.$item->name.' '.$item->lname).'</a>';
		$row[]=$item->joins;
		$row[]=SG\implode_address($item,'short');
		$row[]=$item->phone;
		$row[]=$item->regtype;
		$row[]=$item->jointype;
		if ($isEdit) $row[]='<a href="'.url('org/'.$orgId.'/meeting.join/'.$doid,array('remove'=>$item->psnid)).'" class="sg-action hover--menu" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="none" title="ลบรายการ" data-removeparent="tr"><i class="icon -cancel"></i></a>';
		$tables->rows[]=$row;
	}
	$ret .= $tables->build();

	//$ret.=print_o(post(),'post');
	//$ret.=print_o($rs,'$rs');
	return $ret;
}
?>