<?php
/**
* Project Development Guideline
*
* @param 
* @return String
*/

$debug = true;

function project_develop_guideline($self, $tpid, $action = NULL, $trid = NULL) {
	$devInfo = R::Model('project.develop.get', $tpid);
	$isEdit = $action == 'edit';

	$ret = '';


	$tables = new Table();
	$tables->thead = array('no' => '', 'แนวทาง', 'วิธีการสำคัญ');

	$stmt='SELECT p.*,pn.`name` `planName`
					FROM %tag% p
						LEFT JOIN %tag% pn ON pn.`taggroup`="project:planning" AND CONCAT("project:guideline:",pn.`catid`)=p.`taggroup`
					WHERE p.`taggroup` IN
						(SELECT CONCAT("project:guideline:",`refid`) FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="develop" AND `part`="supportplan")';
	$guidelineDbs=mydb::select($stmt,':tpid',$tpid);

	$no = 0;
	foreach ($guidelineDbs->items as $rs) {
		$detail=json_decode($rs->description);
		$optionsObjective[$rs->planName][$rs->taggroup.':'.$rs->catid]=$detail->objective;
		$tables->rows[] = array(
												++$no,
												$detail->guideline,
												$detail->process
											);
	}
	$ret .= $tables->build();


	// TODO : Add Project Develop Guideline
	if ($isEdit) {
	}

	return $ret;
}
?>