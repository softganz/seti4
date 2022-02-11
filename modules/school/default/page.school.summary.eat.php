<?php
function school_summary_eat($self,$orgid) {
	if ($orgid) {
		$schoolInfo=R::Model('school.get',$orgid);
	}

	R::View('school.toolbar',$self,$schoolInfo->name,NULL,$schoolInfo);

	$isEditable=$schoolInfo->RIGHT & _IS_EDITABLE;

	$self->theme->sidebar.=R::View('school.kids.menu',$orgid);

	if ($isEditable) {
		$ret.='<div class="btn-floating -right-bottom">';
		$ret.='<a class="btn -floating -circle48" href="'.url('school/summary/eat/add/'.$orgid).'"><i class="icon -addbig -white"></i></a>';
		$ret.='</div>';
	}


	$ret.='<h2>สถานการณ์การกินอาหารและการออกกำลังกายของนักเรียน</h2>';

	$topic->tpid=mydb::select('SELECT `tpid` FROM %topic% t WHERE `type`="project" AND `orgid`=:orgid LIMIT 1',':orgid',$orgid)->tpid;
	$ret.=R::Page('project.form.eat',$self,$topic);

	//$ret.=print_o($schoolInfo,'$schoolInfo');

	$ret.='<style type="text/css">
	.main__navbar {display: none;}
	.reportbar {display: none;}
	.item.-cols11 td:nth-child(8) {display:none;}
	</style>';
	return $ret;
}
?>