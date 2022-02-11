<?php
function r_project_develop_calculateexp($tpid) {
	if (!$tpid) return fasle;
	// Update each total expense
	$stmt='UPDATE %project_tr%
					SET `num4`=`num1`*`num2`*`num3`
					WHERE `tpid`=:tpid AND `formid`="develop" AND `part`="exptr"';
	mydb::query($stmt,':tpid',$tpid);
	//echo '<br />'.mydb()->_query;

	// Update each main activity budget
	$stmt='UPDATE %project_tr% a
					LEFT JOIN (
						SELECT `parent`, `formid`, `part`, SUM(`num4`) total
						FROM %project_tr%
						WHERE `tpid`=:tpid AND `formid`="develop" AND `part`="exptr"
						GROUP BY `parent`
						) e ON e.`parent`=a.`trid`
					SET a.`num1`=e.`total`
					WHERE a.`tpid`=:tpid AND a.`formid`="info" AND a.`part`="mainact" ';
	mydb::query($stmt,':tpid',$tpid);
	//$ret.='<pre>'.mydb()->_query.'</pre>';

	// Update project develop budget
	$stmt='UPDATE %project_dev% a
					SET a.`budget`=(SELECT SUM(`num1`) FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="mainact")
					WHERE `tpid`=:tpid';
	mydb::query($stmt,':tpid',$tpid);
	return $ret;
}
?>