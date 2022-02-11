<?php
/**
* Project calendar information
*
* @param Object $self
* @param Integer $tpid or Object $projectInfo
* @return String
*/
function project_plan_objective($self,$tpid=NULL) {
	if (!is_object($tpid)) {
		$projectInfo=R::Model('project.get',$tpid);
	} else {
		$projectInfo=$tpid;
		$tpid=$projectInfo->tpid;
	}

	setcookie('maingrby','objective',time()+10*365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));

	$no=0;
	foreach ($projectInfo->objective as $objective) {
		$ret.='<h4>'.(++$no).'. '.$objective->title.'</h4>';
		$ret.='<ol>';
		foreach ($projectInfo->activity as $activity) {
			if (in_array($objective->trid, explode(',',$activity->objectiveId))) {
				$ret.='<li>'.$activity->title.'</li>';
				continue;
			}
		}
		$ret.='</ol>';
	}

	if ($projectInfo->info->type!='project') return $ret.message('error','This is not a project');

	return $ret;
}
?>