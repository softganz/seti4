<?php
/**
* Delete Project TOR
* Created 2017-04-14
* Modify  2019-10-29
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_project_tor_remove($tpid, $options = '{}') {
	if (empty($tpid)) return;

	$result = NULL;

	$torInfo = R::Model('project.tor.get', $tpid);

	$stmt = 'DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "tor"';

	mydb::query($stmt,':tpid', $tpid);

	$result[] = mydb()->_query;

	if ($torInfo->photos) {
		foreach ($torInfo->photos as $photo) {
			R::Model('photo.delete',$photo->fid);
			$result[] = 'Delete photo '.$photo->fid.' : '.$photo->file;
		}
	}

	return $result;
}