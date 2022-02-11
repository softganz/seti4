<?php
function r_project_plan_expense_calculate($tpid) {
	$tagname='info';
	if (!$tpid) return fasle;
	// Update each total expense
	$stmt='UPDATE %project_tr%
					SET `num4`=`num1`*`num2`*`num3`
					WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="exptr"';
	mydb::query($stmt,':tpid',$tpid, ':tagname',$tagname);
	//echo '<br />'.mydb()->_query;

	// Update each main activity budget
	$stmt='UPDATE %project_tr% a
					LEFT JOIN (
						SELECT `parent`, `formid`, `part`, SUM(`num4`) total
						FROM %project_tr%
						WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="exptr"
						GROUP BY `parent`
						) e ON e.`parent`=a.`trid`
					SET a.`num1`=e.`total`
					WHERE a.`tpid`=:tpid AND a.`formid`=:tagname AND a.`part`="activity" ';
	mydb::query($stmt,':tpid',$tpid, ':tagname',$tagname);
	//$ret.='<pre>'.mydb()->_query.'</pre>';

	// Update project develop budget
	/*
	$stmt='UPDATE %project_dev% d
					SET d.`budget`=(SELECT SUM(`num1`) FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity")
					WHERE `tpid`=:tpid';
	mydb::query($stmt,':tpid',$tpid, ':tagname',$tagname);
	*/
	return $ret;
}
?>