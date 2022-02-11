<?php
/**
* Project trainer
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_form_trainer($self,$topic,$para,$body) {
	$ret.=R::Page('project.form.activity',$self,$topic,$para,$body,'trainer');
	if (post('calid') || post('trid')) return $ret;
	unset($body->comment,$body->comment_form,$body->docs);
	return $ret;
}
?>