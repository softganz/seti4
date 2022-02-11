<?php
/**
* Project Model :: NxtGen Model
* Created 2021-11-26
* Modify 	2021-11-26
*
* @param Array $args
* @return Object
*
* @usage new ProjectNxtGenModel([])
* @usage ProjectNxtGenModel::function($conditions, $options)
*/

class ProjectNxtGenModel {
	public static function createProposalRefNo($projectId) {
		$proposalInfo = mydb::select(
			'SELECT d.`tpid` `projectId`, t.`parent` `parentId`, d.`refNo`, d.`pryear`
			FROM %project_dev% d
				LEFT JOIN %topic% t ON t.`tpid` = d.`tpid`
			WHERE d.`tpid` = :projectId LIMIT 1',
			[':projectId' => $projectId]
		);

		if (!$proposalInfo->projectId) return NULL;
		else if ($proposalInfo->refNo) return $proposalInfo->refNo;

		import('model:format.php');

		$i = 0;
		$orgId = 2;
		$prefix = $proposalInfo->parentId == cfg('project')->nxt->degreeId ? 'PD' : 'PN';
		$docName = 'PROPOSAL.'.$prefix.'.'.$proposalInfo->pryear;
		$docFormat = $prefix.substr($proposalInfo->pryear + 543, -2).'/0000';
		do {
			$nextNo = FormatModel::nextNo($orgId, $docName, $docFormat);
			$docFormat = $nextNo->format;
			$refNo = $nextNo->nextNo;
			// debugMsg($nextNo, '$nextNo');

			$isDup = mydb::select(
				'SELECT `refNo` FROM %project_dev% d WHERE d.`refNo` = :refNo LIMIT 1',
				[':refNo' => $refNo]
			)->refNo;

			// debugMsg('$isDup = '.($isDup ? 'duplicate to no '.$isDup:'not duplicate').'<br />'.mydb()->_query);

			if ($isDup) {
				FormatModel::update([
					'orgId' => $orgId,
					'name' => $docName,
					'format' => $docFormat,
					'lastNo' => $refNo,
				]);
			}
			// if (++$i > 5) break;
		} while ($isDup);

		FormatModel::update([
			'orgId' => $orgId,
			'name' => $docName,
			'format' => $docFormat,
			'lastNo' => $refNo,
		]);

		mydb::query(
			'UPDATE %project_dev% SET `refNo` = :refNo WHERE `tpid` = :projectId',
			[':projectId' => $projectId, ':refNo' => $refNo]
		);
		return $refNo;
	}

	public static function createFollowRefNo($projectId) {
		$projectInfo = mydb::select(
			'SELECT p.`tpid` `projectId`, t.`parent` `parentId`, p.`prid` `refNo`, p.`pryear`
			FROM %project% p
				LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
			WHERE p.`tpid` = :projectId LIMIT 1',
			[':projectId' => $projectId]
		);

		if (!$projectInfo->projectId) return NULL;
		else if ($projectInfo->refNo) return $projectInfo->refNo;

		import('model:format.php');

		$i = 0;
		$orgId = 2;
		$prefix = $projectInfo->parentId == cfg('project')->nxt->degreeId ? 'FD' : 'FN';
		$docName = 'FOLLOW.'.$prefix.'.'.$projectInfo->pryear;
		$docFormat = $prefix.substr($projectInfo->pryear + 543, -2).'/0000';
		do {
			$nextNo = FormatModel::nextNo($orgId, $docName, $docFormat);
			$docFormat = $nextNo->format;
			$refNo = $nextNo->nextNo;
			// debugMsg($nextNo, '$nextNo');

			$isDup = mydb::select(
				'SELECT `prid` FROM %project% p WHERE p.`prid` = :refNo LIMIT 1',
				[':refNo' => $refNo]
			)->refNo;

			// debugMsg('$isDup = '.($isDup ? 'duplicate to no '.$isDup:'not duplicate').'<br />'.mydb()->_query);

			if ($isDup) {
				FormatModel::update([
					'orgId' => $orgId,
					'name' => $docName,
					'format' => $docFormat,
					'lastNo' => $refNo,
				]);
			}
			// if (++$i > 5) break;
		} while ($isDup);

		FormatModel::update([
			'orgId' => $orgId,
			'name' => $docName,
			'format' => $docFormat,
			'lastNo' => $refNo,
		]);

		mydb::query(
			'UPDATE %project% SET `prid` = :refNo WHERE `tpid` = :projectId',
			[':projectId' => $projectId, ':refNo' => $refNo]
		);
		return $refNo;
	}

}
?>