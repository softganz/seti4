<?php
/**
* Flood Model :: Get Camera Information
* Created 2018-11-16
* Modify  2020-08-25
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_flood_camera_get($conditions = NULL, $options = '{}') {
	$defaults = '{debug: false, updateView: false}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else if (is_numeric($conditions)) {
		$conditions = (Object) ['camid' => $conditions];
	}

	$result = NULL;

	if ($debug) debugMsg($conditions,'$conditions');

	$camid = $conditions->camid;

	if (empty($camid)) return $result;

	if (is_numeric($camid)) mydb::where('c.`camid` = :camid', ':camid', $camid);
	else mydb::where('c.`name` = :name', ':name',$camid);

	$stmt = 'SELECT
		c.*
		, r.`sponsor_name` `replaceSponsorName`
		, r.`sponsor_logo` `replaceSponsorLogo`
		, r.`sponsor_url` `replaceSponsorUrl`
		, r.`sponsor_text` `replaceSponsorText`
		, c.`last_photo` `photo`
		, c.`last_updated` `atdate`
		FROM %flood_cam% c
			LEFT JOIN %flood_cam% r ON r.`camid` = c.`replaceid`
		%WHERE%
		LIMIT 1';

	$rs = mydb::select($stmt);


	if ($rs->_empty) return $result;

	$result = mydb::clearprop($rs);

	if ($options->updateView) {
		mydb::query('UPDATE %flood_cam% SET `view`=`view`+1, `last_view`=:now WHERE `camid`=:camid LIMIT 1',':camid',$camid,':now',date('U'));
	}

	if ($options->photo) {
		mydb::where('p.`camid` = :camid',':camid',$camid);
		//if ($options->date) mydb::where('p.`created` <= :date', ':date',sg_date($options->date.' 23:59:59','U'));
		if ($conditions->date) mydb::where('p.`created` BETWEEN UNIX_TIMESTAMP(:fromdate) AND UNIX_TIMESTAMP(:todate)',':fromdate', $conditions->date.' 00:00:00', ':todate', $conditions->date.' 23:59:59');

		mydb::value('$LIMIT$',$options->photo);

		//TODO :: Slow Query
		$stmt = 'SELECT
			p.`aid`, p.`camid`, c.`name`, p.`photo`, p.`created` `atdate`
			FROM %flood_photo% p
				LEFT JOIN %flood_cam% c USING(`camid`)
			%WHERE%
			ORDER BY `aid` DESC
			LIMIT $LIMIT$
			-- @FLOOD.CAMERA.GET MODEL
		';

		$result->photos = mydb::select($stmt)->items;
		//debugMsg(mydb()->_query);
	}
	return $rs;
}
?>