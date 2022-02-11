<?php
/**
* Project calendar information
*
* @param Object $self
* @param Integer $tpid
* @param String $action
* @param Integer $actid
* @param Object $topic
* @param Object $info
* @param JSONstring $options
* @return String
*/
function project_objective($self,$tpid,$action,$actid,$topic,$info,$options) {
	if (!is_object($topic)) {
		$topic=project_model::get_topic($tpid);
		$info=project_model::get_info($tpid);
		$options=NULL;
	} else if (is_object($topic)) {
		$options=sg_json_decode($options);
	}

	if ($topic->type!='project') return message('error','This is not a project');

	$action=SG\getFirst($action,post('act'));
	$isEdit=$topic->project->isEdit;
	$isEditDetail=$info->project->isEditDetail;

	switch ($action) {
		case 'add' :
			$stmt='INSERT INTO %project_tr% (`tpid`, `parent`, `uid`, `formid`, `part`, `created`) VALUES (:tpid, 1, :uid, :formid, :part, :created)';
			mydb::query($stmt,':tpid',$tpid, ':uid', i()->uid, ':formid', 'info', ':part', 'objective', ':created',date('U'));
			location('paper/'.$tpid);
			break;

		case 'remove' :
			if (SG\confirm()) {
				mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `trid`=:trid AND `formid`="info" AND `part`="objective" LIMIT 1',':tpid',$tpid, ':trid',$actid);
			}
			break;

		case 'info' :
			$ret.='<h4>วัตถุประสงค์</h4>';
			$ret.='<p>'.$info->objective[$actid]->title.'</p>';
			$ret.='<h4>ตัวชี้วัดความสำเร็จ</h4>';
			$ret.='<p>'.nl2br($info->objective[$actid]->indicator).'</p>';
			//$ret.=print_o($info,'$info');
			return $ret;
			break;

		case 'addgenobj' :
			$stmt='INSERT INTO %project_tr% (`tpid`, `parent`, `uid`, `formid`, `part`, `text1`, `text2`, `created`) VALUES (:tpid, 2, :uid, "info", "genobj", "เพื่อพัฒนาโรงเรียน... ให้เป็นแหล่งเรียนรู้ของชุมชน ผู้ปกครองและโรงเรียนในพื้นที่อำเภอ... จำนวน... โรงเรียน คือ ... ในการเจริญรอยตามพระยุคลบาทสมเด็จพระเทพรัตนราชสุดาฯ สยามบรมราชกุมารี ด้านเกษตร-อาหาร-โภชนาการ-สุขภาพ แบบบูรณาการภายในปี ๒๕๖๐", "", :created)';
			mydb::query($stmt, ':tpid',$tpid, ':uid',i()->uid, ':created',date('U'));
			$ret.=mydb()->_query;
			break;
	}


	// วัตถุประสงค์ทั่วไป และ วัตถุประสงค์เฉพาะ
	foreach(mydb::select('SELECT `catid`,`name` FROM %tag% WHERE `taggroup`="project:objtype" ORDER BY `catid` ASC')->items as $item) $objTypeList[$item->catid]=$item->name;

	$ret.='<div class="project -objective">'._NL;
	$objectiveNo=0;

	$tables = new Table();
	$tables->addClass='project -objective';
	$tables->colgroup=array('objective'=>'width="50%"','indicator'=>'width="50%"');
	$tables->thead=array(
									'วัตถุประสงค์ / เป้าหมาย',
									'ตัวชี้วัดความสำเร็จ',
									''
									);

	$genObjRs=mydb::select('SELECT `trid`, `text1` `title` FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="genobj"',':tpid',$tpid);
	$tables->rows[]=array('<td colspan="3"><strong>วัตถุประสงค์ทั่วไป</strong>');
	foreach ($genObjRs->items as $objective) {
		$tables->rows[]=array(
												'<td colspan="3">'.view::inlineedit(array('group'=>'tr:info:genobj', 'fld'=>'text1', 'tr'=>$objective->trid), $objective->title, $isEdit, 'textarea').'</td>');
	}

	foreach ($objTypeList as $objTypeId => $objTypeName) {
		if ($objTypeId!=1) continue;

		$tables->rows[]=array(
											'<td colspan="2"><strong>'.$objTypeName.'</strong></td>',
											$isEditDetail && empty($info->project->proposalId) ?'<a class="sg-action" data-rel="#main" href="'.url('project/objective/'.$tpid.'/add').'" data-confirm="ต้องการเพิ่มวัตถุประสงค์ ใช่หรือไม่?"><i class="icon -add -hidetext"></i><span class="-hidden">เพิ่มวัตถุประสงค์</span></a>':'');
		foreach ($info->objective as $objective) {
			if ($objective->objectiveType!=$objTypeId) continue;

			$objectiveIsUse=false;
			foreach ($info->mainact as $mainActItem) {
				if (empty($mainActItem->parentObjectiveId)) continue;
				if (in_array($objective->trid, explode(',', $mainActItem->parentObjectiveId))) {
					$objectiveIsUse=true;
					break;
				}
			}

			// Create submenu
			$ui=new ui();
			$ui->add('<a href="'.url('project/objective/'.$tpid.'/info/'.$objective->trid).'" class="sg-action" data-rel="box">รายละเอียด</a>');
			if ($isEdit) {
				$ui->add('<sep>');
				if ($objectiveIsUse) {
					$ui->add('<a href="javascript:void(0)" title="วัตถุประสงค์ข้อนี้มีการใช้งานในกิจกรรมหลักแล้ว">ลบวัตถุประสงค์ไม่ได้</a>');
				} else {
					$ui->add('<a class="sg-action" href="'.url('project/objective/'.$tpid.'/remove/'.$objective->trid).'" data-confirm="คุณต้องการลบวัตถุประสงค์นี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="this" data-removeparent="tr">ลบวัตถุประสงค์</a>');
				}
			}
			$submenu=sg_dropbox($ui->build('ul'));

			$tables->rows[]=array(
												'<label><b>วัตถุประสงค์ข้อที่ '.(++$objectiveNo).' :</b> </label>'.view::inlineedit(array('group'=>'tr:info:objective', 'fld'=>'text1', 'tr'=>$objective->trid), $objective->title, $isEdit, 'textarea'),
												'<label><b>ตัวชี้วัดความสำเร็จ :</b></label>'.view::inlineedit(array('group'=>'tr:info:objective','fld'=>'text2','tr'=>$objective->trid, 'ret'=>'html'),$objective->indicator,$isEdit,'textarea'),
												$submenu,
											);
		}
	}


	$ret .= $tables->build();

	if ($isEdit && empty($info->objective)) $ret.='<p class="notify">ยังไม่มีการกำหนดวัตถุประสงค์ของโครงการ</p>';

	if ($isEdit && empty($info->project->proposalId)) {
		$ret.='<p><a class="sg-action button floating" data-rel="#main" href="'.url('project/objective/'.$tpid.'/add').'" data-confirm="ต้องการเพิ่มวัตถุประสงค์ ข้อที่ '.($objectiveNo+1).' ใช่หรือไม่?">+เพิ่มวัตถุประสงค์เฉพาะ ข้อที่ '.($objectiveNo+1).'</a></p>'._NL;
	}

	//$ret.=print_o($topic,'$topic');
	//if (i()->uid=='momo') $ret.=print_o($info,'$info');
	//$ret.=print_o($options,'$options');

	$ret.='</div><!-- project -objective -->'._NL;
	return $ret;
}
?>