<?php
/**
* Peoject :: App Follow Controller
* Created 2021-01-21
* Modify  2021-01-27
*
* @param Object $self
* @param Int $projectId
* @param String $action
* @return String
*
* @usage project/app/follow/{id}/{action}
*/

$debug = true;

function project_app_follow($self, $projectId = NULL, $action = NULL) {
	$ret = '';

	//$isAdmin = user_access('administer ibuys');
	//$isOfficer = $isAdmin || user_access('access ibuys customer');
	
	//if (!$isOfficer) return message('error', 'Access Denied');

	if (!is_numeric($projectId)) {$action = $projectId; unset($projectId);} // Action as projectId and clear

	if (empty($action) && empty($projectId)) $action = 'home';
	else if (empty($action) && $projectId) $action = 'view';

	$projectInfo = is_numeric($projectId) ? R::Model('project.get', $projectId) : '';

	$argIndex = 3; // Start argument

	//debugMsg('PAGE CONTROLLER Id = '.$projectId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
	//debugMsg(func_get_args(), '$args');

	$ret = R::Page(
		'project.app.follow.'.$action,
		$self,
		$projectInfo,
		func_get_arg($argIndex),
		func_get_arg($argIndex+1),
		func_get_arg($argIndex+2),
		func_get_arg($argIndex+3),
		func_get_arg($argIndex+4)
	);

	//debugMsg('TYPE = '.gettype($ret));
	if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

	return $ret;
}

function __project_app_follow_list($projectList) {
	$tambonCard = new Ui('div', 'ui-card');
	$orgCard = new Ui('div', 'ui-card');
	$followCard = new Ui('div', 'ui-card');

	foreach ($projectList->items as $rs) {
		$url = url('project/app/follow/'.$rs->projectId);
		$cardOption = array(
			'class' => 'sg-action',
			'href' => $url,
			'data-webview' => $rs->title,
		);

		if ($rs->prtype == 'โครงการ') {
			$followCard->add(
				'<div class="header"><h3><a class="sg-action" href="'.$url.'" data-webview="'.$rs->title.'">'.$rs->title.'</a></h3></div>',
				$cardOption
			);
		} else if ($rs->ownertype == 'หน่วยงาน') {
			$orgCard->add(
				'<div class="header"><h3><a class="sg-action" href="'.$url.'" data-webview="'.$rs->title.'">'.$rs->title.'</a></h3></div>'
				. '<div class="detail">'.($rs->childCount ? $rs->childCount.' หน่วยงาน' : '').'</div>',
				$cardOption
			);
		} else {
			$tambonCard->add(
				'<div class="header"><h3><a class="sg-action" href="'.$url.'" data-webview="'.$rs->title.'">'.$rs->title.'</a></h3></div>'
				. '<div class="detail">'.($rs->childCount ? $rs->childCount.' โครงการ' : '').'</div>',
				$cardOption
			);
		}
	}
	//debugMsg($projectList,'$projectList');

	return array($tambonCard, $followCard, $orgCard);
}
?>