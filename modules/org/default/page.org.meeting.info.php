<?php
/**
*  Meeting information
*
* @param Integer $doid
* @return String
*/
function org_meeting_info($self, $orgId, $doid, $tabs = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId, '{initTemplate: true}');
	$orgId = $orgInfo->orgid;

	$doingInfo = R::Model('org.doing.get',$doid);

	//R::View('org.toolbar',$self, $doingInfo->doings, NULL, $doingInfo);

	$isEdit =org_model::is_edit($doingInfo->orgid,$doingInfo->uid);


	if (!$isEdit) return '<p class="notify">ขออภัย กิจกรรมนี้ไม่ใช่กิจกรรมขององกรค์ที่ท่านดูแล</p>';

	if (!_AJAX) {
		$ret.='<div class="sg-tabs'.($isEdit?'':' readonly').'"><ul class="tabs"><li class="'.(empty($tabs) || $tabs=='info'?'-active':'').'"><a href="'.url('org/'.$orgId.'/meeting.info/'.$doid.'/info').'">รายละเอียด</a></li><li class="'.($tabs=='invite'?'-active':'').'"><a href="'.url('org/'.$orgId.'/meeting.info/'.$doid.'/invite').'">เชิญเข้าร่วม</a></li><li class="'.($tabs=='join'?'-active':'').'"><a href="'.url('org/'.$orgId.'/meeting.info/'.$doid.'/join').'">รายชื่อผู้เข้าร่วม</a></li><li><a href="'.url('org/'.$orgId.'/meeting.registerform/'.$doid).'">พิมพ์ใบลงทะเบียน</a></li><li><a href="'.url('org/'.$orgId.'/meeting.registerform/'.$doid,array('o'=>'excel')).'" target="_blank">ดาวน์โหลดใบลงทะเบียน</a></li></ul>';

		$inlineAttr['class'] = 'org-meeting-info';

		if ($isEdit) {
			$inlineAttr['class'] .= ' sg-inline-edit';
			$inlineAttr['data-update-url'] = url('org/edit/info/'.$doid);
			if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
		}
		$ret.='<div id="org-meeting-info" '.sg_implode_attr($inlineAttr).'>';
	}

	switch ($tabs) {
		case 'invite' :
			$ret.='<div class="nav -page"><header class="header"><h3>รายชื่อเชิญเข้าร่วมกิจกรรม</h3></header><ul class="">';
			if ($isEdit) $ret.='<li><form class="sg-form -sg-flex" method="get" action="'.url('org/'.$orgId.'/meeting.invite/'.$doid).'" data-rel="#org-join-list" role="search"><input type="text" name="addname" class="form-text sg-autocomplete" size="20" value="" placeholder="ป้อน ชื่อ นามสกุล แล้วเลือกจากรายการที่แสดง" data-query="'.url('org/api/person').'" data-callback="orgMeetingAddMember" style="flex-grow: 1;"><span> หรือ <button class="btn -primary" type="submit"><i class="icon -addbig -white"></i><span>เพิ่มชื่อคนรายใหม่</span></button> (กรณีไม่มีรายชื่อในฐานข้อมูล)</span></form></li>';
			$ret.='</ul></div>'._NL;
			$ret.='<div id="org-join-list">'._NL;
			$ret.=R::Page('org.meeting.invite',NULL,$orgInfo,$doid,$doingInfo);
			$ret.='</div>'._NL;
			break;

		case 'join' :
			$ret.='<nav class="nav -page"><header class="header"><h3>รายชื่อผู้เข้าร่วมกิจกรรม</h3></header><ul class="">';
			if ($isEdit) $ret.='<li><form class="sg-form -sg-flex" method="get" action="'.url('org/'.$orgId.'/meeting.join/'.$doid).'" data-query="'.url('org/api/person').'" data-rel="#org-join-list" role="search"><input type="text" name="addname" class="form-text sg-autocomplete" size="20" value="" placeholder="ป้อน ชื่อ นามสกุล แล้วเลือกจากรายการที่แสดง" data-query="'.url('org/api/person').'" data-callback="orgMeetingAddMember" style="flex-grow: 1;"><span> หรือ <button class="btn -primary" type="submit"><i class="icon -addbig -white"></i><span>เพิ่มชื่อคนรายใหม่</span></button> (กรณีไม่มีรายชื่อในฐานข้อมูล)</span></form></li>';
			$ret.='</ul></nav>'._NL;
			$ret.='<div id="org-join-list">'._NL;
			$ret.=R::Page('org.meeting.join',NULL,$orgInfo,$doid,$doingInfo);
			$ret.='</div>'._NL;
			break;

		default :
			$ret.='<div class="nav -page"><header class="header"><h3>รายละเอียดกิจกรรม</h3></header><ul class="">';
			$ret.='</ul></div>'._NL;

			$tables = new Table();
			$tables->colgroup=array('width="20%"','width="80"');
			$tables->rows[]=array(
				'วันที่',
				'<span class="big">'
				.view::inlineedit(
					array('group'=>'doing','tr'=>$doingInfo->doid,'fld'=>'atdate','convert'=>'U','ret'=>'date:ว ดดด ปปปป','value'=>$doingInfo->atdate),$doingInfo->atdate?sg_date($doingInfo->atdate,'ว ดดด ปปปป'):null,$isEdit,'datepicker')
				.($doingInfo->fromtime?' เวลา '.view::inlineedit(array('group'=>'doing','fld'=>'fromtime','tr'=>$doingInfo->doid),substr($doingInfo->fromtime,0,5),$isEdit).' น.':'')
				.'</span>'
			);
			$tables->rows[]=array(
				'ชื่อกิจกรรม',
				'<span class="big">'.view::inlineedit(array('group'=>'doing','fld'=>'doings','tr'=>$doingInfo->doid,'class'=>'-fill'),$doingInfo->doings,$isEdit).'</span>'
			);
			$tables->rows[]=array(
				'สถานที่',
				view::inlineedit(array('group'=>'doing','fld'=>'place','tr'=>$doingInfo->doid,'class'=>'-fill'),$doingInfo->place,$isEdit)
			);
			$tables->rows[]=array(
				'องค์กร',
				$doingInfo->orgname
			);
			$tables->rows[]=array(
				'โครงการ/แผนงาน',
				$doingInfo->tpid?'<a href="'.url('paper/'.$doingInfo->tpid).'">'.$doingInfo->projectTitle.'</a>':''
			);
			$tables->rows[]=array(
				'ประเด็น',
				$doingInfo->issue_name
			);
			$tables->rows[]=array(
				'จำนวนผู้เข้าร่วม',
				'<strong class="big">'.$doingInfo->joins.'</strong> คน'
			);
			$ret.=$tables->build();


			$ret.='<div class="nav -page"><header class="header"><h3>รายชื่อผู้เข้าร่วมกิจกรรม</h3></header><ul class="">';
			if ($isEdit) $ret.='<li><form class="sg-form" method="get" action="'.url('org/'.$orgId.'/meeting.join/'.$doid).'" data-query="'.url('org/api/person').'" data-rel="#org-join-list" role="search"><input type="text" name="addname" class="sg-autocomplete form-text" size="40" value="" placeholder="ป้อน ชื่อ นามสกุล แล้วเลือกจากรายการที่แสดง" data-query="'.url('org/api/person').'" data-callback="orgMeetingAddMember"> หรือ <button class="btn -primary" type="submit"><i class="icon -addbig -white"></i><span>เพิ่มชื่อคนรายใหม่</span></button> (กรณีไม่มีรายชื่อในฐานข้อมูล)</form></li>';
			$ret.='</ul></div>'._NL;
			$ret.='<div id="org-join-list">'._NL;
			$ret.=R::Page('org.meeting.join',NULL,$orgInfo,$doid,$doingInfo);
			$ret.='</div>'._NL;
			break;
	}
	if (!_AJAX) {
		$ret.='</div>';
		$ret.='</div><!--sg-tabs-->';
	}
	//$ret.=print_o($doingInfo,'$doingInfo');
	//$ret .= print_o($orgInfo,'$orgInfo');
	return $ret;
}
?>