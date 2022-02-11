<?php
/**
* Project owner
*
* @param Object $self
* @param Int $tpid
* @return String
*/
function project_info_join($self, $tpid, $calId = NULL, $action = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid, '{initTemplate: true}');
	$tpid = $projectInfo->tpid;

	$calId = SG\getFirst($calId,post('calid'));

	$projectInfo->calid = $calId;

	R::View('project.toolbar', $self, $projectInfo->title, 'people', $projectInfo);

	$isEdit = $projectInfo->info->isEdit;

	$calRs = mydb::select('SELECT * FROM %calendar% WHERE `id` = :calid LIMIT 1',':calid', $calId);

	if (empty($projectInfo->orgid)) {
		return message('error', 'ไม่สามารถสร้างบันทึกผู้เข้าร่วมกิจกรรมได้ เนื่องจากโครงการนี้ไม่ได้สังกัดภายใต้องค์กรใด ๆ');
	}

	switch ($action) {
		case 'create':
			if ($isEdit && $calId) {
				$doing->orgid=$projectInfo->orgid;
				$doing->tpid=$calRs->tpid;
				$doing->calid=$calId;
				$doing->uid=i()->uid;
				$doing->doings=$calRs->title;
				$doing->place=$calRs->location;
				$doing->atdate=sg_date($calRs->from_date,'U');
				$doing->fromtime=$calRs->from_time;
				$stmt='INSERT INTO %org_doings% (`orgid`, `tpid`, `calid`, `uid`, `doings`, `place`, `atdate`, `fromtime`) VALUES (:orgid, :tpid, :calid, :uid, :doings, :place, :atdate, :fromtime)';
				mydb::query($stmt,$doing);
				//$ret.=mydb()->_query;
				//$ret.=print_o($doing,'$doing');
				location('project/'.$tpid.'/info.join/'.$calId);
			}
			break;
		
		default:
			# code...
			break;
	}


	$doRs=mydb::select('SELECT * FROM %org_doings% WHERE `calid`=:calid LIMIT 1',':calid',$calId);
	if ($doRs->doid) {
		$doid=$doRs->doid;

		//$ret.='<div class="sg-tabs'.($isEdit?'':' readonly-x').'"><ul class="tabs -no-print"><li class="'.(empty($tabs) || $tabs=='info'?'active':'').'"><a href="'.url('org/'.$doRs->orgid.'/meeting.info/'.$doid.'/info').'">รายละเอียด</a></li><li class="'.($tabs=='invite'?'active':'').'"><a href="'.url('org/'.$doRs->orgid.'/meeting.info/'.$doid.'/invite').'">เชิญเข้าร่วม</a></li><li class="'.($tabs=='join'?'active':'').'"><a href="'.url('org/'.$doRs->orgid.'/meeting.info/'.$doid.'/join').'">รายชื่อผู้เข้าร่วม</a></li><li><a href="'.url('org/'.$doRs->orgid.'/meeting.registerform/'.$doid).'">พิมพ์ใบลงทะเบียน</a></li><li><a href="'.url('org/'.$doRs->orgid.'/meeting.registerform/'.$doid,array('o'=>'excel')).'" target="_blank">ดาวน์โหลดใบลงทะเบียน</a></li></ul>';


		$inlineAttr['class'] = 'sg-load org-meeting-info';

		if ($isEdit) {
			$inlineAttr['class'] .= ' sg-inline-edit';
			$inlineAttr['data-update-url'] = url('org/edit/info/'.$doid);
			if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
		}

		$ret.='<div id="org-meeting-info" '.sg_implode_attr($inlineAttr).' data-url='.url('org/'.$doRs->orgid.'/meeting.info/'.$doid).'"></div>';
		//$ret.='</div>';

		head('js.org.js','<script type="text/javascript" src="org/js.org.js"></script>');
	} else if ($calId && $isEdit) {
		$ret.='<h3>'.$projectInfo->title.'</h3><h4>กิจกรรม : '.$calRs->title.'</h3>';
		$ret.='<p>ยังไม่ได้เริ่มสร้างบันทึกผู้เข้าร่วมกิจกรรม ต้องการสร้างบันทึกผู้เข้าร่วมหรือไม่?</p>';
		$ret.='<a class="btn -primary" href="'.url('project/'.$tpid.'/info.join/'.$calId.'/create').'">สร้างบันทึกผู้ลงทะเบียน/เข้าร่วมกิจกรรม</a> หรือ <a href="'.url('project/'.$tpid.'/info.action',array('calid'=>$calId)).'">ไม่สร้าง</a></p>';
	} else {
		$ret .= message('error', 'Invalid info');
	}
	//$ret.=print_o($doRs,'$doRs').print_o($calRs,'$calRs');
	//$ret .= print_o($projectInfo,'$projectInfo');

	$ret .= '<style>
	#org-join-list>p {display: none;}
	.nav.-page {display: none;}
	</style>';
	return $ret;
}
?>