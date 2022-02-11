<?php
/**
* Model Name
* Created 2019-01-01
* Modify  2019-01-01
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_paper_comment_get($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['id' => $conditions];
	}

	$stmt = 'SELECT
			c.tpid AS tpid , t.title , c.*
			, u.name AS owner
			, p.fid , p.file AS photo , p.title AS photo_title , p.description AS photo_description
		FROM %topic_comments% AS c
			LEFT JOIN %topic% AS t ON t.tpid=c.tpid
			LEFT JOIN %topic_files% as p ON p.tpid=c.tpid AND p.cid=c.cid AND p.`type`="photo"
			LEFT JOIN %users% AS u ON u.uid=c.uid
		WHERE c.`cid` = :cid LIMIT 1';

	$rs = mydb::select($stmt, ':cid', $conditions->id);

	if ($rs->_empty) return NULL;



	$result = mydb::clearprop($rs);
	return $result;
}
?>