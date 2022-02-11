<?php
function project_edit_func($function) {
	$para=para(func_get_args(),1);
	$tpid=SG\getFirst($para->tpid,post('tpid'));
	//$ret.='function='.$function.' tpid='.$tpid.'<br />'.print_o($para,'$para(edit)').print_o(post(),'post()');
	$topic=model::get_topic_by_id($tpid);
	$topic->project=project_model::get_project($tpid);
	$topic->isEditable=($topic->project->project_statuscode==1) && (user_access('administer projects','edit own project content',$topic->uid) || project_model::is_owner_of($topic->tpid) || project_model::is_trainer_of($topic->tpid));

	$ret.=R::Page('project.edit.'.$function,$self,$topic,$para);
	return $ret;
}
?>