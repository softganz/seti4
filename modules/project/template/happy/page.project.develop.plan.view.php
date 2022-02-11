<?php
function project_develop_plan_view($self,$tpid) {
	$devInfo=R::Model('project.develop.get',$tpid);
	$info=project_model::get_info($tpid);

	$isEdit=$devInfo->RIGHT & _IS_EDITABLE;

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

	$tables = new Table();
	$tables->addClass('project-mainact-items');
	$tables->thead[]='กิจกรรม (จะทำอะไร วิธีการอย่างไร)';
	$tables->thead[]='งบประมาณ';
	$tables->thead[]='';
	if ($isEdit) $tables->thead[]='';
	foreach ($info->mainact as $mainact) {
		if (empty($mainact->trid)) continue;

		$subBudget+=$mainact->budget;
		$subTarget1+=$mainact->targetChild+$mainact->targetTeen+$mainact->targetWork+$mainact->targetElder;
		$subTarget2+=$mainact->targetDisabled+$mainact->targetWoman+$mainact->targetMuslim+$mainact->targetWorker;
		$totalActivity+=count($mainact->calendar[$mainact->trid]);

		unset($row);
		$expTotal=0;

		$expTables = new Table();
		$expTables->addClass('project-develop-exp');
		$expTables->thead[]='ประเภท';
		$expTables->thead['amt amt']='จำนวน';
		$expTables->thead['amt unitprice']='บาท';
		$expTables->thead['amt times']='ครั้ง';
		$expTables->thead['amt total']='รวม(บาท)';
		if ($isEdit) $expTables->thead[]='';
		$no=0;
		foreach ($info->exptr[$mainact->trid] as $expItem) {
			unset($erow);
			$menu=$isEdit?sg_dropbox('<ul><li><a href="'.url('project/develop/plan/'.$tpid,array('action'=>'addexp','id'=>$expItem->parent,'expid'=>$expItem->trid)).'" class="sg-action" data-rel="box" title="แก้ไขรายละเอียดค่าใช้จ่าย"><i class="icon -edit"></i>แก้ไขรายละเอียดค่าใช้จ่าย</a></li><li><a class="sg-action" href="'.url('project/develop/plan/'.$tpid,array('action'=>'removeexp','id'=>$expItem->parent,'expid'=>$expItem->trid)).'" data-confirm="คุณต้องการลบค่าใช้จ่ายนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#project-develop-plan"><i class="icon -delete"></i>ลบค่าใช้จ่าย</a></li></ul>'):'';
			$erow[]=$expItem->expName;
			//.($expItem->detail?'<p>'.$expItem->detail.'</p>':'');
			//$erow[]=$expItem->expName.($expItem->detail?'<p>'.$expItem->detail.'</p>':'');
			$erow[]=number_format($expItem->amt).' '.$expItem->unitname;
			$erow[]=number_format($expItem->unitprice);
			$erow[]=number_format($expItem->times);
			$erow[]=number_format($expItem->total);
			if ($isEdit) $erow[]=$menu;
			//'<span class="sg-dropbox click -no-print"><a href="#"><i class="icon -down"></i></a><div class="-hidden"><ul><li><a href="'.url('project/develop/plan/'.$tpid,array('action'=>'addexp','id'=>$expItem->parent,'expid'=>$expItem->trid)).'" class="sg-action" data-rel="box" title="แก้ไขรายละเอียดค่าใช้จ่าย">แก้ไขรายละเอียดค่าใช้จ่าย</a></li><li><a class="sg-action" href="'.url('project/develop/plan/'.$tpid,array('action'=>'removeexp','id'=>$expItem->parent,'expid'=>$expItem->trid)).'" data-confirm="คุณต้องการลบค่าใช้จ่ายนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#project-develop-plan">ลบค่าใช้จ่าย</a></li></ul></div></span>';
			$expTables->rows[]=$erow;
			if ($expItem->detail) $expTables->rows[]=array('<td colspan="6"><p>'.$expItem->detail.'</p></td>');
			$expTotal+=$expItem->total;
		}
		$expTables->rows[]=array('<td colspan="4"><strong>รวมค่าใช้จ่าย</strong></td>','<strong>'.number_format($expTotal).'</strong>','');

		$expStr = $expTables->build();
		//$expStr.=print_o($dbs,'$dbs');

		// Get parent objective
		$forObj='';
		$forObjNo=0;
		if ($mainact->parentObjective) {
			$forObj.='<ol style="padding:0 0 0 15px;">'._NL;
			foreach (explode('|', $mainact->parentObjective) as $item) {
				list($objId,$objTitle)=explode('=', $item);
				$forObj.='<li><a class="sg-action" href="'.url('project/develop/objective/'.$tpid,array('action'=>'info','id'=>$objId)).'" data-rel="box">'.$objTitle.'</a>'.($isEdit?' <a class="sg-action -no-print" href="'.url('project/develop/plan/'.$tpid,array('action'=>'removeobj','actid'=>$mainact->trid,'id'=>$objId)).'" title="ลบวัตถุประสงค์ออกจากกิจกรรม" data-rel="#project-develop-plan" style="width:16px;height:16px;line-height:16px;padding:0;text-align:center;" data-confirm="ต้องการลบวัตถุประสงค์นี้ออกจากกิจกรรม กรุณายืนยัน"><i class="icon -cancel"></i></a>':'').'</li>'._NL;
			}
			$forObj.='</ol>'._NL;
		}

		// Show main activity header
		$mainactSubmenu='';
		if ($isEdit) $mainactSubmenu=sg_dropbox('<ul><li><a class="sg-action" data-rel="#project-develop-plan" href="'.url('project/develop/plan/'.$tpid,array('action'=>'add','before'=>$mainact->sorder)).'" title="เพิ่มกิจกรรมก่อนกิจกรรมนี้"><i class="icon -add"></i>เพิ่มกิจกรรมก่อนกิจกรรมนี้</a></li><li><a href="'.url('project/develop/plan/'.$tpid,array('action'=>'reorder','id'=>$mainact->trid)).'" class="sg-action" data-rel="box" title="เปลี่ยนลำดับการทำกิจกรรม"><i class="icon -sort"></i>เปลี่ยนลำดับการทำกิจกรรม</a></li><li><a class="sg-action" href="'.url('project/develop/plan/'.$tpid,array('action'=>'remove','id'=>$mainact->trid)).'" data-confirm="คุณต้องการลบกิจกรรมนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#project-develop-plan"><i class="icon -delete"></i>ลบกิจกรรม</a></li>'.($isAdmin?'<li><a class="sg-action" href="'.url('project/develop/plan/'.$tpid,array('action'=>'info','id'=>$mainact->trid)).'" data-rel="box">ข้อมูลเฉพาะ</a></li>':'').'</ul>');

		$tables->rows[]=array('<td colspan="2"><big>กิจกรรมที่ <big style="background:#f60; border-radius:50%; display:inline-block;padding:4px; width:1.4em;height:1.4em;text-align:center;line-height:1.4em; color:#fff;">'.(++$actid).'</big>'.($isAdmin?' <small>[trid='.$mainact->trid.']</small>':'').'</big> '.$mainact->title.'</td>','<td style="width:32px;font-weight:normal;">'.$mainactSubmenu.'</td>','config'=>array('class'=>'subheader'));

		$objStr='';
		$parentObjectiveId=explode(',',$mainact->parentObjectiveId);
		if ($isEdit) {
			foreach ($info->objective as $item) {
					$objStr.='<abbr class="checkbox -block"><label><input type="checkbox" data-type="checkbox" class="inline-edit-field '.($isEdit?'':'-disabled').'" name="parent[]" data-group="objective:info:actobj" data-fld="parent" data-tr="'.$mainact->trid.'" data-objid="'.$item->trid.'" value="'.$item->trid.'" '.(in_array($item->trid,$parentObjectiveId)?'checked="checked"':'').' data-url="'.url('project/develop/plan/'.$tpid).' "data-callback="projectDevelopMainactAddObjective" /> '.$item->title.'</label></abbr><br />';
			}
		} else {
			$objStr.='<ol>';
			foreach ($devInfo->objective as $item) {
				if (in_array($item->trid,$parentObjectiveId)) {
					$objStr.='<li>'.$item->title.'</li>';
				}
			}
			$objStr.='</ol>';
		}

		$row[]='<h5>ชื่อกิจกรรม'.($isEdit?' <a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,mainact,detail1,'.$mainact->trid)).'" data-rel="box">?</a>':'').'</h5>'.
				view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'detail1','tr'=>$mainact->trid, 'class'=>'w-10', 'value'=>$mainact->title),SG\getFirst($mainact->title,'ระบุชื่อกิจกรรม'),$isEdit,'textarea').($isEdit?'<p class="description -no-print"><em>** กรุณาระบุชื่อกิจกรรมให้สั้นและกระชับที่สุด และอธิบายรายละเอียดของกิจกรรมในช่อง "รายละเอียดกิจกรรม" **</em></p>':'')
						// Show main activity detail
				.'<h5>วัตถุประสงค์</h5>'._NL.$objStr
				.'<h5>กลุ่มเป้าหมาย</h5>
				<table class="item">
				<tr><th colspan="3">จำแนกตามช่วงวัย</th></tr>
				<tr><td>เด็กเล็ก</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num3','tr'=>$mainact->trid, 'value'=>$mainact->targetChild, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetChild),$isEdit).'</td><td>คน</td></tr>
				<tr><td>เด็กวัยเรียน</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num4','tr'=>$mainact->trid, 'value'=>$mainact->targetTeen, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetTeen),$isEdit).'</td><td>คน</td></tr>
				<tr><td>วัยทำงาน</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num5','tr'=>$mainact->trid, 'value'=>$mainact->targetWork, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetWork),$isEdit).'</td><td>คน</td></tr>
				<tr><td>ผู้สูงอายุ</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num6','tr'=>$mainact->trid, 'value'=>$mainact->targetElder, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetElder),$isEdit).'</td><td>คน</td></tr>

				<tr><th colspan="3">จำแนกกลุ่มเฉพาะ</th></tr>
				<tr><td>คนพิการ</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num7','tr'=>$mainact->trid, 'value'=>$mainact->targetDisabled, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetDisabled),$isEdit).'</td><td>คน</td></tr>
				<tr><td>ผู้หญิง</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num8','tr'=>$mainact->trid, 'value'=>$mainact->targetWoman, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetWoman),$isEdit).'</td><td>คน</td></tr>
				<tr><td>มุสลิม</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num9','tr'=>$mainact->trid, 'value'=>$mainact->targetMuslim, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetMuslim),$isEdit).'</td><td>คน</td></tr>
				<tr><td>แรงงาน</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num10','tr'=>$mainact->trid, 'value'=>$mainact->targetWorker, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetWorker),$isEdit).'</td><td>คน</td></tr>
				<tr><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'detail3','tr'=>$mainact->trid, 'value'=>$mainact->targetOtherDesc, 'class'=>'w-10'),$mainact->targetOtherDesc?$mainact->targetOtherDesc:'อื่น ๆ ระบุ...',$isEdit).'</td><td>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'num11','tr'=>$mainact->trid, 'value'=>$mainact->targetOther, 'ret'=>'numeric','class'=>'w-10'),number_format($mainact->targetOther),$isEdit).'</td><td>คน</td></tr>
				<tr><td>รวม</td><td align="center">'.number_format($subTarget1).'/'.number_format($subTarget2).'</td><td>คน</td></tr>
				</table>'
				.'<h5>รายละเอียดกิจกรรม'.($isEdit?' <a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,mainact,text1,'.$mainact->trid)).'" data-rel="box">?</a>':'').'</h5>'.
				view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'text1','tr'=>$mainact->trid, 'class'=>'w-10', 'ret'=>'html', 'value'=>$mainact->desc),sg_text2html($mainact->desc),$isEdit,'textarea')
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

				//.'<h5>ระยะเวลาดำเนินงาน</h5>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'detail2','tr'=>$mainact->trid, 'value'=>$mainact->timeprocess),$mainact->timeprocess,$isEdit)
				.'<h5>ผลผลิต (Output) / ผลลัพธ์ (Outcome)'.($isEdit?' <a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,mainact,text3,'.$mainact->trid)).'" data-rel="box">?</a>':'').'</h5>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'text3','tr'=>$mainact->trid, 'ret'=>'html', 'value'=>$mainact->output),sg_text2html($mainact->output),$isEdit,'textarea')
				.'<h5>ภาคีร่วมสนับสนุน (ระบุชื่อภาคีและวิธีการสนับสนุน เช่น งบประมาณ สิ่งของ การเข้าร่วมอื่นๆ)</h5>'.view::inlineedit(array('group'=>'tr:info:mainact','fld'=>'text4','tr'=>$mainact->trid, 'value'=>$mainact->copartner),sg_text2html($mainact->copartner),$isEdit,'textarea');
				//.print_o($mainact,'$mainact');
		$row[]='<td colspan="2"><h5>รายละเอียดงบประมาณ</h5>'.$expStr.($isEdit?'<p align="right"><a class="sg-a btn -primary -no-print" href="'.url('project/develop/plan/'.$tpid,array('action'=>'addexp','id'=>$mainact->trid)).'" data-rel="box" title="เพิ่มค่าใช้จ่าย"><i class="icon -add"></i><span>เพิ่มค่าใช้จ่าย</span></a></p>':'').'</td>';

		$tables->rows[]=$row;
	}
	$totalMainActBudget+=$totalBudget;
	$tables->tfoot[]=array(
			//'รวม '.number_format($subTarget1).'/'.number_format($subTarget2),
			'',
			'<td colspan="2">รวมเงิน '.number_format($subBudget,2).' บาท</td>',
		);

	$ret .= $tables->build();

	$totalBudget+=$subBudget;
	if ($isEdit) {
		$ret.='<p class="noprint" style="padding:4px 10px;">';
		$ret.='<a class="sg-action button floating" data-rel="#project-develop-plan" href="'.url('project/develop/plan/'.$tpid,array('action'=>'add')).'">+เพิ่มกิจกรรม</a>';
		$ret.='</p>';
	}

	$ret.='<p>รวมงบประมาณทุกกิจกรรมของแผนการดำเนินงาน จำนวน <strong>'.number_format($totalBudget,2).'</strong> บาท</p>';

	$stmt='SELECT expCode.*, e.`trid`, `gallery`, SUM(e.`num4`) total
					FROM (
						SELECT DISTINCT eg.`catid` expGroup, eg.`name` expGroupName, ec.`catid` expc
						FROM %tag% eg
							LEFT JOIN %tag% ec ON ec.`taggroup`="project:expcode" AND ec.`catparent`=eg.`catid`
						WHERE eg.`taggroup`="project:expgr"
					) expCode
					LEFT JOIN %project_tr% e ON e.`tpid`=:tpid AND e.`formid`="develop" AND e.`part`="exptr" AND e.`gallery`=expCode.`expc`
					WHERE e.`tpid`=:tpid GROUP BY expGroup';
	$dbs=mydb::select($stmt,':tpid',$tpid);

	$tables = new Table();
	$tables->thead[]='';
	$row = ['ค่าใช้จ่าย (บาท)'];
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
	//$ret.=print_o($info,'$info');
	return $ret;
}
?>