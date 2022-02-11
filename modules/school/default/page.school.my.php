<?php
function school_my($self, $orgid = NULL) {
	if ($orgid) {
		$schoolInfo=R::Model('school.get',$orgid);
		location('school/info/'.$orgid);
		return $ret;
	}

	R::View('school.toolbar',$self,'My School Kids',NULL,$schoolInfo);

	$mySchool=R::Model('school.getmy');

	$ui=new Ui(NULL,'ui-card school-card');
	foreach ($mySchool as $rs) {
		$ui->add('<a href="'.url('school/info/'.$rs->orgid).'"><img src="//softganz.com/img/img/school-house.jpg" width="200" /><h3 class="card-title">'.$rs->name.'</h3></a><p class="card-detail">'.$rs->address.'</p>');
	}
	$ui->add('<a class="btn -primary" href="'.url('school/create').'"><i class="icon -addbig -white"></i><span>Create New School</span></a>','{class:"-addnew"}');
	$ret.=$ui->build();
	return $ret;
}
?>