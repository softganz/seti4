<?php
/**
* Project detail
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_info_joins($self,$tpid) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid, '{initTemplate: true}');
	$tpid = $projectInfo->tpid;

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo,'{showPrint: true}');

	$ret.='<h3>รายชื่อผู้เข้าร่วมกิจกรรมของโครงการ</h3>';
	$stmt='SELECT dos.`psnid`,
						CONCAT(p.`name`," ",p.`lname`) fullname,
						COUNT(*) `joins`
					FROM %org_doings% d
						RIGHT JOIN %org_dos% dos USING(`doid`)
						LEFT JOIN %db_person% p USING(`psnid`)
					WHERE d.`tpid`=:tpid
					GROUP BY `psnid`';
	$dbs=mydb::select($stmt,':tpid',$tpid);


	$tables = new Table();
	$tables->thead=array('no'=>'', 'ชื่อ-สกุล','amt -amt'=>'เข้าร่วม(ครั้ง)');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(++$no,$rs->fullname,$rs->joins);
	}

	$ret .= $tables->build();
	return $ret;
}
?>