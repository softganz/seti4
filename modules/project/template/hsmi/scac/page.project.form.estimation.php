<?php
/**
* แบบฟอร์มการเก็บข้อมูลเพื่อการประเมินแบบมีโครงสร้าง
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_form_estimation($self,$topic,$para,$body) {
	location('paper/'.$topic->tpid.'/info/valuation');
}
?>