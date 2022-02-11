<?php
/**
* Project API :: Proposals Items
* Created 2021-09-28
* Modify  2021-09-28
*
* @param Array $_REQUEST
* @return JSON/Array
*
* @usage project/api/proposals
*/

$debug = true;

import('model:project.proposal.php');

class ProjectApiProposals extends Page {
	var $title;
	var $search;
	var $budgetYear;
	var $changwat;
	var $options = NULL;

	function __construct() {
		$this->title = post('title');
		$this->search = post('q');
		$this->budgetYear = post('budgetYear');
		$this->changwat = post('changwat');
		$this->options->items = post('items');
		$this->options->page = post('page');
		$this->options->resultType = SG\getFirst(post('resultType'), 'json');
		$this->options->debug = debug('api');
	}

	function build() {
		if ($this->options->resultType == 'autocomplete') {
			$this->options->items = 20;

			$result = [];
			if (empty($this->search) && empty($this->title)) return $result;
		} else {
			$this->options->items = SG\getFirst($this->options->items, '*');

			$result = new stdClass();
			$result->count = 0;
			$result->items = NULL;
		}

		$conditions = new stdClass();
		$options = new stdClass();

		if ($this->title) $conditions->title = $this->title;
		else if ($this->search) $conditions->search = $this->search;

		if ($this->budgetYear) $conditions->budgetYear = $this->budgetYear;
		if ($this->changwat) $conditions->changwat = $this->changwat;

		//if (empty((Array) $conditions)) return '[]';
		//debugMsg($conditions, '$conditions');

		//return '';

		$projectList = ProjectProposalModel::items(
			$conditions,
			[
				'items' => $this->options->items,
				'debug' => false,
			]
		);

	// debugMsg($projectList,'$projectList');
		if ($this->options->resultType == 'autocomplete') {
		} else {
			$result->count = $projectList->count;
		}

		if ($this->options->debug) $result->debug[] = reset($projectList->items);

		foreach ($projectList->items as $rs) {
			switch ($this->options->resultType) {
				case 'autocomplete':
					$result[] = [
						'value' => $rs->tpid,
						'label' => htmlspecialchars($rs->title),
						'desc' => htmlspecialchars($rs->orgName),
					];
					break;

				default:
					$result->items[] = [
						'proposalId' => $rs->projectId,
						'title' => $rs->title,
						'budgetYear' => $rs->pryear,
						'orgId' => $rs->orgId,
						'orgName' => $rs->orgName,
						'status' => $rs->project_status,
						'areaCode' => $rs->areacode,
						'location' => $rs->location,
					];
					break;
			}
		}

		if ($this->options->resultType == 'autocomplete') {
			if ($projectList->count == $this->options->items) $result[] = ['value' => '...','label' => '+++ ยังมีอีก +++'];
			if ($this->options->debug) {
				// $result[] = ['value' => 'query','label' => $dbs->_query];
				$result[] = ['value' => 'num_rows','label' => 'Result is '.$projectList->count.' row(s).'];
			}
		}
		return $result;
	}
}
?>