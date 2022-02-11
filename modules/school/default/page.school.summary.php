<?php
function school_summary($self,$orgid = NULL) {
	if ($orgid) {
		$schoolInfo=R::Model('school.get',$orgid);
	} else {
		return R::Page('school.summary.overview',$self);
	}

	R::View('school.toolbar',$self,$schoolInfo->name,NULL,$schoolInfo);

	$self->theme->sidebar.=R::View('school.kids.menu',$orgid);

	$ret.='<h2>สถานการณ์</h2>';

	$tpid=mydb::select('SELECT `tpid` FROM %topic% t WHERE `type`="project" AND `orgid`=:orgid LIMIT 1',':orgid',$orgid)->tpid;

	$stmt='SELECT
					  `formid`, COUNT(*) amt
					FROM %project_tr%
					WHERE `tpid`=:tpid AND formid IN ("weight","schooleat","kamsaiindi","learn") AND `part`="title"
					GROUP BY `formid`;
					-- {key:"formid"} ';
	$dbs=mydb::select($stmt,':tpid',$tpid);

	$ui=new Ui(NULL,'ui-card school-card');

	$ui->add('<a href="'.url('school/summary/weight/'.$orgid).'"><img src="//softganz.com/img/img/school-nutrition.jpg" width="160" height="160" /><span class="card-title">สถานการณ์ภาวะโภชนาการนักเรียน</span></a><div class="card-hots">'.number_format($dbs->items['weight']->amt).'</div>');

	$ui->add('<a href="'.url('school/summary/eat/'.$orgid).'"><img src="//softganz.com/img/img/school-eat.jpg" width="160" height="160" /><span class="card-title">สถานการณ์การกินอาหารและการออกกำลังกายของนักเรียน</span></a><div class="card-hots">'.number_format($dbs->items['schooleat']->amt).'</div>');


	$ui->add('<a href="'.url('school/summary/learn/'.$orgid).'"><img src="//softganz.com/img/img/school-study.jpg" width="160" height="160" /><span class="card-title">ผลสัมฤทธิ์ทางการเรียน</span></a><div class="card-hots">'.number_format($dbs->items['learn']->amt).'</div>');

	$ret.=$ui->build();


	//$ret.=print_o($schoolInfo,'$schoolInfo');

	return $ret;
}
?>