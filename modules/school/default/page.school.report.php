<?php
function school_report($self,$orgid = NULL) {
	if ($orgid) {
		$schoolInfo=R::Model('school.get',$orgid);
	} else {
		return R::Page('school.report.overview',$self);
	}

	R::View('school.toolbar',$self,'Analysis : '.$schoolInfo->name,NULL,$schoolInfo);

	$self->theme->sidebar.=R::View('school.kids.menu',$orgid);



	$topic->tpid=mydb::select('SELECT `tpid` FROM %topic% t WHERE `type`="project" AND `orgid`=:orgid LIMIT 1',':orgid',$orgid)->tpid;

	$ui=new Ui(NULL,'ui-card school-card');

	$ui->add('<a href="'.url('school/summary/weight/'.$orgid).'"><img src="//softganz.com/img/img/school-nutrition.jpg" width="160" height="160" /><span class="card-title">สถานการณ์ภาวะโภชนาการนักเรียน</span></a><div class="card-hots">'.number_format($dbs->items['weight']->amt).'</div>');

	$ui->add('<a href="'.url('school/summary/eat/'.$orgid).'"><img src="//softganz.com/img/img/school-eat.jpg" width="160" height="160" /><span class="card-title">สถานการณ์การกินอาหารและการออกกำลังกายของนักเรียน</span></a><div class="card-hots">'.number_format($dbs->items['schooleat']->amt).'</div>');


	//$ui->add('<a href="'.url('school/summary/learn/'.$orgid).'"><img src="//softganz.com/img/img/school-study.jpg" width="160" height="160" /><span class="card-title">ผลสัมฤทธิ์ทางการเรียน</span></a><div class="card-hots">'.number_format($dbs->items['learn']->amt).'</div>');

	$ret.=$ui->build();

	//$ret.='<br clear="all" />';


	$ret.=R::Page('project.form.weight',$self,$topic);

	//$ret.=print_o($schoolInfo,'$schoolInfo');

	$ret.='<style type="text/css">
	.main__navbar {display: none;}
	.reportbar {display: none;}
	.item.-weightform td:nth-child(21) {display:none;}
	.item.-weightform td:nth-child(19) {display:none;}
	</style>';
	return $ret;
}
?>