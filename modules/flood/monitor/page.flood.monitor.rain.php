<?php
/**
* Flood Monitor : rain
*
* @param Object $self
* @return String
*/
function flood_monitor_rain($self) {
	$self->theme->title.=' : ปริมาณน้ำฝน';

	$ret.='<iframe src="http://www.songkhla.tmd.go.th/RF/Monitor/" scrolling="yes" style="margin:0;overflow:auto;" frameborder="0" height="600" width="100%"></iframe>';

	$stmt='SELECT s.*, st.`title` FROM %flood_sensor% s LEFT JOIN %flood_station% st USING(`station`) WHERE `sensorName`="rain" ORDER BY `sid` DESC LIMIT 1000';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('วันที่','สถานี','amt'=>'ปริมาณน้ำฝน(สะสม)');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(sg_date($rs->timeRec,'d-m-Y H:i'),$rs->title.' ('.$rs->station.')',$rs->value);
	}

	$ret .= $tables->build();

	return $ret;
}
?>