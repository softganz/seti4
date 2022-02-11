<?php
/**
* Project Develop Plan Information
*
* @param Integer $tpid or Object $devInfo
* @param Integer $actid
* @return String
*/

function view_project_develop_plan_render($devInfo, $activityInfo, $isEdit = false) {
	static $activityCount = 0;

	$tpid = $devInfo->tpid;
	$isAdmin = $devInfo->RIGHT & _IS_ADMIN;

	$showBudget = $devInfo->is->showBudget;

	// Generate main activity menu
	$ui = new Ui();
	if ($isEdit) {
		//$ui->add('<a href="'.url('project/develop/plan/'.$tpid.'/obj/'.$activityInfo->trid).'" class="sg-action" data-rel="box" title="กำหนดวัตถุประสงค์">กำหนดวัตถุประสงค์</a>');
		//$ui->add('<a class="sg-action" data-rel="#project-develop-plan" href="'.url('project/develop/plan/'.$tpid.'/add',array('before'=>$activityInfo->sorder)).'" title="เพิ่มกิจกรรมก่อนกิจกรรมนี้">เพิ่มกิจกรรมก่อนกิจกรรมนี้</a>');
		$ui->add('<a href="'.url('project/develop/plan/'.$tpid.'/reorder/'.$activityInfo->trid).'" class="sg-action" data-rel="box" title="เปลี่ยนลำดับการทำกิจกรรม"><i class="icon -sort"></i><span>เปลี่ยนลำดับการทำกิจกรรม</span></a>');
		$ui->add('<a class="sg-action" href="'.url('project/develop/plan/'.$tpid.'/remove/'.$activityInfo->trid).'" data-confirm="คุณต้องการลบกิจกรรมนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#project-develop-plan" data-ret="'.url('project/develop/plan/single/'.$tpid.'/edit').'"><i class="icon -cancel"></i><span>ลบกิจกรรม</span></a>');
	}
	if ($isAdmin) {
		$ui->add('<a class="sg-action" href="'.url('project/proposal/'.$tpid.'/info.plan.rawdata/'.$activityInfo->trid).'" data-rel="box"><i class="icon -view-info"></i><span>ข้อมูลเฉพาะ</span></a>');
	}
	$mainactMenu = $ui->count() ? sg_dropbox($ui->build(),'{class:"leftside -atright"}') : '';



	// Generate main activity information
	$ret .= '<div id="plan-detail-'.$activityInfo->trid.'" class="container box project-develop-plan-item">';
	$ret .= '<h4>กิจกรรมที่ '
			. '<big>'.(++$activityCount).'</big> '
			. '<span>'.$activityInfo->title.'</span>'
			. ($isAdmin?' <small>[trid='.$activityInfo->trid.']</small>':'').'</h4>'
			. $mainactMenu;
	$ret .= '<div class="row">';
	$ret .= '<div class="col -md-6 -detail">';
	$ret .= '<h5>ชื่อกิจกรรม'.($isEdit?' <a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,mainact,detail1,'.$activityInfo->trid)).'" data-rel="box">?</a>':'').'</h5>';

	$ret .= view::inlineedit(
						array('group'=>'tr:info:mainact','fld'=>'detail1','tr'=>$activityInfo->trid, 'class'=>'-fill -primary', 'value'=>$activityInfo->title),
						SG\getFirst($activityInfo->title,'ระบุชื่อกิจกรรม'),
						$isEdit,
						'text')
			. ($isEdit ? '<p class="description -no-print"><em>** กรุณาระบุชื่อกิจกรรมให้สั้นและกระชับที่สุด และอธิบายรายละเอียดของกิจกรรมในช่อง "รายละเอียดกิจกรรม" **</em></p>' : '');


	
	$ret.='<h5>วัตถุประสงค์</h5>'._NL;
	$parentObjectiveId=explode(',',$activityInfo->objectiveId);
	if ($isEdit) {
		foreach ($devInfo->objective as $item) {
				$ret.='<abbr class="checkbox -block"><label><input type="checkbox" data-type="checkbox" class="inline-edit-field '.($isEdit?'':'-disabled').'" name="parent[]" data-group="objective:info:actobj" data-fld="parent" data-tr="'.$activityInfo->trid.'" data-objid="'.$item->trid.'" value="'.$item->trid.'" '.(in_array($item->trid,$parentObjectiveId)?'checked="checked"':'').' data-url="'.url('project/develop/plan/'.$tpid).' "data-callback="projectDevelopMainactAddObjective" /> '.$item->title.'</label></abbr>';
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
	

	/*
	$ret .= '<h5>วัตถุประสงค์</h5>'._NL;
	$parentObjectiveId = explode(',', $activityInfo->parentObjectiveId);
	if ($isEdit) {
		foreach ($devInfo->objective as $item) {
				$ret .= '<abbr class="checkbox -block">'
						. '<label><input type="checkbox" data-type="checkbox" '
						. 'class="inline-edit-field '.($isEdit?'':'-disabled').'" '
						. 'name="parent[]" data-group="objective:info:actobj" '
						. 'data-fld="parent" data-tr="'.$activityInfo->trid.'" data-objid="'.$item->trid.'" value="'.$item->trid.'" '
						. (in_array($item->trid,$parentObjectiveId) ? 'checked="checked"':'')
						. ' data-url="'.url('project/develop/plan/'.$tpid)
						. ' "data-callback="projectDevelopMainactAddObjective" /> '
						. $item->title
						. '</label>'
						. '</abbr>';
		}
	} else {
		$ret .= '<ol>';
		foreach ($devInfo->objective as $item) {
			if (in_array($item->trid,$parentObjectiveId)) {
				$ret .= '<li>'.$item->title.'</li>';
			}
		}
		$ret .= '</ol>';
	}
	*/


	//$target=R::Model('project.target.getoption');
	//$ret.=print_o($target,'$target');
	//$ret.='<h5>กลุ่มเป้าหมาย</h5>';
	//$ret.=R::View('project.develop.plan.target',$devInfo,$activityInfo->trid);



	$ret .= '<h5>รายละเอียดกิจกรรม'
			. ($isEdit?' <a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,mainact,text1,'.$activityInfo->trid)).'" data-rel="box">?</a>':'').'</h5>'
			. view::inlineedit(
					array('group'=>'tr:info:mainact','fld'=>'text1','tr'=>$activityInfo->trid, 'class'=>'-fill', 'ret'=>'text', 'value'=>$activityInfo->desc)
					, $activityInfo->desc
					, $isEdit
					, 'textarea')

			. '<h5>ระยะเวลาดำเนินงาน</h5>'
			. (
				$activityInfo->timeprocess ?
				// Show old value
				view::inlineedit(
					array('group'=>'tr:info:mainact','fld'=>'detail2','tr'=>$activityInfo->trid, 'value'=>$activityInfo->timeprocess)
					, $activityInfo->timeprocess
					, $isEdit)
				:
				// Show new value
				(view::inlineedit(
					array('group'=>'tr:info:mainact','fld'=>'date1','tr'=>$activityInfo->trid, 'value'=>$activityInfo->fromdate,'ret'=>'date:ว ดดด ปปปป')
					, $activityInfo->fromdate ? $activityInfo->fromdate : ''
					, $isEdit
					, 'datepicker')
				.' ถึง '
				.view::inlineedit(
					array('group'=>'tr:info:mainact','fld'=>'date2','tr'=>$activityInfo->trid, 'value'=>$activityInfo->todate,'ret'=>'date:ว ดดด ปปปป')
					, $activityInfo->todate
					, $isEdit
					, 'datepicker'))
			 )

			. '<h5>ผลผลิต (Output) / ผลลัพธ์ (Outcome)'
			. ($isEdit ? ' <a class="sg-action -no-print" href="'.url('project/history',array('tpid'=>$tpid,'k'=>'tr,info,mainact,text3,'.$activityInfo->trid)).'" data-rel="box">?</a>' : '').'</h5>'
			. view::inlineedit(
					array('group'=>'tr:info:mainact','fld'=>'text3','tr'=>$activityInfo->trid, 'class'=>'-fill', 'ret'=>'text', 'value'=>$activityInfo->output)
					, $activityInfo->output
					, $isEdit
					, 'textarea')

			. '<h5>ภาคีร่วมสนับสนุน</h5>'
			. view::inlineedit(
					array('group'=>'tr:info:mainact','fld'=>'text4','tr'=>$activityInfo->trid, 'class'=>'-fill', 'ret'=>'text', 'value'=>$activityInfo->copartner, 'desc' => '<em>(ระบุชื่อภาคีและวิธีการสนับสนุน เช่น งบประมาณ สิ่งของ การเข้าร่วมอื่นๆ)</em>')
					, $activityInfo->copartner
					, $isEdit
					, 'textarea');

	$ret .= '</div><!-- -detail -->';

	if ($showBudget) {
		$ret .= '<div class="col -md-1">&nbsp;</div>';


		// Generate expense transaction string
		$expTotal = 0;

		$expTables = new Table();
		$expTables->addClass('project-develop-exp');
		$expTables->caption = 'รายละเอียดงบประมาณ';
		$expTables->thead[] = 'ประเภท';
		$expTables->thead['amt -amt'] = 'จำนวน';
		$expTables->thead['unitprice -money'] = 'บาท';
		$expTables->thead['times -amt'] = 'ครั้ง';
		$expTables->thead['total -amt -hover-parent'] = 'รวมเงิน';

		foreach ($activityInfo->expense as $expId) {
			$expItem = $devInfo->expense[$expId];
			$ui = new Ui('span');
			if ($isEdit) {
				$ui->add('<a href="'.url('project/develop/plan/'.$tpid.'/addexp/'.$expItem->parent,array('expid'=>$expItem->trid)).'" class="sg-action" data-rel="box" title="แก้ไขรายละเอียดค่าใช้จ่าย"><i class="icon -edit"></i></a>');
				$ui->add('<a class="sg-action" href="'.url('project/develop/plan/'.$tpid,array('action'=>'removeexp','id'=>$expItem->parent,'expid'=>$expItem->trid)).'" data-confirm="คุณต้องการลบค่าใช้จ่ายนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#project-develop-plan"><i class="icon -cancel"></i></a>');
			}
			$exptrMenu = '<nav class="nav iconset -hover">'.$ui->build().'</nav>';
			$expTables->rows[] = array(
						$expItem->expName.($expItem->detail?'<p>'.$expItem->detail.'</p>':''),
						number_format($expItem->amt).' '.$expItem->unitname,
						number_format($expItem->unitprice),
						number_format($expItem->times),
						number_format($expItem->total)
						.$exptrMenu,
					);
			$expTotal += $expItem->total;
		}
		$expTables->rows[] = array(
						'<td colspan="4"><strong>รวมค่าใช้จ่าย</strong></td>',
						'<strong class="'.($activityInfo->budget!=$expTotal?'-error':'').'" title="ผลรวม='.number_format($expTotal,2).' ยอดรวม='.number_format($activityInfo->budget,2).'">'.number_format($expTotal).'</strong>',
					);

		$expStr=$expTables->build();


		//$expStr.=print_o($dbs,'$dbs');

		$ret .= '<div class="col -md-5 -budget">';
		$ret .= $expStr;
		$ret .= $isEdit?'<p align="right"><a class="sg-action btn -primary -no-print" href="'.url('project/develop/plan/'.$tpid.'/addexp/'.$activityInfo->trid).'" data-rel="box" title="เพิ่มค่าใช้จ่าย" data-width="480"><i class="icon -add -white"></i><span>เพิ่มค่าใช้จ่าย</span></a></p>':'';
		$ret .= '</div><!-- -budget -->';
	}

	$ret .= '</div><!-- row -->';
	$ret .= '<br clear="all" />';
	//$ret.='<h4>กิจกรรมย่อย</h4>';
	//$ret.=R::View('project.develop.plan.activity',$devInfo,$activityInfo->trid);

	//$ret .= print_o($activityInfo, '$activityInfo');
	$ret .= '</div><!-- container box -->';
	return $ret;
}
?>