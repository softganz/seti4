<?php
function r_project_paiddoc_canPaid($topic) {
	$error=array();

	$paided=mydb::select('SELECT SUM(`amount`) `paided` FROM %project_paiddoc% WHERE `tpid`=:tpid LIMIT 1', ':tpid',$topic->tpid)->paided;

	if ($topic->project->budget>0 && $topic->project->budget<=$paided) {
		$error[]='คำเตือน : โครงการ <b>"เบิกงบประมาณหมดแล้ว"</b><br />โครงการนี้ได้เบิกเงินงบประมาณไปหมดแล้ว จึงจะสามารถบันทึกใบเบิกเงินได้อีกนะคะ';
	}
	if ($error) return $error;
}
?>