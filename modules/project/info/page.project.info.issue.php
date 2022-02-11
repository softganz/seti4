<?php
/**
* Project :: Follow Issue Information
* Created 2021-10-26
* Modify  2021-10-27
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.issue
*/

$debug = true;

class ProjectInfoIssue extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		$isEdit = $this->projectInfo->info->isEdit && post('mode') != 'view';

		$issueDbs = mydb::select(
			'SELECT a.*, tg.`catid`, tg.`name`
			FROM %tag% tg
				LEFT JOIN
					(
						SELECT
						 tr.`trid`, tr.`refid`
						FROM %project_tr% tr
						WHERE tr.`tpid` = :projectId AND tr.`formid` = "info" AND tr.`part` = "supportplan"
					) a
				ON a.`refid` = tg.`catid`
			WHERE tg.`taggroup` = "project:planning"
			ORDER BY tg.`weight`, tg.`catid`
			',
			[':projectId' => $this->projectId]
		);

		// debugMsg($issueDbs, '$issueDbs');
		// debugMsg(mydb()->_query);

		if ($issueDbs->_empty) return NULL;

		$optionsIssue = [];

		foreach ($issueDbs->items as $rs) {
			if ($isEdit) {
				$optionsIssue[] = '<abbr class="checkbox -block"><label>'
					. view::inlineedit(
						[
							'group' => 'info:supportplan:'.$rs->catid,
							'fld' => 'refid',
							'tr' => $rs->trid,
							'value' => $rs->refid,
							'removeempty' => 'yes',
							'options' => '{done: "load"}'
						],
						$rs->catid.':'.$rs->name,
						$isEdit,
						'checkbox'
					)
					.' </label></abbr>';
			} else {
				if ($rs->trid) $optionsIssue[] = $rs->name;
			}
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
			]),
			'body' => new Container([
				'tagName' => 'section',
				'id' => 'project-info-issue',
				'class' => 'project-info-issue',
				'children' => [
					$isEdit ? implode('', $optionsIssue) : implode(' , ', $optionsIssue),
				], // children
			]), // Container
		]);
	}
}
?>