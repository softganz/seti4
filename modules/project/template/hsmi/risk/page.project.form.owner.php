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
function project_form_owner($self,$topic,$para=NULL,$body=NULL) {
	location('project/'.$topic->tpid.'/info.action');
	return $ret;
}
?>