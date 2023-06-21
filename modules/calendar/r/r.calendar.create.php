<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_calendar_create($data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$data->id = empty($data->id) ? NULL : intval($data->id);

	$isUpdateData = !empty($data->id);
	$isAddData = empty($data->id);

	$result = (Object) [
		'_invalid' => NULL,
		'module_add' => NULL,
		'module_edit' => NULL,
		'data' => $data,
		'_query' => NULL,
	];

	$data->owner = i()->uid;

	$data->from_date = sg_date($data->from_date, 'Y-m-d');
	$data->to_date = sg_date($data->to_date, 'Y-m-d');
	if ($data->from_date > $data->to_date) $result->_invalid[] = 'วันที่เริ่มต้น หรือ วันที่สิ้นสุดผิดพลาด';

	$data->from_time = \SG\getFirst($data->from_time);
	$data->to_time = \SG\getFirst($data->to_time);

	$data->tpid = \SG\getFirst($data->tpid);
	$data->category = \SG\getFirst($data->category);
	$data->reminder = \SG\getFirst($data->reminder,'no');
	$data->repeat = \SG\getFirst($data->repeat,'no');

	$address = \SG\explode_address($data->location, $data->areacode);
	$data->changwat = \SG\getFirst($address['changwatCode'],' ');
	$data->ampur = \SG\getFirst($address['ampurCode'],' ');
	$data->tambon = \SG\getFirst($address['tambonCode'],' ');
	$data->village = \SG\getFirst($address['villageCode'],' ');

	$data->ip = ip2long(GetEnv('REMOTE_ADDR'));
	$data->created_date = date('Y-m-d H:i:s');

	$calendarOptions = (Object) [
		'color' => $data->color,
	];

	$data->options = sg_json_encode($calendarOptions);

	if ($debug) debugMsg($address,'$address');

	$stmt='INSERT INTO %calendar%
		(
		  `id`, `tpid`, `owner`, `privacy`, `category`, `title`, `location`, `latlng`
		, `village`, `tambon`, `ampur`, `changwat`
		, `from_date`, `from_time`, `to_date`, `to_time`
		, `detail`, `reminder`, `repeat`
		, `options`
		, `ip`, `created_date`
		) VALUES (
		  :id, :tpid, :owner, :privacy, :category, :title, :location, :latlng
		, :village, :tambon, :ampur, :changwat
		, :from_date, :from_time, :to_date, :to_time
		, :detail, :reminder, :repeat
		, :options
		, :ip, :created_date
		)
		ON DUPLICATE KEY UPDATE
		  `privacy` = :privacy , `category` = :category , `title` = :title
		, `location` = :location , `latlng` = :latlng
		, `village` = :village , `tambon` = :tambon
		, `ampur` = :ampur , `changwat` = :changwat
		, `from_date` = :from_date , `from_time` = :from_time
		, `to_date` = :to_date , `to_time` = :to_time
		, `detail` = :detail
		, `options` = :options
		';

	mydb::query($stmt,$data);

	$result->_query[] = mydb()->_query;

	if (empty($data->id)) $data->id=mydb()->insert_id;


	list($year,$month)=explode('-',$data->from_date);
	$month=sprintf('%02d',$month);

	if ($data->module && $isAddData) {
		$result->module_add = R::On($data->module.'.calendar.add', $data, $para);
	} else if ($data->module && $isUpdateData) {
		$result->module_edit = R::On($data->module.'.calendar.edit', $data, $para);
	}

	$result->data = $data;
	if ($debug) debugMsg($result, '$result');
	return $result;
}
?>