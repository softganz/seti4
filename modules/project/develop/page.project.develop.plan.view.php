<?php
function project_develop_plan_view($self,$tpid,$action,$trid=NULL) {
	$devInfo=R::Model('project.develop.get',$tpid);
	$isDownload=post('a');
	$ret='';

	if (empty($devInfo)) return 'No project';

	$isEdit=$devInfo->RIGHT & _IS_EDITABLE;
	$isAdmin=$devInfo->RIGHT & _IS_ADMIN;
	if ($isDownload) $isEdit=$isAdmin=false;

	$info=project_model::get_info($tpid);

	$objectiveNo=0;
	$totalMainActBudget=0;
	$totalBudget=$totalTarget=$totalActivity=$totalActivityBudget=0;
	$j=0;
	$actid=0;
	$subBudget=$subTarget1=$subTarget2=$subActivity=$subActivityBudget=0;


	if ($action=='detail' && $trid) return R::View('project.develop.plan.detail',$devInfo,$trid);



	$ret.='<div id="project-develop-plan-item-'.SG\getFirst($trid,'master').'" class="__item'.($trid?'':' -master').'">';
	//$ret.=print_o($devInfo->activity,'$activity');
	if ($trid) {
		$ret.=R::View('project.develop.plan.detail',$devInfo,$trid);
		if ($devInfo->activity[$trid]->childsCount) {
			//$ret.='<div><h3>กิจกรรมย่อย</h3></div>';
		}
	}

	$ret.=R::View('project.develop.plan.activity',$devInfo,$trid);

	$ret.='</div>';




	if (empty($action)) {
		$totalBudget+=$subBudget;
		/*
		if ($isEdit) {
			$ret.='<p class="noprint" style="padding:4px 10px;">';
			$ret.='<a class="sg-action btn -primary" data-rel="#project-develop-plan" href="'.url('project/develop/plan/'.$tpid.'/add').'"><i class="icon -add"></i><span>เพิ่มกิจกรรมหลัก/แผนการดำเนินงาน</span></a>';
			$ret.='</p>';
		}
		*/

		$ret.='<h4>รวมงบประมาณทุกกิจกรรมของแผนการดำเนินงาน</h4>';

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
	}


	return $ret;
}
?>