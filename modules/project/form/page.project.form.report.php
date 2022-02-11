<?php
/**
 * แบบรายงานความก้าวหน้าโครงการประจำงวด
 *
 * @param Object $topic
 * @param Object $para
 * @param String $report
 */
function project_form_report($self,$topic,$para,$report) {
	$tpid=$topic->tpid;
	$formid='ส.1';
	$period=$para->period;

	$isAdmin=user_access('administer projects');

	$is_edit=($topic->project->project_statuscode==1) && (user_access('administer projects') || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid));

	$reportLists=array("ส.1"=>'m1',"ส.2"=>'s2',"ง.1"=>'m1',"ง.2"=>'m2',"ส.3"=>'s3');

	$currentReport=mydb::select('SELECT `formid`,`period`, COUNT(*) reportItems FROM %project_tr% tr WHERE tr.`tpid`=:tpid AND tr.`formid` IN ("ส.1","ส.2","ง.1","ง.2","ส.3") GROUP BY `formid`,`period`',':tpid',$tpid,':formid',$formid);
	$allReport=$currentReport->_num_rows;

	$maxPeriod=cfg('project.period.max');

	$tables = new Table();
	$tables->thead[]='รายงาน';
	for ($i=1; $i<$maxPeriod; $i++) $tables->thead['amt p'.$i]='งวดที่ '.$i;
	$tables->thead['amt close']='ปิดโครงการ';
	foreach ($reportLists as $key => $value) {
		unset($row);
		$row['report']=$key;
		for ($i=1; $i<$maxPeriod; $i++) $row['p'.$i]='-';
		$row['close']='-';
		$tables->rows[$key]=$row;
	}

	foreach ($currentReport->items as $rs) {
		$tables->rows[$rs->formid]['p'.$rs->period]='<a class="button" href="'.url('paper/'.$tpid.'/owner/'.$reportLists[$rs->formid].'/period/'.$rs->period).'">ดูรายงาน</a>';
	}

	$tables->rows['ง.2']['close']='<a class="button" href="'.url('paper/'.$tpid.'/owner/m2').'">ดูรายงาน</a>';
	$tables->rows['ส.3']['close']='<a class="button" href="'.url('paper/'.$tpid.'/owner/s3').'">ดูรายงาน</a>';

	$ret .= $tables->build();

	//$ret.=print_o($tables,'$tables');
	//$ret.=print_o($currentReport,'$currentReport');

	return $ret;
}
?>