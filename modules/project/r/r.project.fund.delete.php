<?php
function r_project_fund_delete($fundInfo) {
	$ret='';
	$debug=false;

	if (empty($fundInfo)) return message('error','ไม่มีข้อมูลตามที่ระบุ');

	$stmt='SELECT COUNT(*) `totalProject` FROM %topic% t WHERE t.`orgid`=:orgid LIMIT 1';
	$totalProject=mydb::select($stmt,':orgid',$fundInfo->orgid)->totalProject;

	$stmt='SELECT COUNT(*) `totalGl` FROM %project_gl% g WHERE g.`orgid`=:orgid LIMIT 1';
	$totalGl=mydb::select($stmt,':orgid',$fundInfo->orgid)->totalGl;

	$stmt='SELECT COUNT(*) `totalTr` FROM %project_tr% tr WHERE tr.`part`=:fundid LIMIT 1';
	$totalTr=mydb::select($stmt,':fundid',$fundInfo->fundid)->totalTr;

	if ($debug) {
		debugMsg('totalProject='.$totalProject);
		debugMsg('totalGl='.$totalGl);
		debugMsg('totalTr='.$totalTr);
	}

	if ($totalProject || $totalGl || $totalTr) {
		$ret.='<p class="notify">กองทุน <b>"'.$fundInfo->name.'"</b>มีการบันทึกข้อมูลแล้ว ไม่สามารถลบกองทุนได้</p>';
	} else {
		mydb::query('DELETE FROM %project_fund% WHERE `fundid`=:fundid LIMIT 1',':fundid',$fundInfo->fundid);
		//$ret.=mydb()->_query.'<br />';
		mydb::query('DELETE FROM %db_org% WHERE `orgid`=:orgid LIMIT 1',':orgid',$fundInfo->orgid);
		//$ret.=mydb()->_query.'<br />';

		$ret.='<p>ดำเนินการลบกองทุน <b>"'.$fundInfo->name.'"</b> เรียบร้อย</p>';
		$ret.='<a href="'.url('project/fund').'" class="button"><< กลับสู่หน้าหลักกองทุนตำบล</a>';
	}
	return $ret;
}
?>