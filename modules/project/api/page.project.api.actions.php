<?php
/**
 * Search from organization name
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function project_api_actions($self) {
	sendheader('text/html');

	$getProjectId = post('projectId');
	$getUserId = post('u');
	$getBudgetYear = post('year');
	$getChangwat = post('changwat');
	$getAmpur = post('ampur');
	$getTambon = post('tambon');
	$getChildOf = post('childOf');
	$getDateFrom = post('dateFrom');
	$getDateEnd = post('dateEnd');

	$getResultType = SG\getFirst(post('result'), 'json');
	$getItems = intval(SG\getFirst(post('items'), 10));
	$getPage = intval(SG\getFirst(post('page'), post('p'), 1));

	$debug = debug('api');

	$result = (Object) [
		'count' => 0,
		'items' => [],
	];

	$conditions = (Object) [];
	$options = (Object) [
		'start' => $getPage == '*' ? -1 : ($getPage - 1) * $getItems,
		'item' => $getItems,
		'actionOrder' => '`trid` DESC',
		'order' => '`actionId` DESC',
		'includePhoto' => false,
		'debug' => false,
	];

	if ($getProjectId) $conditions->projectId = $getProjectId;
	if ($getUserId) $conditions->uid = $getUserId;
	if ($getBudgetYear) $conditions->budgetYear = $getBudgetYear;
	if ($getChangwat) $conditions->changwat = $getChangwat;
	if ($getAmpur) $conditions->ampur = $getAmpur;
	if ($getTambon) $conditions->tambon = $getTambon;
	if ($getChildOf) $conditions->childOf = $getChildOf;
	if ($getDateFrom) $conditions->dateFrom = $getDateFrom;
	if ($getDateEnd) $conditions->dateEnd = $getDateEnd;

	$actionList = R::Model('project.action.get', $conditions, $options);


	$result->count = count($actionList);

	if ($debug) $result->debug[] = reset($actionList->items);

	foreach ($actionList as $rs) {
		$result->items[] = [
			'actionId' => $rs->actionId,
			'projectId' => $rs->projectId,
			'actionTitle' => $rs->title,
			'projectTitle' => $rs->projectTitle,
			'parentTitle' => $rs->parentTitle,
			'actionReal' => $rs->actionReal,
			'outputOutcomeReal' => $rs->outputOutcomeReal,
			'actionDate' => $rs->actionDate,
			'actionTime' => $rs->actionTime,
			'userId' => $rs->uid,
			'ownerName' => $rs->ownerName,
			'ownerPhoto' => model::user_photo($rs->username),
			'createDate' => $rs->created,
		];
	}

	return $result;
}
?>