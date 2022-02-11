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
function project_objective($self,$tpid,$action,$actid,$topic,$info) {
	if (!is_object($topic)) {
		$topic=project_model::get_topic($tpid);
		$info=project_model::get_info($tpid);
		$options=NULL;
	}

	$options=options('project');

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

	}


	// วัตถุประสงค์ทั่วไป และ วัตถุประสงค์เฉพาะ
	foreach(mydb::select('SELECT `catid`,`name` FROM %tag% WHERE `taggroup`="project:objtype" ORDER BY `catid` ASC')->items as $item) $objTypeList[$item->catid]=$item->name;

	$ret.='<div class="--objective">'._NL;
	$objectiveNo=0;

	$tables = new Table();
	$tables->addClass='--objectivelist';
	$tables->colgroup=array('objective'=>'width="50%"','indicator'=>'width="50%"');
	$tables->thead=array(
		'วัตถุประสงค์ / เป้าหมาย',
		'ตัวชี้วัดความสำเร็จ',
		''
	);

	foreach ($objTypeList as $objTypeId => $objTypeName) {
		//if ($objTypeId!=1) continue;
		if ($objTypeId==1) $tables->rows[]='<tr><th colspan="4"><h4>วัตถุประสงค์โดยตรง</h4></th></tr>';
		else if ($objTypeId==2) $tables->rows[]='<tr><th colspan="4"><h4>วัตถุประสงค์โดยอ้อม</h4></th></tr>';

		$tables->rows[]=array(
			'<td colspan="2"><strong>'.$objTypeName.'</strong></td>',
			$isEditDetail && empty($info->project->proposalId) ?'<a class="sg-action" data-rel="#main" href="'.url('project/objective/'.$tpid.'/add').'" data-confirm="ต้องการเพิ่มวัตถุประสงค์ ใช่หรือไม่?"><i class="icon -adddoc -hidetext"></i><span class="-hidden">เพิ่มวัตถุประสงค์</span></a>':''
		);
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
			$ui->add('<a href="'.url('project/objective/'.$tpid.'/info/'.$objective->trid).'" class="sg-action" data-rel="box"><i class="icon -view"></i> รายละเอียด</a>');
			if ($isEdit) {
				$ui->add('<sep>');
				//$ui->add('<a href="'.url('project/develop/objective/'.$tpid,array('action'=>'move','id'=>$objective->trid)).'" class="sg-action" data-rel="box" title="ย้ายวัตถุประสงค์">ย้ายวัตถุประสงค์</a>');
				if ($objectiveIsUse) {
					$ui->add('<a href="javascript:void(0)" title="วัตถุประสงค์ข้อนี้มีการใช้งานในกิจกรรมหลักแล้ว">ลบวัตถุประสงค์ไม่ได้</a>');
				} else {
					$ui->add('<a class="sg-action" href="'.url('project/objective/'.$tpid.'/remove/'.$objective->trid).'" data-confirm="คุณต้องการลบวัตถุประสงค์นี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="this" data-removeparent="tr"><i class="icon -delete"></i> ลบวัตถุประสงค์</a>');
				}
			}
			$submenu=sg_dropbox($ui->build('ul'));

			$tables->rows[]=array(
				'<label><b>วัตถุประสงค์ข้อที่ '.(++$objectiveNo).' :</b> </label>'.view::inlineedit(array('group'=>'tr:info:objective', 'fld'=>'text1', 'tr'=>$objective->trid,'class'=>'-fill'), $objective->title, $isEdit, 'textarea'),
				'<label><b>ตัวชี้วัดความสำเร็จ :</b></label>'.view::inlineedit(array('group'=>'tr:info:objective','fld'=>'text2','tr'=>$objective->trid, 'ret'=>'html','class'=>'-fill'),$objective->indicator,$isEdit,'textarea'),
				$submenu,
			);
		}
	}


	$ret .= $tables->build();

	if ($isEdit && empty($info->objective)) $ret.='<p class="notify">ยังไม่มีการกำหนดวัตถุประสงค์ของโครงการ</p>';

	// Add new objective
	if ($isEdit && empty($info->project->proposalId)) {
		$ret.='<div class="actionbar -project -objective"><a class="sg-action button floating" data-rel="#main" href="'.url('project/objective/'.$tpid.'/add').'" data-confirm="ต้องการเพิ่มวัตถุประสงค์ ข้อที่ '.($objectiveNo+1).' ใช่หรือไม่?">+ เพิ่มวัตถุประสงค์เฉพาะ ข้อที่ '.($objectiveNo+1).'</a></div>'._NL;
	}

	//$ret.=print_o($topic,'$topic');
	//$ret.=print_o($info,'$info');
	//$ret.=print_o($options,'$options');

	$ret.='</div><!-- --objective -->'._NL;
	return $ret;
}
?>