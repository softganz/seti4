<?php
function project_develop_plan_single($self,$tpid,$action=NULL) {
	$devInfo=R::Model('project.develop.get',$tpid);

	if (empty($devInfo)) return 'No project';

	$isEdit=($devInfo->RIGHT & _IS_EDITABLE);// && $action=='edit';

	$info=project_model::get_info($tpid);

	$objectiveNo=0;
	$totalMainActBudget=0;
	$totalBudget=$totalTarget=$totalActivity=$totalActivityBudget=0;
	$j=0;
	$actid=0;
	$subBudget=$subTarget1=$subTarget2=$subActivity=$subActivityBudget=0;

	$stmt='SELECT
		ec.`name` expName, e.`trid`, e.`parent`, e.`gallery` `costid`, e.`num1` amt, e.`num2` `unitprice`, e.`num3` `times`, e.`num4` `total`, e.`detail1` `unitname`, e.`text1` detail
		FROM %project_tr% e
			LEFT JOIN %tag% ec ON ec.`taggroup`="project:expcode" AND ec.`catid`=e.`gallery`
		WHERE `tpid`=:tpid AND `formid`="develop" AND `part`="exptr"
		ORDER BY `trid` ASC';
	$dbs=mydb::select($stmt,':tpid',$tpid);

	foreach ($dbs->items as $item) {
		$info->exptr[$item->parent][$item->trid]=$item;
	}

	foreach ($devInfo->activity as $mainact) {
		if (empty($mainact->trid)) continue;

		// Generate main activity menu
		$ui=new Ui();
		if ($isEdit) {
			//$ui->add('<a href="'.url('project/develop/plan/'.$tpid.'/obj/'.$mainact->trid).'" class="sg-action" data-rel="box" data-width="640" title="กำหนดวัตถุประสงค์">กำหนดวัตถุประสงค์</a>');
			//$ui->add('<a class="sg-action" data-rel="#project-develop-plan-add" href="'.url('project/develop/plan/'.$tpid.'/add',array('before'=>$mainact->sorder,'ret'=>'single')).'" title="เพิ่มกิจกรรมก่อนกิจกรรมนี้"><i class="icon -add"></i><span>เพิ่มกิจกรรมก่อนกิจกรรมนี้</span></a>');
			$ui->add('<a href="'.url('project/develop/plan/'.$tpid.'/reorder/'.$mainact->trid).'" class="sg-action" data-rel="box" data-width="640" title="เปลี่ยนลำดับการทำกิจกรรม"><i class="icon -sort"></i><span>เปลี่ยนลำดับการทำกิจกรรม</span></a>');
			$ui->add('<a class="sg-action" href="'.url('project/develop/plan/'.$tpid.'/remove/'.$mainact->trid).'" data-confirm="คุณต้องการลบกิจกรรมนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#project-develop-plan" data-ret="'.url('project/develop/plan/single/'.$tpid).'"><i class="icon -cancel"></i><span>ลบกิจกรรม</span></a>');
		}
		if ($isAdmin) {
			$ui->add('<a class="sg-action" href="'.url('project/proposal/'.$tpid.'/info.plan.rawdata/'.$mainact->trid).'" data-rel="box" data-width="640">ข้อมูลเฉพาะ</a>');
		}
		$mainactMenu=$ui->count()?sg_dropbox($ui->build(),'{class:"leftside -atright"}'):'';



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
		foreach ($info->exptr[$mainact->trid] as $expItem) {
			unset($erow);
			$ui=new Ui();
			if ($isEdit) {
				$ui->add('<a href="'.url('project/develop/plan/'.$tpid,array('action'=>'addexp','id'=>$expItem->parent,'expid'=>$expItem->trid)).'" class="sg-action" data-rel="box" data-width="640" title="แก้ไขรายละเอียดค่าใช้จ่าย"><i class="icon -edit"></i><span>แก้ไขรายละเอียดค่าใช้จ่าย</span></a>');
				$ui->add('<a class="sg-action" href="'.url('project/develop/plan/'.$tpid,array('action'=>'removeexp','id'=>$expItem->parent,'expid'=>$expItem->trid)).'" data-confirm="คุณต้องการลบค่าใช้จ่ายนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#project-develop-plan"><i class="icon -delete"></i><span>ลบค่าใช้จ่าย</span></a>');
			}
			$exptrMenu=$ui->count()?sg_dropbox($ui->build()):'';
			$erow[]=$expItem->expName.($expItem->detail?'<p>'.$expItem->detail.'</p>':'');
			$erow[]=number_format($expItem->amt).' '.$expItem->unitname;
			$erow[]=number_format($expItem->unitprice);
			$erow[]=number_format($expItem->times);
			$erow[]=number_format($expItem->total);
			if ($isEdit) $erow[]=$exptrMenu;
			$expTables->rows[]=$erow;
			$expTotal+=$expItem->total;
		}
		unset($row);
		$row[]='<td colspan="4"><strong>รวมค่าใช้จ่าย</strong></td>';
		$row[]='<strong class="'.($mainact->budget!=$expTotal?'-error':'').'" title="ผลรวม='.number_format($expTotal,2).' ยอดรวม='.number_format($mainact->budget,2).'">'.number_format($expTotal).'</strong>';
		if ($isEdit) $row[]='';
		$expTables->rows[]=$row;

		$expStr=$expTables->build();
		//$expStr.=print_o($dbs,'$dbs');



		// Generate main activity information
		$ret.='<div id="plan-detail-'.$mainact->trid.'" class="container box project-develop-plan-item">';
		$ret.='<h4>กิจกรรมที่ <big>'.(++$actid).'</big> <span>'.$mainact->title.'</span>'.($isAdmin?' <small>[trid='.$mainact->trid.']</small>':'').'</h4>'.$mainactMenu;
		$ret.='<div class="row">';
		$ret.='<div class="col -md-12 -detail">';
		$ret.='<h5>ชื่อกิจกรรม'.($isEdit?' <a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,mainact,detail1,'.$mainact->trid)).'" data-rel="box" data-width="640">?</a>':'').'</h5>';

		$ret.=view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'detail1','tr'=>$mainact->trid, 'class'=>'-fill -primary', 'value'=>$mainact->title),SG\getFirst($mainact->title,'ระบุชื่อกิจกรรม'),$isEdit,'text')
		.($isEdit?'<p class="description -no-print"><em>** กรุณาระบุชื่อกิจกรรมให้สั้นและกระชับที่สุด และอธิบายรายละเอียดของกิจกรรมในช่อง "รายละเอียดกิจกรรม" **</em></p>':'');

		$ret.='<h5>รายละเอียดกิจกรรม/งบประมาณ/อื่นๆ'.($isEdit?' <a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,mainact,text1,'.$mainact->trid)).'" data-rel="box" data-width="640">?</a>':'').'</h5>'
			.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'text1','tr'=>$mainact->trid, 'class'=>'-fill', 'ret'=>'html', 'value'=>$mainact->desc),sg_text2html($mainact->desc),$isEdit,'textarea')
			.'<h5>ระยะเวลาดำเนินงาน</h5>'
			.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'date1','tr'=>$mainact->trid, 'value'=>$mainact->fromdate,'ret'=>'date:ว ดดด ปปปป'),$mainact->fromdate?$mainact->fromdate:'',$isEdit,'datepicker')
			.' ถึง '
			.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'date2','tr'=>$mainact->trid, 'value'=>$mainact->todate,'ret'=>'date:ว ดดด ปปปป'),$mainact->todate,$isEdit,'datepicker')
			.'<h5>ผลผลิต (Output) / ผลลัพธ์ (Outcome)'.($isEdit?' <a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,mainact,text3,'.$mainact->trid)).'" data-rel="box" data-width="640">?</a>':'').'</h5>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'text3','tr'=>$mainact->trid, 'class'=>'-fill', 'ret'=>'html', 'value'=>$mainact->output),sg_text2html($mainact->output),$isEdit,'textarea')
			//.'<h5>ภาคีร่วมสนับสนุน (ระบุชื่อภาคีและวิธีการสนับสนุน เช่น งบประมาณ สิ่งของ การเข้าร่วมอื่นๆ)</h5>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'text4','tr'=>$mainact->trid, 'value'=>$mainact->copartner),sg_text2html($mainact->copartner),$isEdit,'textarea')
			.'<h5>จำนวนเงินงบประมาณของกิจกรรม (บาท)</h5>'
			.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num1','tr'=>$mainact->trid, 'ret'=>'money', 'value'=>$mainact->budget, 'callback' => 'projectDevelopPlanSingleCalculateBudget'),$mainact->budget,$isEdit)
			;
		//$ret.=print_o($mainact,'$mainact');
		$ret.='</div><!-- -detail -->';

		/*
		$ret.='<div class="col -md-1">&nbsp;</div>';

		$ret.='<div class="col -md-5 -budget">';
		$ret.=$expStr;
		$ret.=$isEdit?'<p align="right"><a class="sg-action btn -primary -no-print" href="'.url('project/develop/plan/'.$tpid.'/addexp/'.$mainact->trid).'" data-rel="box" data-width="640" title="เพิ่มค่าใช้จ่าย"><i class="icon -add"></i><span>เพิ่มค่าใช้จ่าย</span></a></p>':'';
		$ret.='</div><!-- -budget -->';
		*/

		$ret.='</div><!-- row -->';
		$ret.='<br clear="all" />';

		//$ret.='<h4>กิจกรรมย่อย</h4>';
		//$ret.=R::View('project.develop.plan.activity',$devInfo,$mainact->trid);

		$ret.='</div><!-- container box -->';


		$subBudget+=$mainact->budget;
		$subTarget1+=$mainact->targetChild+$mainact->targetTeen+$mainact->targetWork+$mainact->targetElder;
		$subTarget2+=$mainact->targetDisabled+$mainact->targetWoman+$mainact->targetMuslim+$mainact->targetWorker;
		$totalActivity += is_array($mainact->calendar) ? count($mainact->calendar[$mainact->trid]) : 0;
	}




	$totalBudget+=$subBudget;

	if ($isEdit) {
		$addButtonText='เพิ่มกิจกรรม';
		$ret.='<div id="project-develop-plan-add" class="project-develop-plan-add -no-print -sg-text-right">';
		$ret.='<a class="sg-action btn -primary" data-rel="#project-develop-plan-add" href="'.url('project/develop/plan/'.$tpid.'/add',array('ret'=>'single')).'"><i class="icon -addbig -white"></i><span>'.$addButtonText.'</span></a>';
		$ret.='</div>';
	}

	// TODO : ปรับปรุงจำนวนงบประมาณโครงการ เมื่อมีการ inlineedit งบประมาณของกิจกรรม
	$ret.='<div class="box"><h3>งบประมาณโครงการ</h3>'
				.'<p>จำนวนงบประมาณที่ต้องการสนับสนุน จำนวน <strong id="project-develop-budget">'.
				number_format($totalBudget,2)
				.'</strong> บาท</p>'
				.'</div>';

	// debugMsg($devInfo,'$devInfo');

	$ret .= '<script type="text/javascript">
	function projectDevelopPlanSingleCalculateBudget($this,data,$parent) {
		var url = "'.url('project/develop/plan/'.$tpid.'/calculatebudget').'"
		$.post(url, function(data) {
			var budget = parseFloat(data).toFixed(2)
			$("#project-develop-budget").text(budget)
		})
	}
	</script>';
	return $ret;
}
?>