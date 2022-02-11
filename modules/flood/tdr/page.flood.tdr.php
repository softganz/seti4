<?php
/**
* flood_tdr_table
*
* @param Object $self
* @return String
*/
function flood_tdr($self) {
	$self->theme->title='TDR Monitoring System';
	$basin=post('basin');
	$sensorName=SG\getFirst(post('sensorName'),'voltage');
	$from=SG\getFirst(post('from'),date('d/m/Y',strtotime('-0 days')));
	$to=SG\getFirst(post('to'),date('d/m/Y'));
	$export=post('export');
	$rainsum=post('rainsum',0);
	$station='TDR01';

	$cfg['voltage']=array('name'=>'TDR','unit'=>'Volt','digit'=>3,'nameEN'=>'TDR','unitEN'=>'Volt');
	$cfg['level']=array('name'=>'ระดับน้ำ','unit'=>'ม.รทก.','digit'=>3,'nameEN'=>'Water Level','unitEN'=>'m.');
	$cfg['rain']=array('name'=>'ปริมาณน้ำฝน','unit'=>'มม.','digit'=>1,'nameEN'=>'Rain','unitEN'=>'mm.');
	$cfg['batteryVoltage']=array('name'=>'แรงดันแบตเตอรี','unit'=>'โวลท์','digit'=>1,'nameEN'=>'Battery Voltage','unitEN'=>'Volt');

	$tdrList=array('90:4'=>'1','A1:1'=>'2','A1:2'=>'3','A1:3'=>'4','A1:4'=>'5');


	$stmt='SELECT d.*
					FROM %flood_sensor% d
					WHERE `station`="TDR01"
					ORDER BY `sid` DESC
					LIMIT 100';
	$dbs=mydb::select($stmt);

	// Get midnight rain
	$stmt='SELECT d.*
					FROM %flood_sensor% d
					WHERE `nodeAddress`="00:13:A2:00:40:8C:A1:9A" AND `endpointId`=1
								AND `timeStamp`>=:today
					ORDER BY `sid` ASC
					LIMIT 1';
	$midnightRainRs=mydb::select($stmt,':today',date('Y-m-d 00:00:00'));

	foreach ($dbs->items as $rs) {
		$nodeId=substr($rs->nodeAddress,-2).':'.$rs->endpointId;
		if ($data[$nodeId]) continue;
		$data[$nodeId]=$rs;
		$data[$nodeId]->tdrName=$tdrList[$nodeId]?'TDR'.$tdrList[$nodeId]:$nodeId;
	}

	//$ret.=print_o($midnightRainRs,'$midnightRainRs').print_o($data,'$data');

	$rainFromMidnight='-';
	if ($midnightRainRs->_num_rows && $data['9A:1']->value) {
		$rainFromMidnight=$data['9A:1']->value-$midnightRainRs->value;
	}

	$ret.='<div class="tdr--show">';
	$ret.='<img class="tdrphoto" src="'._URL.'themes/default/tdrphoto.png" />';
	$ret.='<span class="tdr--item -date"><h3>วันที่</h3><span class="value">'.date('d-m-Y').'</span></span>';
	$ret.='<span class="tdr--item -time"><h3>เวลา</h3><span class="value">'.date('H:i:s').'</span></span>';
	foreach ($tdrList as $key => $value) {
		$ret.='<span class="tdr--item -tdr -tdr'.$value.'"><h3>TDR'.$value.'</h3><span class="value">'.number_format($data[$key]->value,3).' %</span><span class="time">'.sg_date($data[$key]->timeStamp,'d-m-ปปปป H:i:s').'</span></span>';
	}
	$ret.='<span class="tdr--item -rain"><h3>ปริมาณน้ำฝนตั้งแต่เที่ยงคืน</h3><span class="value">'.$rainFromMidnight.'</span> มม.</span>';
	$ret.='<span class="tdr--item -battery"><h3>แบตเตอรี่</h3><span class="value">???</span> โวลท์</span>';
	$ret.='<span class="tdr--item -water"><h3>ระดับน้ำ</h3><span class="value">'.$data['90:3']->value.'</span> ซม.</span>';
	$ret.='</div>';

	$tables = new Table();
	$tables->thead=array('Sensor','timeStamp','Value','sensorName');
	foreach ($data as $rs) {
		$tables->rows[$rs->tdrName]=array($rs->tdrName,$rs->timeStamp,$rs->value,$rs->sensorName);
	}
	asort($tables->rows);
	$ret.=$tables->build();

	//$ret.=print_o($data,'$data');

	//$ret.=print_o(post());
	//$ret.=print_o($dbs,'$dbs');
	$ret.='<style type="text/css">
		body#flood #main {margin:0;}
		.tdr--show {position:relative; min-width:600px; max-width:1067px; border:1px #ccc solid; margin:0 0 20px 0;}
		.tdrphoto {width:100%;}
		.tdr--item {position: absolute; z-index:1; left: 45%;}
		.tdr--item h3 {display:inline-block; margin:0 4px 0; font-weight: normal; color: #333;}
		.tdr--item .time {display: none;}
		.tdr--item.-tdr h3 {color:#eee;}
		.tdr--item.-tdr {left: 40%;}
		.tdr--item.-tdr1 {top:55.0%;}
		.tdr--item.-tdr2 {top:62.0%;}
		.tdr--item.-tdr3 {top:69.0%;}
		.tdr--item.-tdr4 {top:76.0%;}
		.tdr--item.-tdr5 {top:83.0%;}
		.tdr--item.-rain {top:16%; left: 17%;}
		.tdr--item.-date {top:5.5%; left: 75%;}
		.tdr--item.-time {top:15%; left: 75%;}
		.tdr--item.-battery {top:16%; left: 50%;}
		.tdr--item.-water {top:66.0%; left: 75%;}
		.tdr--item .value {background:#D2E1FF; border:1px #98B0D5 solid; border-radius:4px; padding: 2px 6px; box-shadow: 2px 2px 5px #aaa;}
		</style>';

		$ret.='<script type="text/javascript"><!--
		$(document).ready(function() {
			function pollServerForTDR() {
				$.get("'.url("flood/tdr").'",function(html) {
					$("#main").html(html);
					setTimeout(pollServerForTDR, 5*60*1000);
				});
			};
			setTimeout(pollServerForTDR, 5*60*1000);
		});

		--></script>';
	return $ret;
}

function cleanData(&$str)
  {
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
  }

?>