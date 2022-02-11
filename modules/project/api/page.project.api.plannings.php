<?php
/**
* project :: Get Planning List
* Created 2021-06-05
* Modify  2021-06-05
*
* @return json[{value:org_id, label:org_name},...]
*
* @usage project/plannings
*/

$debug = true;

import('model:project.planning.php');

function project_api_plannings($self) {
	sendheader('text/html');

	$getPlan = post('plan');
	$getProblem = post('problem');
	$getTitle = post('title');
	$getSearch = post('q');
	$getBudgetYear = post('year');
	$getArea = post('area');
	$getChangwat = post('changwat');
	$getAmpur = post('ampur');
	$getOrg = SG\getFirst(post('org'),post('fund'));

	$resultType = SG\getFirst(post('result'), 'json');
	$getItems = post('items');
	$getPage = intval(SG\getFirst(post('p'),1));

	$debug = debug('api');

	if ($resultType == 'autocomplete') {
		$items = SG\getFirst($getItems, 20);

		$result = array();
		if (empty($getSearch) && empty($getTitle)) return $result;
	} else {
		$items = SG\getFirst($getItems, '*');

		$result = new stdClass();
		$result->count = 0;
		$result->items = NULL;
	}

	$conditions = (Object)[];
	$options = (Object)[];

	if ($getPlan) $conditions->plan = $getPlan;
	if ($getProblem) $conditions->problem = $getProblem;

	if ($getTitle) $conditions->title = $getTitle;
	else if ($getSearch) $conditions->search = $getSearch;

	if ($getBudgetYear) $conditions->year = $getBudgetYear;
	if ($getOrg) $conditions->orgId = $getOrg;

	if ($getArea) $conditions->area = $getArea;
	if ($getAmpur) $conditions->ampur = $getAmpur;
	else if ($getChangwat) $conditions->changwat = $getChangwat;

	$options = [
		'items' => $items,
		'debug' => false,
	];


	//if (empty((Array) $conditions)) return '[]';
	//debugMsg($conditions, '$conditions');

	//return '';

	$planningList = ProjectPlanningModel::items($conditions,$options);


	if ($resultType == 'autocomplete') {
	} else {
		$result->items = $planningList;
		$result->count = count($result->items);
	}

	if ($debug) $result->debug[] = reset($planningList->items);

	// foreach ($planningList->items as $rs) {
	// 	switch ($resultType) {
	// 		case 'autocomplete':
	// 			$result[] = array(
	// 				'value' => $rs->tpid,
	// 				'label' => htmlspecialchars($rs->title),
	// 				'desc' => htmlspecialchars($rs->orgName),
	// 			);
	// 			break;

	// 		default:
	// 			$result->items[] = array(
	// 				'projectId' => $rs->projectId,
	// 				'title' => $rs->title,
	// 				'budgetYear' => $rs->pryear,
	// 				'status' => $rs->project_status,
	// 				'areaCode' => $rs->areacode,
	// 				'location' => $rs->location,
	// 			);
	// 			break;
	// 	}
	// }

	if ($resultType == 'autocomplete') {
		if ($planningList->count() == $items) $result[] = array('value' => '...','label' => '+++ ยังมีอีก +++');
		if ($debug) {
			$result[] = array('value' => 'query','label' => $dbs->_query);
			$result[] = array('value' => 'num_rows','label' => 'Result is '.$dbs->_num_rows.' row(s).');
		}
	}

	return $result;
}
?>