<?php
/**
 * Search from organization name
 *
 * @param String $q or $_GET['q'] or $_POST['q'] : Substring in name
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value:org_id, label:org_name},...]
 */
function project_api_follows($self) {
	sendheader('text/html');

	$getTitle = post('title');
	$getSearch = post('q');
	$getBudgetYear = post('budgetYear');
	$getChangwat = post('changwat');
	$getType = post('type');

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

	$conditions = new stdClass();
	$options = new stdClass();

	if ($getTitle) $conditions->title = $getTitle;
	else if ($getSearch) $conditions->search = $getSearch;

	if ($getBudgetYear) $conditions->budgetYear = $getBudgetYear;
	if ($getChangwat) $conditions->changwat = $getChangwat;
	if ($getType) $conditions->projectType = $getType;

	//if (empty((Array) $conditions)) return '[]';
	// debugMsg($conditions, '$conditions');

	//return '';

	$projectList = R::Model(
		'project.follows',
		$conditions,
		array(
			'items' => $items,
			'debug' => false,
		)
	);


	if ($resultType == 'autocomplete') {
	} else {
		$result->count = $projectList->count();
	}

	if ($debug) $result->debug[] = reset($projectList->items);

	foreach ($projectList->items as $rs) {
		switch ($resultType) {
			case 'autocomplete':
				$result[] = array(
					'value' => $rs->tpid,
					'label' => htmlspecialchars($rs->title),
					'desc' => htmlspecialchars($rs->orgName),
				);
				break;

			default:
				$result->items[] = array(
					'projectId' => $rs->projectId,
					'title' => $rs->title,
					'budgetYear' => $rs->pryear,
					'orgId' => $rs->orgId,
					'orgName' => $rs->orgName,
					'status' => $rs->project_status,
					'areaCode' => $rs->areacode,
					'location' => $rs->location,
				);
				break;
		}
	}

	if ($resultType == 'autocomplete') {
		if ($projectList->count() == $items) $result[] = array('value' => '...','label' => '+++ ยังมีอีก +++');
		if ($debug) {
			$result[] = array('value' => 'query','label' => $dbs->_query);
			$result[] = array('value' => 'num_rows','label' => 'Result is '.$dbs->_num_rows.' row(s).');
		}
	}

	return $result;
}
?>