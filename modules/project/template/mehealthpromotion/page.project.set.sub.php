<?php
/**
* Project Set View
*
* @param Object $self
* @param Int $tpid
* @return String
*/

$debug = true;

function project_set_sub($self, $tpid) {
	$projectInfo = R::Model('project.get', $tpid);
	$ret = '';

	R::View('project.toolbar',$self, $projectInfo->title, 'set', $projectInfo);


	$ret.='<section class="box -no-print">';
	$ret.='<h4>โครงการตามแผนงาน</h4>';
	$stmt='SELECT
					p.`tpid`, p.`pryear`, t.`title`, p.`budget`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
					WHERE p.`prtype`="โครงการ" AND t.`parent` = :tpid';
	$dbs=mydb::select($stmt,':tpid', $tpid);

	if ($dbs->count()) {
		$tables = new Table();
		$tables->thead=array('no'=>'','year -date' => 'ปีงบประมาณ', 'ชื่อติดตามโครงการ','money'=>'งบประมาณ (บาท)');
		$no=0;
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
												++$no,
												$rs->pryear+543,
												'<a href="'.url('project/'.$rs->tpid).'">'.$rs->title.'</a>',
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