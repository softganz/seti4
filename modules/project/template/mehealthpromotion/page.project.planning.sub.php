<?php
/**
* Project Set View
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

import('model:project.planning.php');

function project_planning_sub($self, $tpid, $action = NULL, $tranId = NULL) {
	$planningInfo = ProjectPlanningModel::get($tpid);
	$ret = '';

	//$ret .= '$action ='.$action.' $tranId = '.$tranId;

	R::View('project.toolbar',$self, $planningInfo->title, 'planning', $planningInfo);

	//$ret .= '<h3>ชุดโครงการ/ติดตามและประเมินโครงการ</h3>';

	$ret.='<section class="box -no-print">';
	$ret.='<h4>ชุดโครงการตามแผนงาน</h4>';
	$stmt='SELECT
					p.`tpid`, p.`pryear`, t.`title`, p.`budget`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
					WHERE p.`prtype`="ชุดโครงการ" AND t.`parent` = :tpid';
	$dbs=mydb::select($stmt,':tpid',$tpid);
	if ($dbs->count()) {
		$tables = new Table();
		$tables->thead=array('year -date' => 'ปีงบประมาณ', 'ชื่อชุดโครงการ','budget -money'=>'งบประมาณ');
		$no=0;
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
												$rs->pryear+543,
												'<a href="'.url('project/set/'.$rs->tpid).'">'.$rs->title.'</a>',
												number_format($rs->budget,2)
											);
		}
		$ret.=$tables->build();
	} else {
		$ret.='ไม่มี';
	}
	if ($isEdit) {
		$ret .= '<nav class="nav -sg-text-right"><a class="btn -primary -circle24" href="'.url('project/my/set/new',array('parent'=>$tpid)).'"><i class="icon -addbig -white"></i></a></nav>';
	}
	$ret.='</section><!-- box -->';


	$ret.='<section class="box -no-print">';
	$ret.='<h4>โครงการตามแผนงาน</h4>';
	$stmt='SELECT
					*
					FROM %project_tr% tr
						RIGHT JOIN %project% d USING(`tpid`)
						LEFT JOIN %topic% t USING(`tpid`)
					WHERE tr.`formid`="info" AND tr.`part`="supportplan" AND tr.`refid`=:refid AND t.`orgid`=:orgid';
	$dbs=mydb::select($stmt,':refid',$planInfo->info->planGroup, ':orgid',$planInfo->info->orgid);
	if ($dbs->count()) {
		$tables = new Table();
		$tables->thead=array('no'=>'','year -date' => 'ปีงบประมาณ', 'ชื่อติดตามโครงการ','องค์กรรับผิดชอบ','money'=>'งบประมาณ (บาท)');
		$no=0;
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
												++$no,
												$rs->pryear+543,
												'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>',
												$rs->orgnamedo,
												number_format($rs->budget,2)
											);
		}
		$ret.=$tables->build();
	} else {
		$ret.='ไม่มี';
	}
	if ($isEdit) {
		$ret .= '<nav class="nav -sg-text-right"><a class="btn -primary -circle24" href="'.url('project/my/project/new',array('parent'=>$tpid)).'"><i class="icon -addbig -white"></i></a></nav>';
	}
	$ret.='</section><!-- box -->';
	//$ret .= print_o($projectInfo,'$projectInfo');
	return $ret;
}
?>