<?php
/**
* Project calendar information
*
* @param Object $self
* @param Integer $tpid or Object $projectInfo
* @param String $action
* @param Integer $actid
* @return String
*/
function project_plan_detail($self,$tpid,$action = NULL,$actid=NULL) {
	if (!is_object($tpid)) {
		$projectInfo=R::Model('project.get',$tpid);
	} else {
		$projectInfo=$tpid;
		$tpid=$projectInfo->tpid;
	}

	$isEdit=$projectInfo->RIGHT & _IS_EDITABLE;
	$isAdmin=$projectInfo->RIGHT & _IS_ADMIN;

	$activity=$projectInfo->activity[$actid];

	$isSubActivity=$activity->expense?true:false;

	/*
	$ret.='<nav class="nav -plan">';
	if ($isEdit) {
		$ret.='<a class="btn" href="'.url('paper/'.$tpid.'/owner/activity?act=addreport&calid='.$activity->calid).'"><i class="icon -save"></i><span>บันทึกกิจกรรม</span></a>';
	}
	$ret.=' <a class="sg-action btn" href="'.url('project/plan/detail/'.$tpid.'/view/'.$activity->trid).'" data-rel="parent:.__plan_detail"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>';
	$ret.='</nav>';
	*/

	$ret.='<div class="container">';
	//$ret.='<h3>รายละเอียดกิจกรรม<a class="project-toogle-display" href="javascript:void(0)"><icon class="icon '.($isSubActivity?'-up':'-down').'"></i></a></h3>';
	//$ret.='<h4>กิจกรรมหลักที่ <big style="background:#f60; border-radius:50%; display:inline-block;padding:4px; width:1.4em;height:1.4em;text-align:center;line-height:1.4em; color:#fff;">'.(++$actid).'</big> <span>'.$activity->title.'</span>'.($isAdmin?' <small>[trid='.$activity->trid.']</small>':'').'</h4>'.$activityMenu;
	$ret.='<div class="row">';
	$ret.='<div class="col'.($activity->childsCount?'':' -md-6').' -detail">';
	$ret.='<table class="item"><caption>รายละเอียด'.($isSubActivity?'กิจกรรมย่อย':'').'</caption><tbody><tr><td>';

	$ret.='<b>ชื่อกิจกรรม'
		.($isEdit?' <a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,activity,detail1,'.$activity->trid)).'" data-rel="box">?</a>':'').'</b><br />'
		.view::inlineedit(
			array(
				'group'=>'tr:info:activity',
				'fld'=>'detail1',
				'tr'=>$activity->trid,
				'class'=>'-fill -primary',
				'value'=>$activity->title,
				'callback'=>'projectPlanTitleUpdate'
				),
			SG\getFirst($activity->title,'ระบุชื่อกิจกรรม'),
			$isEdit,
			'text')
	.'<br />'
	.($isEdit?'<p class="description -no-print"><em>** กรุณาระบุชื่อกิจกรรมให้สั้นและกระชับที่สุด และอธิบายรายละเอียดของกิจกรรมในช่อง "รายละเอียดกิจกรรม" **</em></p>':'');

	// Add Objective for Top Plan Only
	if (empty($activity->parent)) {
		$ret.='<b>วัตถุประสงค์</b><br />'._NL;
		$parentObjectiveId=explode(',',$activity->parentObjectiveId);
		$parentObjectiveList=array();
		foreach (explode('|', $activity->parentObjectiveList) as $v) {
			list($a,$b)=explode('=',$v);
			$parentObjectiveList[$b]=$a;
		}
		if ($isEdit) {
			foreach ($projectInfo->objective as $objItem) {
				/*
				$ret.='<abbr class="checkbox -block"><label>'
					.'<input type="checkbox" data-type="checkbox" '
					.'class="inline-edit-field '.($isEdit?'':'-disabled').'" '
					.'name="parent[]" '
					.'data-group="info:actobj" data-fld="refid" '
					.'data-tr="'.$activity->trid.'" data-objid="'.$objItem->trid.'" '
					.'value="'.$objItem->trid.'" '
					.(in_array($objItem->trid,$parentObjectiveId)?'checked="checked"':'').' '
					.'data-url="'.url('project/plan/'.$tpid).' '
					.'"data-callback="projectPlanAddObjective" '
					.'/> '
					.$objItem->title
					.'</label></abbr>';
					*/

				$ret.='<abbr class="checkbox -block"><label>'
					.'<input type="checkbox" data-type="checkbox" '
					.'class="inline-edit-field '.($isEdit?'':'-disabled').'" '
					.'name="refid[]" '
					.'data-group="info:actobj:'.$objItem->trid.'" data-fld="refid" '
					.'data-tr="'.$parentObjectiveList[$objItem->trid].'" '
					.'data-parent="'.$activity->trid.'" '
					.'value="'.$objItem->trid.'" '
					.(in_array($objItem->trid,$parentObjectiveId)?'checked="checked"':'').' '
					.'data-removeempty="yes" '
					.'/> '
					.$objItem->title
					.'</label></abbr>';
				//$ret.=print_o($activity,'$activity');
				//$ret.=print_o($objItem,'$objItem');
				//$ret.=print_o($parentObjectiveList,'$parentObjectiveList');
			}
		} else {
			$ret.='<ol>';
			foreach ($projectInfo->objective as $objItem) {
				if (in_array($objItem->trid,$parentObjectiveId)) {
					$ret.='<li>'.$objItem->title.'</li>';
				}
			}
			$ret.='</ol>';
		}
	}


	$ret.='<b>กลุ่มเป้าหมาย</b><br />';
	$ret.=R::View('project.plan.target.view',$projectInfo,$activity->trid);


	$ret.='<b>รายละเอียดกิจกรรม'
		.($isEdit?' <a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,activity,text1,'.$activity->trid)).'" data-rel="box">?</a>':'')
		.'</b><br />'
		.view::inlineedit(array('group'=>'tr:info:activity','fld'=>'text1','tr'=>$activity->trid, 'class'=>'w-10', 'ret'=>'html', 'value'=>$activity->desc),sg_text2html($activity->desc),$isEdit,'textarea');

	$ret.='<b>ระยะเวลาดำเนินงาน</b><br />'
		.(
			$activity->timeprocess ?
			// Show old value
			view::inlineedit(array('group'=>'tr:info:activity','fld'=>'detail2','tr'=>$activity->trid, 'value'=>$activity->timeprocess),$activity->timeprocess,$isEdit)
			:
			// Show new value
			(view::inlineedit(array('group'=>'tr:info:activity','fld'=>'date1','tr'=>$activity->trid, 'value'=>$activity->fromdate,'ret'=>'date:ว ดดด ปปปป'),$activity->fromdate,$isEdit,'datepicker')
			.' ถึง '
			.view::inlineedit(array('group'=>'tr:info:activity','fld'=>'date2','tr'=>$activity->trid, 'value'=>$activity->todate,'ret'=>'date:ว ดดด ปปปป'),$activity->todate,$isEdit,'datepicker'))
		).'<br />';

	$ret.='<b>ผลผลิต (Output) / ผลลัพธ์ (Outcome)'
		.($isEdit?' <a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,activity,text3,'.$activity->trid)).'" data-rel="box">?</a>':'')
		.'</b><br />'
		.view::inlineedit(array('group'=>'tr:info:activity','fld'=>'text3','tr'=>$activity->trid, 'ret'=>'html', 'value'=>$activity->output),sg_text2html($activity->output),$isEdit,'textarea');

	$ret.='<b>ภาคีร่วมสนับสนุน</b><br />'
		.view::inlineedit(array('group'=>'tr:info:activity','fld'=>'text4','tr'=>$activity->trid, 'value'=>$activity->copartner),sg_text2html($activity->copartner),$isEdit,'textarea')
		.($isEdit?'<p><em>ระบุชื่อภาคีและวิธีการสนับสนุน เช่น งบประมาณ สิ่งของ การเข้าร่วมอื่นๆ</em></p>':'');

	$stmt='SELECT `trid`,`tpid`,`refid` `strategyId` FROM %project_tr% WHERE `tpid`=:tpid AND `parent`=:actid AND `formid`="info" AND `part`="strategy"; -- {key:"strategyId"}';
	$projectStrategy=mydb::select($stmt,':tpid',$tpid,':actid',$actid)->items;

	//$ret.=print_o($projectStrategy,'$projectStrategy');
	$strategyList=array(
									1=>'การจัดการความรู้ นวัตกรรมและสื่อ',
									'พัฒนาขีดความสามารถของคนและเครือข่าย (Health Litercy - PA)',
									'สร้างพื้นที่ต้นแบบสุขภาวะ	- ที่บ้าน',
									'สร้างพื้นที่ต้นแบบสุขภาวะ	- ที่โรงเรียน',
									'สร้างพื้นที่ต้นแบบสุขภาวะ	- ที่องค์กร',
									'สร้างพื้นที่ต้นแบบสุขภาวะ	- ที่ชุมชน',
									'ขับเคลื่อนนโยบาย PA ทั้งระดับชาติและระดับพื้นที่',
									'องค์กรกีฬาเป็นกลไกในการขับเคลื่อนกิจกรรมทางกายและเป็นองค์กรที่มีนโยบายปลอดเหล้าบุหรี่',
									99=>'อื่น ๆ',
									);
	$ret.='<h4>ความสอดคล้องต่อยุทธศาสตร์</h4>';
	$no=0;
	$tables = new Table();
	$tables->thead=array('ยุทธศาสตร์','amt'=>'สอดคล้อง');
	foreach ($strategyList AS $strategyKey=>$strategyName) {
		$tables->rows[]=array(
											$strategyName,
											'<input type="checkbox" data-type="checkbox" '
												.'class="inline-edit-field '.($isEdit?'':'-disabled').'" '
												.'name="refid[]" '
												.'data-group="info:strategy:'.$strategyKey.'" data-fld="refid" '
												.'data-tr="'.$projectStrategy[$strategyKey]->trid.'" '
												.'data-parent="'.$actid.'" '
												.'value="'.$strategyKey.'" '
												.(array_key_exists($strategyKey, $projectStrategy)?'checked="checked"':'').' '
												.'data-removeempty="yes" '
												.'/>'
												);
	}
	$ret.=$tables->build();

	$ret.='</td></tr></tbody></table>';
	$ret.='</div><!-- -detail -->';

	if (empty($activity->childsCount)) {
		// Generate expense transaction string
		unset($expTables,$row);
		$expTotal=0;
		$expTables = new Table();
		$expTables->addClass('project-develop-exp');
		$expTables->caption='รายละเอียดงบประมาณ';
		$expTables->thead[]='ประเภท';
		$expTables->thead['amt amt']='จำนวน';
		$expTables->thead['amt unitprice']='บาท';
		$expTables->thead['amt times']='ครั้ง';
		$expTables->thead['amt total']='รวม(บาท)';
		if ($isEdit) $expTables->thead[]='';
		$no=0;
		foreach ($activity->expense as $expId) {
			$expItem=$projectInfo->expense[$expId];
			unset($erow);
			$ui=new Ui();
			if ($isEdit) {
				$ui->add('<a href="'.url('project/plan/'.$tpid.'/addexp/'.$activity->trid,array('expid'=>$expItem->trid)).'" class="sg-action" data-rel="box" title="แก้ไขรายละเอียดค่าใช้จ่าย"><i class="icon -edit"></i><span>แก้ไขรายละเอียดค่าใช้จ่าย</span></a>');
				$ui->add('<a class="sg-action" href="'.url('project/plan/'.$tpid.'/removeexp/'.$activity->trid,array('expid'=>$expItem->trid)).'" data-confirm="คุณต้องการลบค่าใช้จ่ายนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#plan-detail-'.$actid.'"><i class="icon -delete"></i><span>ลบค่าใช้จ่าย</span></a>');
			}
			$exptrMenu=$ui->count()?sg_dropbox($ui->build()):'';
			$erow[]=$expItem->expName.($expItem->detail?'<p><em>'.$expItem->detail.'</em></p>':'');
			$erow[]=number_format($expItem->amt).' '.$expItem->unitname;
			$erow[]=number_format($expItem->unitprice,2);
			$erow[]=number_format($expItem->times);
			$erow[]=number_format($expItem->total,2);
			if ($isEdit) $erow[]=$exptrMenu;
			$expTables->rows[]=$erow;
			$expTotal+=$expItem->total;
		}
		unset($row);
		$row[]='<td colspan="4"><strong>รวมค่าใช้จ่าย</strong></td>';
		$row[]='<strong class="'.($activity->budget!=$expTotal?'-error':'').'" title="ผลรวม='.number_format($expTotal,2).' ยอดรวม='.number_format($activity->budget,2).'">'.number_format($expTotal,2).'</strong>';
		if ($isEdit) $row[]='';
		$expTables->rows[]=$row;

		$expStr=$expTables->build();
		$expStr.=$isEdit?'<p align="right"><a class="sg-action btn -primary -no-print" href="'.url('project/plan/'.$tpid.'/addexp/'.$activity->trid).'" data-rel="box" title="เพิ่มงบประมาณ"><i class="icon -addbig -white"></i><span>เพิ่มงบประมาณ</span></a></p>':'';

		//$expStr.=print_o($dbs,'$dbs');

		$ret.='<div class="col -md-1">&nbsp;</div>';
		$ret.='<div class="col -md-5 -budget">';
		$ret.=$expStr;
		$ret.='</div><!-- -budget -->';
		$ret.='<br clear="all" />';
	}

	$ret.='</div><!-- row -->';
	$ret.='</div><!-- container box -->';

	//$ret.=print_o($activity,'$activity');

	return $ret;
}
?>