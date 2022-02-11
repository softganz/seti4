<?php
/**
* Project owner
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_form_owner($self, $topic = NULL, $para = NULL, $body = NULL) {
	$ret = R::Page('project.action.plan', NULL, $topic->tpid);
	//$ret .= R::Page('project.form.activity', $self, $topic, $para, $body, 'owner');
	//if (post('calid') || post('trid')) return $ret;

	//$ret .= '<h3>บันทึกกิจกรรมของพื้นที่</h3>';
	$ret .= R::Page('project.action.done', NULL, $topic->tpid);
	unset($body->comment, $body->comment_form, $body->docs);
	return $ret;
}
?>