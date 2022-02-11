<?php
/**
* Flood Monitor : rain
*
* @param Object $self
* @return String
*/
function flood_monitor_data($self) {
	$self->theme->title.=' : Monitoring Data';
	$post=(object)post('data');
	//$ret.=print_o($post,'$post');
	// $stations=mydb::select('SELECT * FROM %flood_station%')->items;

	$form = new Form([
		'variable' => 'data',
		'method' => 'get',
		'action' => url('flood/monitor/data'),
		'children' => [
			'region' => [
				'type' => 'select',
				'label' => 'Region',
				'value' => $post->region,
			],
			'station' => [
				'type' => 'select',
				'label' => 'Station',
				'value' => $post->station,
				'options' => mydb::select('SELECT `station`, CONCAT(`title`," (",`station`,")") `title` FROM %flood_station%; -- {key: "station", value: "title"}')->items,
			],
			'sensor' => [
				'type' => 'select',
				'label' => 'Sensor :',
				'options' => ['level'=>'level','rain'=>'rain','temperature'=>'temperature','relativeHumidity'=>'relativeHumidity','batteryVoltage'=>'batteryVoltage'],
				'value' => $post->sensor,
			],
			'observ' => [
				'type' => 'select',
				'label' => 'Observ',
				'value' => $post->observ,
				'options' => array('60'=>'Observ. : Every 60 Min'),
			],
			'date' => [
				'type' => 'text',
				'label' => 'Date',
				'class' => 'sg-datepicker',
				'value' => $post->date,
			],
			'save' => [
				'type' => 'button',
				'items' => [
					'save' => ['type' => 'submit','value' => '<i class="icon -material">search</i><span>ดู</span>', 'class' => '-primary'],
					//'excel' => ['type' => 'submit', 'value' => 'Export to Excel'],
				],
			],
		],
	]);

	$ret .= $form->build();

	$ret.='<style>.form-item {display:inline-block;} .form-item label {display:inline-block;}</style>';

	if ($post->station) mydb::where('s.station=:station',':station',$post->station);
	if ($post->sensor) mydb::where('s.sensorName=:sensor',':sensor',$post->sensor);
	if ($post->date) mydb::where('DATE_FORMAT(s.timeRec,"%Y-%m-%d")=:date',':date',sg_date($post->date,'Y-m-d'));

	$stmt='SELECT s.*, st.`title` FROM %flood_sensor% s
		LEFT JOIN %flood_station% st USING(`station`)
		%WHERE%
		ORDER BY `sid`
		DESC LIMIT 1000';

	$dbs=mydb::select($stmt,$where['value']);

	$tables = new Table();
	$tables->thead=array('วันที่','สถานี','amt value'=>'Data','amt rawdata'=>'Raw Data');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = [
			sg_date($rs->timeRec,'d-m-Y H:i'),
			SG\getFirst($rs->title,$rs->nodeAddress).($rs->station?' ('.$rs->station.')':''),
			$rs->value,
			$rs->rawdata,
		];
	}

	$ret .= $tables->build();

	//$ret.=print_o($dbs,'$dbs');
	if (post('api')) die(json_encode($dbs->items));

	return $ret;
}
?>