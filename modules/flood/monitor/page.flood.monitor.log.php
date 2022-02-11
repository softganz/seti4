<?php
function flood_monitor_log() {
	$station=post('s');
	$items=SG\getFirst($_REQUEST['items'],100);
	$isAdmin=user_access('administrator floods,operator floods');

	if ($action=post('action') && $isAdmin) {
		switch ($action) {
			case 'delete':
				$rs=mydb::select('SELECT * FROM %flood_sensor% WHERE `sid`=:id LIMIT 1',':id',post('id'));
				if ($rs->_num_rows) {
					// remove photo file
					if ($rs->sensorName=='camera' && $rs->value) unlink(cfg('folder.abs').'upload/sensor/'.$rs->value);
					mydb::query('DELETE FROM %flood_sensor% WHERE `sid`=:id LIMIT 1',':id',$rs->sid);
				}
				break;
			
			default:
				# code...
				break;
		}
	}
	$ret.='<div id="flood-log-info">';
	$ret.='<h3>Sensor log @'.date('Y-m-d H:i:s').'</h3>';
	$sensorList=array('level'=>'ระดับน้ำ','rain'=>'ปริมาณน้ำฝน','temperature'=>'อุณหภูมิ','relativeHumidity'=>'ความชื้นสัมพัทธ์','batteryVoltage'=>'แรงดันแบตเตอรี','camera'=>'CCTV');

	$ret.='<ul class="tabs">';
	foreach ($sensorList as $key => $value) {
		$ret.='<li><a class="sg-action" href="'.url('flood/monitor/log',array('s'=>$station,'sensor'=>$key,'d'=>post('d'))).'" data-rel="#flood-log-info">'.$value.'</a></li>';
	}
	$ret.='</ul>';
	$where=array();
	if (post('n')) $where=sg::add_condition($where,'`nodeAddress`=:nodeAddress ','nodeAddress',post('n'));
	if (post('e')) $where=sg::add_condition($where,'`endpointId`=:endpointId ','endpointId',post('e'));
	if (post('s')) $where=sg::add_condition($where,'`station`=:station ','station',post('s'));
	if (post('sensor')) $where=sg::add_condition($where,'`sensorName`=:sensorName ','sensorName',post('sensor'));
	if (post('d')) $where=sg::add_condition($where,'`timeStamp` BETWEEN :fromTimeStamp AND :toTimeStamp ','fromTimeStamp',sg_date(post('d'),'Y-m-d 00:00:00'), 'toTimeStamp',sg_date(post('d'),'Y-m-d 23:59:59'));

	$stmt='SELECT s.*, st.`title`, st.`mmsl`, st.`levelfactor`
					FROM %flood_sensor% s
						LEFT JOIN %flood_station% st USING(`station`)
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					 ORDER BY `sid` DESC LIMIT '.$items;
	$dbs=mydb::select($stmt,$where['value']);
	//$ret.=mydb()->_query;

	$tables = new Table();
	$tables->addClass('sensor--log');
	$tables->caption='Sensor data';
	$tables->thead=array('TimeStamp','Station','NodeAddress','EndpointId','SensorName', 'Value', 'Raw Data', 'reference level','');
	foreach ($dbs->items as $rs) {
		$compute='';
		$tables->rows[]=array($rs->timeStamp,
			$rs->title.'<br />('.$rs->station.')',
			$rs->nodeAddress,
			$rs->endpointId,
			$rs->sensorName,
			($rs->sensorName=='camera'?'<a class="sg-action sensor-photo" href="'.flood_model::sensor_photo($rs->station,$rs->value).'" data-rel="img" target="_blank" title="ภาพจากกล้อง '.$rs->value.'"><img src="'.flood_model::sensor_photo($rs->station,$rs->value).'" width="100" /></a>':$rs->value),
			$rs->sensorName=='camera'?'':$rs->rawdata,
			$rs->sensorName=='level'?$rs->mmsl:'',
			$isAdmin?'<a href="'.url('flood/monitor/log',array('action'=>'delete','id'=>$rs->sid)).'" class="sg-action icon-delete hover--menu" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน" data-removeparent="tr" data-rel="this">X</a>':'',
		);
	}

	$ret .= $tables->build();

	$ret.='</div>';
	return $ret;
}
?>