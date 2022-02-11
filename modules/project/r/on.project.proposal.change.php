<?php
/**
* On Project Proposal Change
* Created 2019-09-23
* Modify  2019-09-23
*
* @param Int $tpid
* @param String $action
* @param Array $para
* @return
*/

$debug = true;

function on_project_proposal_change($tpid, $action = 'update', $para = array()) {
	if ($action=='update') {
		$stmt='UPDATE %topic% SET `changed`=:changed WHERE `tpid`=:tpid LIMIT 1';
		mydb::query($stmt,':tpid',$tpid, ':changed', date('Y-m-d H:i:s'));
	} else {
		// Trick firebase update
		$firebaseCfg=cfg('firebase');

		$firebase=new Firebase($firebaseCfg['projectId'],'update');
		$data = array('tpid'=>$tpid,'tags'=>'Project Develop '.$action,'url'=>_DOMAIN.url('project/develop/'.$tpid),'time'=>array('.sv'=>'timestamp'))+$para;
		$firebase->post($data);
	}
	//$ret='ON PROJECT DEVELOP CHANGE of '.$tpid.' Query = '.mydb()->_query;
	//return $ret;
}