<?php
function project_form($self,$tpid=NULL,$formname=NULL) {
	$para=para(func_get_args(),2);

	R::Module('project.template',$self,$tpid);
	$topic=model::get_topic_by_id($tpid);
	$topic->project=project_model::get_project($tpid);

	$ret=R::Page('project.form.'.$formname,$self,$topic,$para);
	//$ret.='function _form tpid='.$tpid.' formname='.$formname.'<br />'.print_o($topic,'$topic').print_o(func_get_args(),'args');
	return $ret;
}
?>