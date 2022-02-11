<?php
function project_develop_plan_view_single($self,$tpid) {
	$devInfo=R::Model('project.develop.get',$tpid);

	if (empty($devInfo)) return 'No project';

	$isEdit=$devInfo->RIGHT & _IS_EDITABLE;

	$info=project_model::get_info($tpid);

	$objectiveNo=0;
	$totalMainActBudget=0;
	$totalBudget=$totalTarget=$totalActivity=$totalActivityBudget=0;
	$j=0;
	$actid=0;
	$subBudget=$subTarget1=$subTarget2=$subActivity=$subActivityBudget=0;

	$dbs=mydb::select('SELECT ec.`name` expName, e.`trid`, e.`parent`, e.`gallery` `costid`, e.`num1` amt, e.`num2` `unitprice`, e.`num3` `times`, e.`num4` `total`, e.`detail1` `unitname`, e.`text1` detail FROM %project_tr% e LEFT JOIN %tag% ec ON ec.`taggroup`="project:expcode" AND ec.`catid`=e.`gallery` WHERE `tpid`=:tpid AND `formid`="develop" AND `part`="exptr" ORDER BY `trid` ASC',':tpid',$tpid);
	foreach ($dbs->items as $item) {
		$info->exptr[$item->parent][$item->trid]=$item;
	}

	foreach ($devInfo->mainact as $mainact) {
		if (empty($mainact->trid)) continue;

		// Generate main activity menu
		$ui=new Ui();
		if ($isEdit) {
			$ui->add('<a href="'.url('project/develop/plan/'.$tpid,array('action'=>'obj','id'=>$mainact->trid)).'" class="sg-action" data-rel="box" title="กำหนดวัตถุประสงค์">กำหนดวัตถุประสงค์</a>');
			$ui->add('<a class="sg-action" data-rel="#project-develop-plan" href="'.url('project/develop/plan/'.$tpid,array('action'=>'add','before'=>$mainact->sorder)).'" title="เพิ่มกิจกรรมก่อนกิจกรรมนี้">เพิ่มกิจกรรมก่อนกิจกรรมนี้</a>');
			$ui->add('<a href="'.url('project/develop/plan/'.$tpid,array('action'=>'reorder','id'=>$mainact->trid)).'" class="sg-action" data-rel="box" title="เปลี่ยนลำดับการทำกิจกรรม">เปลี่ยนลำดับการทำกิจกรรม</a>');
			$ui->add('<a class="sg-action" href="'.url('project/develop/plan/'.$tpid,array('action'=>'remove','id'=>$mainact->trid)).'" data-confirm="คุณต้องการลบกิจกรรมนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#project-develop-plan">ลบกิจกรรม</a>');
		}
		if ($isAdmin) {
			$ui->add('<a class="sg-action" href="'.url('project/develop/plan/'.$tpid,array('action'=>'info','id'=>$mainact->trid)).'" data-rel="box">ข้อมูลเฉพาะ</a>');
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
				$ui->add('<a href="'.url('project/develop/plan/'.$tpid,array('action'=>'addexp','id'=>$expItem->parent,'expid'=>$expItem->trid)).'" class="sg-action" data-rel="box" title="แก้ไขรายละเอียดค่าใช้จ่าย"><i class="icon -edit"></i><span>แก้ไขรายละเอียดค่าใช้จ่าย</span></a>');
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
		$ret.='<div class="container box project-develop-plan-item">';
		$ret.='<h4>กิจกรรมหลักที่ <big style="background:#f60; border-radius:50%; display:inline-block;padding:4px; width:1.4em;height:1.4em;text-align:center;line-height:1.4em; color:#fff;">'.(++$actid).'</big> <span>'.$mainact->title.'</span>'.($isAdmin?' <small>[trid='.$mainact->trid.']</small>':'').'</h4>'.$mainactMenu;
		$ret.='<div class="row">';
		$ret.='<div class="col -md-6 -detail">';
		$ret.='<h5>ชื่อกิจกรรม'.($isEdit?' <a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,mainact,detail1,'.$mainact->trid)).'" data-rel="box">?</a>':'').'</h5>';

		$ret.=view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'detail1','tr'=>$mainact->trid, 'class'=>'-fill -primary', 'value'=>$mainact->title),SG\getFirst($mainact->title,'ระบุชื่อกิจกรรม'),$isEdit,'text')
		.($isEdit?'<p class="description -no-print"><em>** กรุณาระบุชื่อกิจกรรมให้สั้นและกระชับที่สุด และอธิบายรายละเอียดของกิจกรรมในช่อง "รายละเอียดกิจกรรม" **</em></p>':'');


		$ret.='<h5>วัตถุประสงค์</h5>'._NL;
		$parentObjectiveId=explode(',',$mainact->parentObjectiveId);
		if ($isEdit) {
			foreach ($devInfo->objective as $item) {
					$ret.='<abbr class="checkbox -block"><label><input type="checkbox" data-type="checkbox" class="inline-edit-field '.($isEdit?'':'-disabled').'" name="parent[]" data-group="objective:info:actobj" data-fld="parent" data-tr="'.$mainact->trid.'" data-objid="'.$item->trid.'" value="'.$item->trid.'" '.(in_array($item->trid,$parentObjectiveId)?'checked="checked"':'').' data-url="'.url('project/develop/plan/'.$tpid).' "data-callback="projectDevelopMainactAddObjective" /> '.$item->title.'</label></abbr>';
			}
		} else {
			$ret.='<ol>';
			foreach ($devInfo->objective as $item) {
				if (in_array($item->trid,$parentObjectiveId)) {
					$ret.='<li>'.$item->title.'</li>';
				}
			}
			$ret.='</ol>';
		}


		$ret.='<h5>กลุ่มเป้าหมาย</h5>';
		$ret.=R::View('project.develop.plan.target',$devInfo,$mainact->trid);



		$ret.='<h5>รายละเอียดกิจกรรม'.($isEdit?' <a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,mainact,text1,'.$mainact->trid)).'" data-rel="box">?</a>':'').'</h5>'
			.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'text1','tr'=>$mainact->trid, 'class'=>'w-10', 'ret'=>'html', 'value'=>$mainact->desc),sg_text2html($mainact->desc),$isEdit,'textarea')
			.'<h5>ระยะเวลาดำเนินงาน</h5>'
			.(
				$mainact->timeprocess ?
				// Show old value
				view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'detail2','tr'=>$mainact->trid, 'value'=>$mainact->timeprocess),$mainact->timeprocess,$isEdit)
				:
				// Show new value
				(view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'date1','tr'=>$mainact->trid, 'value'=>$mainact->fromdate,'ret'=>'date:ว ดดด ปปปป'),$mainact->fromdate,$isEdit,'datepicker')
				.' ถึง '
				.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'date2','tr'=>$mainact->trid, 'value'=>$mainact->todate,'ret'=>'date:ว ดดด ปปปป'),$mainact->todate,$isEdit,'datepicker'))
			)
			.'<h5>ผลผลิต (Output) / ผลลัพธ์ (Outcome)'.($isEdit?' <a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,mainact,text3,'.$mainact->trid)).'" data-rel="box">?</a>':'').'</h5>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'text3','tr'=>$mainact->trid, 'ret'=>'html', 'value'=>$mainact->output),sg_text2html($mainact->output),$isEdit,'textarea')
			.'<h5>ภาคีร่วมสนับสนุน (ระบุชื่อภาคีและวิธีการสนับสนุน เช่น งบประมาณ สิ่งของ การเข้าร่วมอื่นๆ)</h5>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'text4','tr'=>$mainact->trid, 'value'=>$mainact->copartner),sg_text2html($mainact->copartner),$isEdit,'textarea');
		$ret.='</div><!-- -detail -->';

		$ret.='<div class="col -md-1">&nbsp;</div>';

		$ret.='<div class="col -md-5 -budget">';
		$ret.=$expStr;
		$ret.=$isEdit?'<p align="right"><a class="sg-action btn -primary -no-print" href="'.url('project/develop/plan/'.$tpid,array('action'=>'addexp','id'=>$mainact->trid)).'" data-rel="box" title="เพิ่มค่าใช้จ่าย"><i class="icon -add"></i><span>เพิ่มค่าใช้จ่าย</span></a></p>':'';
		$ret.='</div><!-- -budget -->';

		$ret.='</div><!-- row -->';
		$ret.='<br clear="all" />';

		$ret.='<h4>กิจกรรมย่อย</h4>';
		$ret.=R::View('project.develop.plan.activity',$devInfo,$mainact->trid);

		$ret.='</div><!-- container box -->';


		$subBudget+=$mainact->budget;
		$subTarget1+=$mainact->targetChild+$mainact->targetTeen+$mainact->targetWork+$mainact->targetElder;
		$subTarget2+=$mainact->targetDisabled+$mainact->targetWoman+$mainact->targetMuslim+$mainact->targetWorker;
		$totalActivity+=count($mainact->calendar[$mainact->trid]);

	}




	$totalBudget+=$subBudget;
	if ($isEdit) {
		$ret.='<p class="noprint" style="padding:4px 10px;">';
		$ret.='<a class="sg-action btn -primary" data-rel="#project-develop-plan" href="'.url('project/develop/plan/'.$tpid.'/add').'"><i class="icon -add"></i><span>เพิ่มกิจกรรมหลัก/แผนการดำเนินงาน</span></a>';
		$ret.='</p>';
	}

	$ret.='<p>รวมงบประมาณทุกกิจกรรมของแผนการดำเนินงาน จำนวน <strong>'.number_format($totalBudget,2).'</strong> บาท</p>';

	$stmt='SELECT expCode.*, e.`trid`, `gallery`, SUM(e.`num4`) total
					FROM (
						SELECT DISTINCT eg.`catid` expGroup, eg.`name` expGroupName, ec.`catid` expc
						FROM %tag% eg
							RIGHT JOIN %tag% ec ON ec.`taggroup`="project:expcode" AND ec.`catparent`=eg.`catid`
						WHERE eg.`taggroup`="project:expgr"
					) expCode
					LEFT JOIN %project_tr% e ON e.`tpid`=:tpid AND e.`formid`="develop" AND e.`part`="exptr" AND e.`gallery`=expCode.`expc`
					WHERE e.`tpid`=:tpid GROUP BY expGroup';
	$dbs=mydb::select($stmt,':tpid',$tpid);

	$row = Array();
	$tables = new Table();
	$tables->thead[]='';
	$row[]='ค่าใช้จ่าย (บาท)';
	$percent[]='เปอร์เซ็นต์ (%)';
	foreach ($dbs->items as $item) $total+=$item->total;
	foreach ($dbs->items as $item) {
		$tables->thead['amt '.$item->expGroupName]=$item->expGroupName;
		$row[]=number_format($item->total,2);
		$percent[]=number_format($item->total*100/$total,2).'%';
	}
	$tables->thead['amt total']='รวมเงิน';
	$row[]='<strong>'.number_format($total,2).'</strong>';
	$percent[]='<strong>100.00%</strong>';
	$tables->rows[]=$row;
	$tables->rows[]=$percent;
	$ret .= $tables->build();

	$ret.='<a class="sg-action" href="'.url('project/develop/report/budgetbytype/'.$tpid).'" data-rel="box">ดูงบประมาณตามประเภท</a>';
	if ($isAdmin) $ret.=' | <a class="sg-action" href="'.url('project/develop/plan/'.$tpid,array('action'=>'calculateexp')).'" data-rel="#project-develop-plan">คำนวณค่าใช้จ่ายใหม่</a>';

	//$ret.=print_o($devInfo,'$devInfo');

	return $ret;
}
?>