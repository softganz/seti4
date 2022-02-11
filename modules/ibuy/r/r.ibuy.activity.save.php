<?php
/**
* ibuy :: Save Activiry
* Created 2020-09-09
* Modify  2020-09-09
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_ibuy_activity_save($data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	//if (!($data->productname || $data->landid)) return false;

	$result = new stdClass();
	$result->msgId = NULL;
	$result->plantId = NULL;
	$result->data = NULL;
	$result->query = NULL;


	$data->msgid = SG\getFirst($data->msgid);
	$data->message = SG\getFirst($data->message);
	$data->uid = i()->uid;
	$data->created = date('U');
	$data->tagname = SG\getFirst($data->tagname, 'GREEN,ACTIVITY');
	$data->landid = SG\getFirst($data->landid);
	$data->privacy = SG\getFirst($data->privacy, 'PUBLIC');
	$data->staytime = SG\getFirst($data->staytime);
	$data->locid = SG\getFirst($data->locid);
	$data->locname = SG\getFirst($data->locname);

	$stmt = 'INSERT INTO %msg%
		(
			`msgid`, `uid`, `tagname`, `privacy`
			, `landid`, `locid`, `locname`
			, `staytime`, `message`
			, `created`
		)
		VALUES
		(
			:msgid, :uid, :tagname, :privacy,
			:landid, :locid, :locname,
			:staytime, :message,
			:created
		)
		ON DUPLICATE KEY UPDATE
		`landid` = :landid
		, `locid` = :locid
		, `locname` = :locname
		, `staytime` = :staytime
		, `message` = :message';

	mydb::query($stmt, $data);
	$result->query[] = mydb()->_query;

	$createError = mydb()->_error;

	if (!$createError) {
		if (empty($data->msgid)) $data->msgid = mydb()->insert_id;

		$result->msgId = $data->msgid;

		if ($data->uploadFiles) {
			$data->refid = $data->msgid;
			$data->prename = 'green_activity_'.$data->msgid.'_';
			$data->cid = NULL;
			$uploadResult = R::Model('photo.upload', $data->uploadFiles, $data);
		}

		if ($data->productname && $data->msgid) {
			$data->detail = SG\getFirst($data->detail,$data->message);
			$result->plant = R::Model('ibuy.plant.save', $data);

			$result->plantId = $data->plantid = $result->plant->plantId;

			mydb::query('UPDATE %msg% SET `plantid` = :plantid WHERE `msgid` = :msgid LIMIT 1', $data);
			$result->query[] = mydb()->_query;

			//$ret .= print_o($resultPlant, '$resultPlant');
		}

		// Trigger Change to Firebase Function, Slow on localhost
		R::On('ibuy.activity.create', $data->msgid);
	}

	$result->data = $data;

	return $result;
}
?>