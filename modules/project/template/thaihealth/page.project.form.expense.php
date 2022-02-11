<?php
/**
 * Post/Update project expense
 *
 * @param Integer $tpid
 * @param Array $_POST["activity"]
 * @return String
 */
function project_form_expense($self,$topic,$para,$body,$part='owner') {
	if ($topic->project->project_status!='กำลังดำเนินโครงการ') return false;

	$action=post('act');
	$tpid=$topic->tpid;
	$trid=post('trid');
	$isAdmin=user_access('administer projects');
	$is_edit=user_access('administer projects') || (project_model::is_owner_of($tpid) && $part=="owner") || (project_model::is_trainer_of($tpid));
	if (!$is_edit) return false;

	$lockReportDate=project_model::get_lock_report_date($tpid);




	switch ($action) {

		// Add new report of calendar
		case 'add':
			return $ret;
			break;
		
		case 'update' :
			break;

		default:

			break;
	}

	$ret.=__project_activity_expenseform($trid);
	return $ret;
}

/**
* Get activity information
* @param Int $trid
* @return Record Set
*/
function __project_activity_get($trid) {
	$stmt='SELECT tr.*
						, c.`title` activityname
						, c.`detail`
						, tr.`date1` `action_date`
						, tr.`detail1` `action_time`
						, tr.`text1` `goal_do`
						, tr.`text2` `real_do`
						, tr.`text4` `real_work`
						, tr.`text5` `problem`
						, a.`targetpreset`, a.`budget`, a.`mainact`
						, m.`detail1` mainact_title
						, m.`text3` `presetOutputOutcome`
						, m.`num2` `target`
						, m.`num3` `presetTargetChild`
						, m.`num4` `presetTargetTeen`
						, m.`num5` `presetTargetWorker`
						, m.`num6` `presetTargetElder`
						, m.`num7` `presetTargetDisabled`
						, m.`num8` `presetTargetWoman`
						, m.`num9` `presetTargetMuslim`
						, m.`num10` `presetTargetWorkman`
						, m.`num11` `presetTargetOtherman`
					FROM %project_tr% tr
						LEFT JOIN %calendar% c ON c.`id`=tr.`calid`
						LEFT JOIN %project_activity% a ON a.`calid`=tr.`calid`
						LEFT JOIN %project_tr% m ON m.`trid`=a.`mainact`
					WHERE tr.`trid`=:trid LIMIT 1';
	$rs=mydb::select($stmt,':trid',$trid);
	return $rs;
}

function __project_activity_expenseform($trid) {
	$actRs=__project_activity_get($trid);

	$mainact=project_model::get_main_activity(NULL,NULL,NULL,$actRs->parent);

	$stmt='SELECT ec.`name` expName, e.`trid`, e.`parent`, e.`gallery` `costid`, e.`num1` amt, e.`num2` `unitprice`, e.`num3` `times`, e.`num4` `total`, e.`detail1` `unitname`, e.`text1` detail FROM %project_tr% e LEFT JOIN %tag% ec ON ec.`taggroup`="project:expcode" AND ec.`catid`=e.`gallery` WHERE `parent`=:mainact AND `formid`="develop" AND `part`="exptr" ORDER BY `trid` ASC';
	$mainexp=mydb::select($stmt,':mainact',$actRs->parent);


	$ret.='<h4>รายการค่าใช้จ่ายของกิจกรรม</h4>';

	$form->config->variable='activity';
	$form->config->method='post';
	$form->config->enctype='multipart/form-data';
	$form->config->action=url(q());
	//$form->config->attr='autosave="'.url('project/edit/activityautosave/'.$tpid).'"';
	$form->title='<h3>ชื่อกิจกรรม : '.$actRs->activityname.'</h3>';
	if (cfg('project.usemainact')) $form->title.='<p><strong>กิจกรรมหลัก : '.$mainact->title.'</strong></p>';

	$form->action=array('name'=>'act', 'value'=>'updateaction', 'type'=>'hidden');
	$form->trid=array('type'=>'hidden', 'value'=>$post->trid);
	$form->tpid=array('type'=>'hidden', 'value'=>$post->tpid);
	$form->calid=array('type'=>'hidden', 'value'=>$post->calid);
	$form->part=array('type'=>'hidden', 'value'=>$post->part);
	if ($nextform) $form->nextform=array('type'=>'hidden','name'=>'nextform','value'=>$nextform);

	//$form->action_date=array('type'=>'hidden','value'=>htmlspecialchars(SG\getFirst($post->action_date,date('Y-m-d'))));

	$form->headerplan=array('type'=>'textfield','value'=>'<h4>ค่าใช้จ่ายตามแผนงานที่วางไว้</h4>');
	$form->headerdo=array('type'=>'textfield','value'=>'<h4>ค่าใช้จ่ายที่เกิดขึ้นจริง</h4>');

	$expTables = new Table();
	$expTables->class='item project-develop-exp';
	$expTables->thead[]='ประเภท';
	$expTables->thead['amt amt']='จำนวน';
	$expTables->thead['amt unitprice']='บาท';
	$expTables->thead['amt times']='ครั้ง';
	$expTables->thead['amt total']='รวม(บาท)';
	if ($isEdit) $expTables->thead[]='';
	foreach ($mainexp->items as $expItem) {
		unset($erow);
		$erow[]=$expItem->expName.($expItem->detail?'<p>'.$expItem->detail.'</p>':'');
		$erow[]=number_format($expItem->amt).' '.$expItem->unitname;
		$erow[]=number_format($expItem->unitprice);
		$erow[]=number_format($expItem->times);
		$erow[]=number_format($expItem->total);
		if ($isEdit) $erow[]=sg_dropbox('<ul><li><a href="'.url('project/develop/plan/'.$tpid,array('action'=>'addexp','id'=>$expItem->parent,'expid'=>$expItem->trid)).'" class="sg-action" data-rel="box" title="แก้ไขรายละเอียดค่าใช้จ่าย">แก้ไขรายละเอียดค่าใช้จ่าย</a></li><li><a class="sg-action" href="'.url('project/develop/plan/'.$tpid,array('action'=>'removeexp','id'=>$expItem->parent,'expid'=>$expItem->trid)).'" data-confirm="คุณต้องการลบค่าใช้จ่ายนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#project-develop-plan">ลบค่าใช้จ่าย</a></li></ul>');//'<span class="sg-dropbox click -no-print"><a href="#"><i class="icon icon-dropdown"></i></a><div class="-hidden"><ul><li><a href="'.url('project/develop/plan/'.$tpid,array('action'=>'addexp','id'=>$expItem->parent,'expid'=>$expItem->trid)).'" class="sg-action" data-rel="box" title="แก้ไขรายละเอียดค่าใช้จ่าย">แก้ไขรายละเอียดค่าใช้จ่าย</a></li><li><a class="sg-action" href="'.url('project/develop/plan/'.$tpid,array('action'=>'removeexp','id'=>$expItem->parent,'expid'=>$expItem->trid)).'" data-confirm="คุณต้องการลบค่าใช้จ่ายนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#project-develop-plan">ลบค่าใช้จ่าย</a></li></ul></div></span>';
		$expTables->rows[]=$erow;
		$expTotal+=$expItem->total;
	}
	$expTables->rows[]=array('<td colspan="4"><strong>รวมค่าใช้จ่าย</strong></td>','<strong>'.number_format($expTotal).'</strong>');

	$form->mainactExpense=$expTables->build();

	$ret .= theme('form','activity-expense',$form);

	$ret.=print_o($mainexp,'$mainexp');
	$ret.=print_o($actRs,'$actRs');
	$ret.=print_o($mainact,'$mainact');
	return $ret;
}
?>