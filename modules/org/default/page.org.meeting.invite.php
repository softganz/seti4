<?php
/**
*  Invite meeting
*
* @param $_POST
* @return String
*/
function org_meeting_invite($self,$orgId, $doid , $rs = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	if (post('id')) {
		$post->psnid=post('id');
		$post=R::Model('org.meeting.member.add',$doid,'Invite',$post);
		//$ret.=print_o($post,'$post');
	} else if (post('addname')) {
		$ret.=R::View('org.meeting.member.new', post('addname'));
	} else if (post('person')) {
		//	$ret.=$this->__new_member_form();
		$post=R::Model('org.meeting.member.add', $doid,'Invite',(object)post('person'));
		//$ret.=print_o($post,'$post');
	} else if (post('remove')) {
		$psnid=post('remove');
		$isJoin=mydb::select('SELECT `isjoin` FROM %org_dos% WHERE `doid`=:doid AND `psnid`=:psnid LIMIT 1',':doid',$doid, ':psnid',$psnid)->isjoin;
		if ($isJoin) {
			// Remove regtype only
			mydb::query('UPDATE %org_dos% SET `regtype`="Walk In" WHERE `doid`=:doid AND `psnid`=:psnid LIMIT 1',':doid',$doid, ':psnid',$psnid);
		} else {
			// Remove record
			mydb::query('DELETE FROM %org_dos% WHERE `doid`=:doid AND `psnid`=:psnid LIMIT 1',':doid',$doid, ':psnid',$psnid);
			$orgid=mydb::select('SELECT `orgid` FROM %org_doings% WHERE `doid`=:doid LIMIT 1',':doid',$doid)->orgid;
			$isHaveMetting=mydb::select('SELECT `psnid` FROM %org_dos% do LEFT JOIN %org_doings% d USING(`doid`) WHERE `psnid`=:psnid AND `orgid`=:orgid LIMIT 1',':psnid',$psnid, ':orgid',$orgid)->psnid;
			//$ret.='Remain metting ='.$isHaveMetting.' : '.mydb()->_query;
			if (!$isHaveMetting) {
				mydb::query('DELETE FROM %org_mjoin% WHERE `orgid`=:orgid AND `psnid`=:psnid LIMIT 1',':psnid',$psnid, ':orgid',$orgid);
			}
		}
	}

	if (empty($rs)) $rs=R::Model('org.doing.get',$doid);;
	$isEdit = org_model::is_edit($rs->orgid,$rs->uid);



	$tables = new Table();
	$tables->caption = 'รายชื่อเชิญเข้าร่วมกิจกรรม';
	$tables->thead = array(
										'no' => '',
										'name -nowrap' => 'ชื่อ - นามสกุล',
										'joins -amt -nowrap' => '<span title="จำนวนครั้งที่เคยเข้าร่วมกิจกรรม">เข้าร่วม</span>',
										'ที่อยู่',
										'โทรศัพท์',
										'regtype -center -nowrap' => '',
										'jointype -center -nowrap -hover-parent' => '',
									);

	foreach ($rs->members as $item) {
		if ($item->regtype == "Walk In") continue;
		if ($item->uid != i()->uid) unset($item->house,$item->phone);

		$ui = new Ui();
		if ($isEdit) $ui->add('<a class="sg-action" href="'.url('org/'.$orgId.'/meeting.invite/'.$doid,array('remove'=>$item->psnid)).'" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="#org-join-list" title="ลบรายการ"><i class="icon -material">cancel</i></a>');
		$tables->rows[] = array(
				++$no,
				'<a class="sg-action" href="'.url('org/member/'.$item->psnid).'" data-rel="box" data-width="640">'.trim($item->prename.' '.$item->name.' '.$item->lname).'</a>',
				$item->joins,
				SG\implode_address($item,'short'),
				$item->phone,
				$item->regtype,
				$item->jointype
				. ($ui->count() ? '<nav class="nav -icons -hover">'.$ui->build().'</nav>' : ''),
			);
	}
	$ret .= $tables->build();

	$ret.='หมายเหตุ : รายชื่อผู้ที่ถูกบันทึกให้เข้าร่วมกิจกรรมนี้แล้ว จะไม่สามารถเชิญเข้าร่วมกิจกรรมได้อีก';

	//$ret.=print_o(post(),'post');
	//$ret.=print_o($rs,'$rs');
	//$ret .= print_o($orgInfo,'orgInfo');
	return $ret;
}
?>