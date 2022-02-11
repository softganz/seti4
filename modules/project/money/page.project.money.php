<?php
function project_money($self,$tpid=NULL,$action=NULL,$trid=NULL) {
	if ($tpid) {
		R::Module('project.template',$self,$tpid);
		$projectInfo=R::Model('project.get',$tpid);
	}

	R::View('project.toolbar',$self,'การเงินโครงการ','money',$projectInfo);

	$view='project.money.'.($action?$action:'home');
	$ret.=R::Page($view,$self,$projectInfo,$trid);
	return $ret;
}
?>