<?php
/**
* Get calendar item by id
*
* @param Integer $id
* @return Record Set
*/

$debug = true;

function r_calendar_get($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = (Object) [];

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$id = $conditions;
		$conditions = (Object) ['id' => $id];
	}

	$stmt = 'SELECT
		c.`id` `calId`, c.`id` `calid`
		, c.*
		, t.`title` topic_title
		, t.`type` `topicType`
		, u.`username`, u.`name` as owner_name
		FROM %calendar% AS c
			LEFT JOIN %topic% t USING(tpid)
			LEFT JOIN %users% AS u ON c.`owner`=u.`uid`
		WHERE c.`id`=:id LIMIT 1';

	$rs = mydb::select($stmt,':id',$id);

	if ($rs->_empty) return $result;

	$rs->options = json_decode($rs->options);

	$result = $rs;

	if ($rs->from_time) $result->from_time=substr($rs->from_time,0,5);
	if ($rs->to_time) $result->to_time=substr($rs->to_time,0,5);
	$result->areacode = $rs->changwat.$rs->ampur.$rs->tambon;

	$result->property = (object)property('calendar::'.$id);

	if ($debug) debugMsg($result, '$result');

	return $result;
}
?>