<?php
/**
* flood_monitor_table
*
* @param Object $self
* @return String
*/
function flood_monitor_table($self) {
	$basin=post('basin');
	$sensorName=SG\getFirst(post('sensorName'),'level');
	$from=SG\getFirst(post('from'),date('d/m/Y',strtotime('-2 days')));
	$to=SG\getFirst(post('to'),date('d/m/Y'));
	$export=post('export');
	$rainsum=post('rainsum',0);

	$cfg['level']=array('name'=>'ระดับน้ำ','unit'=>'ม.รทก.','digit'=>3,'nameEN'=>'Water Level','unitEN'=>'m.');
	$cfg['rain']=array('name'=>'ปริมาณน้ำฝน','unit'=>'มม.','digit'=>1,'nameEN'=>'Rain','unitEN'=>'mm.');
	$cfg['temperature']=array('name'=>'อุณหภูมิ','unit'=>'องศาเซลเซียส','digit'=>1,'nameEN'=>'Temperature','unitEN'=>'Celsius');
	$cfg['relativeHumidity']=array('name'=>'ความชื้นสัมพัทธ์','unit'=>'%','digit'=>1,'nameEN'=>'Relative Humidity','unitEN'=>'%');
	$cfg['batteryVoltage']=array('name'=>'แรงดันแบตเตอรี','unit'=>'โวลท์','digit'=>1,'nameEN'=>'Battery Voltage','unitEN'=>'Volt');


	R::View('flood.monitor.toolbar',$self);

	$ret.='<div class="flood__realtime">';
	$ret.='<h2>รวมข้อมูลตาราง-กราฟ</h2>';
	//$options=array('level'=>'ระดับน้ำ','temperature'=>'อุณหภูมิ','relativeHumidity'=>'ความชื้น','rain'=>'ปริมาณน้ำฝน','batteryVoltage'=>'แรงดันแบตเตอรี');
	$ret.='<div class="toolbar main">';
	$ret.='<form method="get"><input type="hidden" name="basin" value="'.$basin.'" /><span style="font-weight:bold;background:#f60;display:inline-block;padding:2px;"><label for="edit-sensorName">ชนิดข้อมูล</label> <select id="edit-sensorName" name="sensorName" class="form-select"><option value="">เลือกชนิดข้อมูล</option>';
	foreach ($cfg as $key => $value) {
		$ret.='<option value="'.$key.'"'.($key==$sensorName?' selected="selected"':'').'>'.$value['name'].'</option>';
	}
	$ret.='</select></span> <label>ช่วงวันที่</label> <input type="text" name="from" class="form-text sg-datepicker" size="10" value="'.$from.'" /> - <input type="text" name="to" class="form-text sg-datepicker" size="10" value="'.$to.'" /> <button>ดู</button> <input class="button" type="submit" name="export" value="Export to Excel" /><input class="button" type="submit" name="print" value="Print to PDF" onclick="window.print();return false;" /></form>';
	$ret.='</div>';

	$stations=mydb::select('SELECT * FROM %flood_station% WHERE `basin`=:basin ORDER BY `sorder` ASC',':basin',$basin)->items;
	$stmt='SELECT s.*, st.`title`
						FROM %flood_sensor% s
							LEFT JOIN %flood_station% st USING(`station`)
						WHERE `station` IN (SELECT `station` FROM %flood_station% WHERE `basin`=:basin) AND `sensorName`=:sensorName AND `timeRec` BETWEEN :from AND :to
						ORDER BY `timeRec` ASC';
	$dbs=mydb::select($stmt,':basin',$basin, ':sensorName',$sensorName, ':from',sg_date($from,'Y-m-d 00:00:00'), ':to',sg_date($to,'Y-m-d 23:59:59'));

	$graph=NULL;
	$graph->title=$cfg[$sensorName]['name'].' ('.$cfg[$sensorName]['unit'].')';
	$graphHeader[]='Date';
	foreach ($stations as $item) $graphHeader[]=$item->title;
	$graph->items[]=$graphHeader;

	$tables = new Table();
	$tables->thead['date']='วันที่';
	$tables->thead['date time']='เวลา';
	foreach ($stations as $item) {
		if ($sensorName=='level') $unit=$item->levelref;
		else if ($sensorName=='temperature') $unit='องศาเซลเซียส';
		else if ($sensorName=='relativeHumidity') $unit='%';
		else if ($sensorName=='rain') $unit='มม.';
		else if ($sensorName=='batteryVoltage') $unit='โวลท์';
		$tables->thead['amt '.$item->station]=$item->title.'<br />('.$unit.')';
	}
	foreach ($dbs->items as $rs) {
		$value=$rs->value;
		if ($export || $sensorName=="rain") {
			$time=sg_date($rs->timeRec,'d-m-ปปปป H:00');
		} else {
			$time=sg_date($rs->timeRec,'d-m-ปปปป H:i');
		}
		if (!$tables->rows[$time]) {
			list($date,$hr)=explode(' ', $time);
			$tables->rows[$time]['Date']=$date;
			$tables->rows[$time]['Time']=$hr;
			foreach ($stations as $item) $tables->rows[$time][$item->station]='';
		}
		$tables->rows[$time][$rs->station]=number_format($value,$cfg[$sensorName]['digit']);
	}

	foreach ($tables->rows as $key=>$item) {
		unset($graphItem);
		$graphItem[]=$key;
		$i=1;
		foreach ($stations as $s) {
			$sid=$s->station;
			//if ($sensorName=='rain') {
			//	$tables->rows[$key][$sid]=$graphItem[]=$item[$sid]!=''?floatval($item[$sid])-$prevItem[$i]:(is_null($prevItem[$i])?0:$prevItem[$i]);
			//} else {
				$graphItem[]=$item[$sid]!=''?floatval(sg_strip_money($item[$sid])):(is_null(sg_strip_money($prevItem[$i]))?0:$prevItem[$i]);
			//}
			++$i;
		}
		$graph->items[]=$graphItem;
		$prevItem=$graphItem;
	}
	if (debug('method')) {
		$ret.=print_o($tables,'$tables');
		$ret.=print_o($graph,'$graph');
	}
	if ($sensorName=='rain' && $rainsum==0) {
		$prev=$graph->items[1];
		foreach ($graph->items as $key => $value) {
			if ($key==0) continue;
			//$prev=$graph->items[$key-1];
			$cur=$value;
			$gvalue=array();
			foreach ($value as $k2 => $v2) {
				if ($k2==0) $gvalue[$k2]=$v2;
				else $gvalue[$k2]=round($value[$k2]-$prev[$k2],2);
			}
			$graph->items[$key]=$gvalue;

			list($date,$time)=explode(' ',$value[0]);
			$row=array($date,$time);
			foreach ($value as $k2 => $v2) {
				if ($k2==0) ;
				else $row[]=round($value[$k2]-$prev[$k2],2);
			}
			$graph->items[$key]=$gvalue;
			/*
			$graph->items[$key]=array(
														$value[0],
														$value[1]-$prev[1],
														$value[2]-$prev[2],
														$value[3]-$prev[3]
														);
			$row=array(
									$value[0],
									$tables->rows[$value[0]]['Time'],
									number_format($value[1]-$prev[1],1),
									number_format($value[2]-$prev[2],1),
									number_format($value[3]-$prev[3],1),
									);
			*/
			$tables->rows[$value[0]]=$row;
			$prev=$cur;
		}
	}
	foreach ($graph->items[1] as $key => $value) if (is_null($value)) $graph->items[1][$key]=0;

	if (debug('method')) {
		$ret.=print_o($tables,'$tables');
		$ret.=print_o($graph,'$graph');
	}

	if ($export) {
		// file name for download
		$filename = "sensor_data_".$sensorName.'_utf8_'.date('Y-m-d_H-i').".xls";

		header("Content-Disposition: attachment; filename=\"$filename\"");
		header("Content-Type: application/vnd.ms-excel");
		//header('<meta http-equiv="Content-Type" content="text/html; charset='.cfg('client.characterset').'" />');
		//header('<meta http-equiv="Content-Language" content="th" />');

		//echo "ศูนย์ป้องกันและบรรเทาสาธารณภัย เขต 12 สงขลา"."\n";
		echo "Disaster Prevention and Mitigation Regional Center 12 Songkhla"."\n";
		echo $cfg[$sensorName]['nameEN'].'('.$cfg[$sensorName]['unitEN'].')'."\n";
		echo "Date ".sg_date($from,'d-m-ปปปป').' - '.sg_date($to,'d-m-ปปปป')."\n";

		$flag = false;
		echo "Date\tTime\t";
		foreach ($stations as $item) {
			echo $item->station.'-'.$item->title."\t";
		}
		echo "\n";
		foreach($tables->rows as $row) {
			if(!$flag) {
				// display field/column names as first row
							//echo implode("\t", array_keys($row)) . "\n";
				$flag = true;
			}
			array_walk($row, 'cleanData');
			echo implode("\t", array_values($row)) . "\n";
		}
		die;
	}

	if ($dbs->_num_rows) {
		$ret.='<div id="chart_div" class="flood__box flood__box--graph">กราฟ</div>';
		$ret.='<div class="flood__box flood__box--data">';

		$ret .= $tables->build();

		$ret.='</div>';
	} else {
		$ret.='<p class="notify">ไม่มีข้อมูลตามเงื่อนไขที่กำหนด</p>';
	}
	$ret.='</div><!--flood__main-->';
// options ={ hAxis: {title: "Years" , direction:-1, slantedText:true, slantedTextAngle:90 }}

	$chartTypes=array('bar'=>'BarChart','pie'=>'PieChart','col'=>'ColumnChart','line'=>'LineChart');
	$graphType='line';
	if ($sensorName=="rain") $graphType='col';
	if ($dbs->_num_rows) {
		head('jspi','<script type="text/javascript" src="https://www.google.com/jsapi"></script>');

		$ret.='<script type="text/javascript">
			google.load("visualization", "1", {packages:["corechart"]});
			google.setOnLoadCallback(drawChart);
			var graph='.json_encode($graph).'
			function drawChart() {
				if (graph) {
					var data = google.visualization.arrayToDataTable(graph.items);
					var options = {
								title: graph.title,
								legend: { position: "top", maxLines: 2 },
								hAxis: {
												title: "Date",
												direction:1,
												slantedText:true,
												slantedTextAngle:90,
												textStyle: {fontSize:7,}
												},
								vAxis: {
												viewWindow: {
														//min:auto,
													},
												},
								chartArea: {
									width: "90%",
								}
							};
					var chart = new google.visualization.'.$chartTypes[$graphType].'(document.getElementById("chart_div"));
					chart.draw(data, options);
				}
			}
			</script>';
	}
	//$ret.=print_o(post());
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}

function cleanData(&$str)
  {
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
  }

?>