<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_imed_care_get($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['id' => $conditions];
	}

	$id = $conditions->id;

	$stmt = 'SELECT
						c.*
					, u.`name` `ownerName`
					FROM %imed_careplan% c
						LEFT JOIN %users% u USING(`uid`)
					WHERE `cpid` = :cpid
					LIMIT 1';
	$rs = mydb::select($stmt, ':cpid', $id);

	if (empty($rs->_num_rows)) return NULL;

	if (!$debug) mydb::clearprop($rs);

	$result->cpid = $rs->cpid;
	$result->psnid = $rs->psnid;
	$result->orgid = $rs->orgid;
	$result->fullname = $rs->fullname;
	$result->realname = $rs->realname;
	$result->uid = $rs->uid;
	$result->RIGHT = NULL;
	$result->RIGHTBIN = NULL;
	$result->error = NULL;

	$result->info = $rs;


	$stmt = 'SELECT
		  tr.*
		, LEFT(`plantime`, 5) `plantime`
		, c.`name` `careName`
		, FROM_UNIXTIME(s.`timedata`) `donedate`
		, s.`rx` `doneDetail`
		FROM %imed_careplantr% tr
			LEFT JOIN %imed_stkcode% c ON c.`stkid` = tr.`carecode`
			LEFT JOIN %imed_service% s USING(`seq`)
		WHERE `cpid` = :cpid
		ORDER BY `plandate`, `plantime`;
		-- {key: "cptrid"}';

	$result->plan = mydb::select($stmt, ':cpid', $id)->items;

	//debugMsg($result,'$result');

	return $result;
}
?>