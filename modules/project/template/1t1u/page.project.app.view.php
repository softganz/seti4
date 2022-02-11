<?php
/**
 * Project Application
 *
 * @param Object $topic
 */
function project_app_view($self,$tpid) {
	project_model::init_app_mainpage();
	$stmt='SELECT t.`tpid`, t.`title`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
					WHERE p.`tpid`=:tpid LIMIT 1';
	$rs=mydb::select($stmt,':tpid',$tpid);
	$ret.='<h2>'.$rs->title.'</h2>';
	$ret.=R::Page('project.develop.view',$self,$tpid);
	return $ret;
}
?>