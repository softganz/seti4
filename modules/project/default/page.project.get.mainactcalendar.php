<?php
function project_get_mainActCalendar($self,$mainact=NULL) {
	$mainact=SG\getFirst($mainact,post('id'));
	$ret.='<h4>รายการกิจกรรม</h4>';


	$stmt='SELECT a.*, c.*
					FROM %project_activity% a
						LEFT JOIN %calendar% c ON c.`id`=a.`calid`
					WHERE `mainact`=:mainact
					ORDER BY c.`from_date` ASC';
	$dbs=mydb::select($stmt,':mainact',$mainact);

	$tpid=$dbs->items[0]->tpid;
	$project=project_model::get_project($tpid);

	$is_edit=false;
	if ($project->project_statuscode==1) {
		$is_edit=user_access('administer projects') || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid);
	}
	$lockReportDate=project_model::get_lock_report_date($tpid);

	if ($is_edit) {
		$inlineAttr['data-update-url']=url('project/edit/tr');
		$inlineAttr['data-current-url']=url('project/get/mainActCalendar/'.$mainact);
		$inlineAttr['data-refresh-url']=url('paper/'.$tpid);
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}
	$ret.='<div id="project-report-m1" class="inline-edit project__report" '.sg_implode_attr($inlineAttr).'>'._NL;

	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับ','date'=>'วันที่','กิจกรรม','amt'=>'กลุ่มเป้าหมาย(คน)','money'=>'งบประมาณ(บาท)');
	$no=0;
	$target=0;
	$budget=0;
	foreach ($dbs->items as $rs) {
		$lockReport=$rs->from_date<=$lockReportDate;
		$is_item_edit=$is_edit && !$lockReport;
		$tables->rows[]=array(
			++$no,
			sg_date($rs->from_date,'ว ดด ปปปป'),
			view::inlineedit(array('group'=>'calendar','fld'=>'title','tr'=>$rs->calid),$rs->title,$is_item_edit),
			view::inlineedit(array('group'=>'activity','fld'=>'targetpreset','tr'=>$rs->calid,'ret'=>'numeric','callback'=>'refreshContent'),number_format($rs->targetpreset),$is_item_edit,'text'),
			view::inlineedit(array('group'=>'activity','fld'=>'budget','tr'=>$rs->calid,'ret'=>'numeric','callback'=>'refreshContent'),number_format($rs->budget,2),$is_item_edit,'text'),
		);

		$budget+=$rs->budget;
		$target+=$rs->targetpreset;
	}

	$tables->tfoot[]=array(
		'',
		'',
		'รวมงบประมาณ',
		'<td align="center"><strong>'.number_format($target).'</strong></td>',
		'<td align="right"><strong>'.number_format($budget,2).'</strong></td>'
	);

	$ret .= $tables->build();
	//$ret.='<a class="button" rel="popup" href="'.url('calendar/add/tpid/'.$tpid.'/module/project').'">+เพิ่มกิจกรรมย่อย</a><div id="project-add"></div>';
	$ret.='</div>';

	$ret.='<script>

	</script>';

	return $ret;
}
?>