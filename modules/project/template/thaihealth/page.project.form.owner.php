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
function project_form_owner($self,$topic,$para,$body) {
	$ret.=R::Page('project.form.activity',$self,$topic,$para,$body,'owner');
	if (post('calid') || post('trid')) return $ret;

	$ret.='<h3>บันทึกรายงานกิจกรรมของพื้นที่</h3>';
	$ret.=R::Page('project.form.show_activity',$self,$topic,$para,$body,true,'owner');
	return $ret;
}
?>