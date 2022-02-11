<?php
/**
* Project Main Activity Plan Information
*
* @param Object $self
* @param Integer $tpid
* @param String $action
* @param Integer $actid
* @param $topic Object
* @param $info Array
* @param $options JSON
* @param
* @return String
*/
function project_plan($self,$tpid=NULL,$action=NULL,$actid=NULL,$topic=NULL,$info=NULL,$options=NULL) {
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
			if ($isEdit) $ret.=__project_plan_add($tpid,NULL,NULL,$info);
			return $ret;
			break;

		case 'edit' :
			if ($isEdit) $ret.=__project_plan_add($tpid,$actid,$info->mainact[$actid],$info);
			return $ret;
			break;

		case 'remove' :
			if ($isEdit && SG\confirm() && $info->mainact[$actid]->totalCalendar==0) {
				$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `trid`=:trid AND `formid`="info" AND `part`="mainact" LIMIT 1';
				mydb::query($stmt, ':tpid',$tpid, ':trid',$actid);
				//$ret.=mydb()->_query;
				$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="actobj" AND `gallery`=:mainActId';
				mydb::query($stmt,':tpid',$tpid, ':mainActId',$actid);
				//$ret.=mydb()->_query;
			} else {
				$ret.=message('error','กิจกรรมหลักนี้มีการกิจกรรมย่อยแล้ว ไม่สามารถลบทิ้งได้');
			}
			break;

		case 'info' :
			$mainact=$info->mainact[$actid];

			$ret .= '<header class="header -box -hidden"><nav class="nav -back"><a class="sg-action" data-rel="back" href="javascript:void(0)"><i class="icon -material">arrow_back</i></a></nav><h3>'.$mainact->title.'</h3></header>';

			$ret.='<h3>กิจกรรมหลัก : '.$mainact->title.'</h3>';
			$ret.='<p><b>งบประมาณ '.number_format($mainact->budget,2).' บาท</b></p>';
			$ret.='<h3>วัตถุประสงค์</h3>';
			$ret.='<p>'.$mainact->objectiveTitle.'</p>';
			$ret.='<h3>รายละเอียดกิจกรรม : </h3>'.sg_text2html($mainact->desc);
			$ret.='<h3>ผลผลิต : </h3>'.sg_text2html($mainact->output);
			$ret.='<h3>ผลลัพธ์ : </h3>'.sg_text2html($mainact->outcome);
			$ret.='<h3>ภาคีร่วมสนับสนุน : </h3>'.sg_text2html($mainact->copartner);

			//$ret.=print_o($mainact,'$mainact');
			//$ret.=print_o($info->mainact,'$info');

			return $ret;
			break;
	}




	$ret .= '<header class="header -box -hidden"><nav class="nav -back"><a class="sg-action" data-rel="back" href="javascript:void(0)"><i class="icon -material">arrow_back</i></a></nav><h3>กิจกรรมหลัก</h3></header>';

	$tables = new Table();
	$tables->addClass('-project-plan-list');
	$tables->thead=array('no'=>'ลำดับ','กิจกรรมหลัก','center budget'=>'งบประมาณ');
	$start = $month = strtotime($info->project->date_from);
	$end = strtotime($info->project->date_end);
	while($month < $end) {
		$tables->thead[]=sg_date($month,'ดด ปป');
		$monthList[]=date('Y-m',$month);
		$month = strtotime("+1 month", $month);
	}
	$tables->thead[]='';
	foreach ($info->mainact as $rs) {
		if (!$rs->trid) continue;

		// Create submenu
		$ui=new ui();
		$ui->add('<a href="'.url('project/plan/'.$tpid.'/info/'.$rs->trid).'" class="sg-action" data-rel="box"><i class="icon -view"></i> รายละเอียด</a>');
		if ($isEdit) {
			$ui->add('<sep>');
			//$ui->add('<a href="'.url('project/develop/objective/'.$tpid,array('action'=>'move','id'=>$objective->trid)).'" class="sg-action" data-rel="box" title="ย้ายวัตถุประสงค์">ย้ายวัตถุประสงค์</a>');
			$ui->add('<a class="sg-action" href="'.url('project/plan/'.$tpid.'/edit/'.$rs->trid).'" data-rel="box" title="แก้ไขรายละเอียดกิจกรรมหลัก"><i class="icon -edit -showtext"></i> <span>แก้ไขรายละเอียด</span></a>');
			if (empty($rs->totalCalendar)) {
				$ui->add('<a class="sg-action" href="'.url('project/plan/'.$tpid.'/remove/'.$rs->trid).'" data-confirm="คุณต้องการลบกิจกรรมหลักนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="this" data-removeparent="tr"><i class="icon -delete"></i> ลบกิจกรรมหลัก</a>');
			} else {
				$ui->add('<a href="javascript:void(0)">ลบกิจกรรมหลักไม่ได้</a>');
			}
		}
		$submenu=sg_dropbox($ui->build());
		unset($row);
		$row[]=++$no;
		$row[]=$rs->title;
		$row[]=number_format($rs->budget,2);
		foreach ($monthList as $month) {
			$row[]='<span class="project-plan-month'.($month>=sg_date($rs->fromdate,'Y-m') && $month<=sg_date($rs->todate,'Y-m')?' -active':'').'">&nbsp;<!-- '.$month.'<br />'.sg_date($rs->fromdate,'Y-m').'<br />'.sg_date($rs->todate,'Y-m').'--></span>';
		}
		$row[]=$submenu;
		$tables->rows[]=$row;
	}
	$tables->tfoot[1]=array('<td></td>','รวม',number_format($info->summary->budget,2));
	foreach ($monthList as $month) $tables->tfoot[1][]='';
	$tables->tfoot[1][]='';

	$ret.=$tables->build();

	if ($info->objective) {
		if ($isEdit && $info->summary->budget!=$info->project->budget) $ret.='<p class="notify">คำเตือน : งบประมาณรวมทุกทุกกิจกรรมหลัก ('.number_format($info->summary->budget,2).' บาท) ไม่เท่ากับ งบประมาณของโครงการ ('.number_format($info->project->budget,2).' บาท)<br />กรุณาตรวจสอบความถูกต้องด้วยค่ะ!!!</p>';


		//$mainact=project_model::get_main_activity($tpid,'owner')->info[$actid];
	} else {
		if ($isEdit && empty($info->objective)) $ret.='<p class="notify">ยังไม่มีการกำหนดกิจกรรมหลักของโครงการ</p>';
	}

	if ($isEdit && $isEditDetail) {
		$ret.='<nav class="nav -page -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/plan/'.$tpid.'/add').'" data-rel="box" data-wieth="640"><i class="icon -material">add</i><span>เพิ่มกิจกรรมหลัก</span></a></div>'._NL;
	}

	//$ret.=print_o($topic,'$topic');
	//$ret.=print_o($mainact,'$mainact');
	//$ret.=print_o($info,'$info');
	//$ret.=print_o($options,'$options');

	$ret.='<style type="text/css">
	.project-plan-month {display:block; vertical-align:middle;}
	.project-plan-month.-active {background-color:green;}
	</style>';

	return $ret;
}


/**
* Add/edit plan/mainact of project
*
* @param Integer $tpid
* @param Integer $objectId
* @return String
*/
function __project_plan_add($tpid,$planId,$data,$info) {
	$post=(object)post('data');

	$ret .= '<header class="header -box -hidden"><nav class="nav -back"><a class="sg-action" data-rel="back" href="javascript:void(0)"><i class="icon -material">arrow_back</i></a></nav><h3>เพิ่มกิจกรรมหลัก</h3></header>';

	//if (!property_exists($post, 'objective') && $objectiveId) $post->objective[$objectiveId]=$objectiveId;
	$objective=project_model::get_tr($tpid,'info:objective');

	if ($post->title) {
		$post->trid=$planId;
		$post->tpid=$tpid;
		$post->uid=i()->uid;
		$post->fromdate=sg_date($post->fromdate,'Y-m-d');
		$post->todate=sg_date($post->todate,'Y-m-d');
		$post->parentObjectiveId=reset($post->objective);
		$post->sorder=mydb::select('SELECT MAX(`sorder`) maxOrder FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="mainact" LIMIT 1',':tpid',$tpid)->maxOrder+1;
		$post->formid='info';
		$post->part='mainact';
		$post->created=date('U');

		if (cfg('project.options.multipletarget')) {
			$post->target=0;
			foreach (cfg('project.target') as $key => $value) {
				$post->{$key}=sg_strip_money($post->{$key});
				$post->target+=$post->{$key};
			}
		} else {

		}

		$stmt='INSERT INTO %project_tr%
			(`trid`, `tpid`, `parent`, `uid`, `sorder`, `formid`, `part`
			, `num1`, `num2`
			, `detail1`, `text1`, `date1`, `date2`
			, `text3`, `text4`, `text6`
			, `created`)
			VALUES
			(:trid, :tpid, :parentObjectiveId, :uid, :sorder, :formid, :part
			, :budget, :target
			, :title, :desc, :fromdate, :todate
			, :output, :copartner, :outcome
			, :created)
			ON DUPLICATE KEY
			UPDATE `num1`=:budget
			, `num2`=:target
			, `detail1`=:title
			, `text1`=:desc
			, `date1`=:fromdate
			, `date2`=:todate
			, `text3`=:output
			, `text4`=:copartner
			, `text6`=:outcome';
		mydb::query($stmt,$post);
		$post->mainActId=$planId?$planId:mydb()->insert_id;
		//$ret.=mydb()->_query.'<br />';
		if (!mydb()->_error) {
			$remainObjectId=array();
			foreach ($post->objective as $item) if ($item) $remainObjectId[]=$item;
			//$ret.=print_o($remainObjectId,'$remainObjectId');
			$stmt='DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="actobj" AND `gallery`=:mainActId '.($remainObjectId?' AND `parent` NOT IN (:objidset)':'');
			mydb::query($stmt,':tpid',$tpid, ':calid',$post->calid, ':mainActId',$post->mainActId, ':objidset','SET:'.implode(',',$remainObjectId));
			//$ret.=mydb()->_query.'<br />';

			/*
			foreach ($post->objid as $key => $value) {
				$stmt='INSERT INTO %project_actguide% (`tpid`,`calid`,`objid`) VALUES (:tpid,:calid,:objid)
							ON DUPLICATE KEY UPDATE `objid`=:objid';
				mydb::query($stmt,':tpid',$tpid, ':calid',$post->calid, ':objid',$value);
				//$ret.=mydb()->_query.'<br />';
			}
			*/

			foreach ($post->objective as $parentObjectiveId=>$objIdKey) {
				$parentObjective->trid=$objIdKey;
				$parentObjective->tpid=$tpid;
				$parentObjective->parentObjectiveId=$parentObjectiveId;
				$parentObjective->mainActId=$post->mainActId;
				$parentObjective->uid=i()->uid;
				$parentObjective->formid='info';
				$parentObjective->part='actobj';
				$parentObjective->created=date('U');
				$stmt='INSERT INTO %project_tr%
						(`trid`, `tpid`, `parent`, `gallery`, `uid`, `formid`, `part`, `created`)
					VALUES
						(:trid, :tpid, :parentObjectiveId, :mainActId, :uid, :formid, :part, :created)
					ON DUPLICATE KEY UPDATE `parent`=:parentObjectiveId';
				mydb::query($stmt,$parentObjective);
				//$ret.=mydb()->_query.'<br />';
			}
			$stmt='UPDATE %project_tr% tr
							LEFT JOIN %project_tr% o ON o.`tpid`=:tpid AND o.`formid`="info" AND o.`part`="actobj" AND o.`gallery`=tr.`trid`
						SET tr.`parent`=o.`parent`
						WHERE tr.`trid`=:trid';
			mydb::query($stmt, ':tpid',$tpid, ':trid',$post->mainActId);
			//$ret.=mydb()->_query;
		}
		//$ret.=print_o($post,'$post');
		//$ret.=__project_mainact_detail($tpid,$mainActId);
		return $ret;
	}

	if ($data) $post=$data;
	foreach (explode('|',$post->parentObjectiveList) as $item) {
		list($id,$parent)=explode('=',$item);
		$post->objective[$parent]=$id;
	}

	$stmt='SELECT `num3` `studentjoin`, `num4` `teacherjoin`, `num5` `parentjoin`, `num6` `clubjoin`, `num7` `localorgjoin`, `num8` `govjoin`, `num9` `otherjoin` FROM %project_tr% WHERE `trid`=:trid LIMIT 1';
	$joinRs=mydb::select($stmt,':trid',$post->trid);
	$post->studentjoin=number_format($joinRs->studentjoin);
	$post->teacherjoin=number_format($joinRs->teacherjoin);
	$post->parentjoin=number_format($joinRs->parentjoin);
	$post->clubjoin=number_format($joinRs->clubjoin);
	$post->localorgjoin=number_format($joinRs->localorgjoin);
	$post->govjoin=number_format($joinRs->govjoin);
	$post->otherjoin=number_format($joinRs->otherjoin);

	$ret.='<h4>เพิ่มกิจกรรมหลัก</h4>';

	$form = new Form([
		'variable' => 'data',
		'action' => url('project/plan/'.$tpid.($post->trid?'/edit/'.$planId:'/add')),
		'id' => 'project-edit-movemainact',
		'class' => 'sg-form',
		'checkValid' => true,
		'rel' => 'none',
		'done' => 'close | load:#main',
		'children' => [
			'title' => [
				'type' => 'text',
				'label' => 'ชื่อกิจกรรมหลัก',
				'class' => '-fill',
				'require' => true,
				'value' => htmlspecialchars($post->title),
			],
			'objective' => [
				'label' => 'เลือกวัตถุประสงค์ :',
				'type' => 'textfield',
				'value' => (function($objective,$post) {
					$ret = '';
					foreach ($objective->items['objective'] as $item) {
						$ret .= '<div class="form-item"><label class="option" style="display:block;"><input type="checkbox" name="data[objective]['.$item->trid.']" value="'.$post->objective[$item->trid].'" '.($post->objective[$item->trid]?'checked="checked"':'').' /> '.$item->text1.'</label></div>'._NL;
					}
					return $ret;
				})($objective,$post),
			],
			'budget' => [
				'type' => 'text',
				'label' => 'งบประมาณ (บาท)',
				'class' => '-money',
				'value' => htmlspecialchars($post->budget),
				'placeholder' => '0.00',
			],
			'target' => [
				'type' => 'text',
				'label' => 'กลุ่มเป้าหมาย (คน)',
				'class' => '-numeric',
				'value' => number_format($post->target,0,'',''),
				'placeholder' => '0',
			],
			'fromdate' => [
				'type' => 'text',
				'label' => 'ระยะเวลาดำเนินงาน จาก',
				'class' => 'sg-datepicker -date',
				'require' => true,
				'value' => sg_date(SG\getFirst($post->fromdate,date('Y-m-d')),'d/m/Y'),
				'attr' => [
					'data-min-date'=>sg_date($info->project->date_from,'d/m/Y'),
					'data-max-date'=>sg_date($info->project->date_end,'d/m/Y'),
					'data-change-month'=>true,
					'data-change-year'=>true,
				],
			],
			'todate' => [
				'type' => 'text',
				'label' => 'ถึง',
				'class' => 'sg-datepicker -date',
				'require' => true,
				'value' => sg_date(SG\getFirst($post->todate,date('Y-m-d')),'d/m/Y'),
				'attr' => [
					'data-min-date'=>sg_date($info->project->date_from,'d/m/Y'),
					'data-max-date'=>sg_date($info->project->date_end,'d/m/Y'),
					'data-change-month'=>true,
					'data-change-year'=>true,
				],
			],
			'desc' => [
				'type' => 'textarea',
				'label' => 'รายละเอียดกิจกรรม',
				'class' => '-fill',
				'rows' => 3,
				'value' => $post->desc,
			],
			'output' => [
				'type' => 'textarea',
				'label' => 'ผลผลิต (Output)',
				'class' => '-fill',
				'rows' => 3,
				'value' => $post->output,
			],
			'outcome' => [
				'type' => 'textarea',
				'label' => 'ผลลัพธ์ (Outcome)',
				'class' => '-fill',
				'rows' => 3,
				'value' => $post->outcome,
			],
			'copartner' => [
				'type' => 'textarea',
				'label' => 'ภาคีร่วมสนับสนุน',
				'class' => '-fill',
				'rows' => 3,
				'description' => 'ระบุชื่อภาคีและวิธีการสนับสนุน เช่น งบประมาณ สิ่งของ การเข้าร่วมอื่นๆ',
				'value' => htmlspecialchars($post->copartner),
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="sg-action" href="javascript:void(0)" data-rel="close">ยกเลิก</a>',
				'container' => '{class: "-sg-text-right"}',
			],
		],
	]);

	$ret .= $form->build();
	if (user_access(false)) $ret.='<hr /><a class="sg-action" href="'.url('project/plan/'.$tpid.'/add/'.$objectiveId).'" data-rel="box">Refresh</a>';

	//$ret.=print_o($post,'$post');
	//$ret.=print_o($info,'$info');
	return $ret;
}
?>