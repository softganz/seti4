<?php
/**
* flood_tdr_table
*
* @param Object $self
* @return String
*/
function flood_tdr_table($self) {
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

	$ret.='<div class="flood__realtime">';
	$ret.='<h2>รวมข้อมูลตาราง-กราฟ</h2>';
	//$options=array('level'=>'ระดับน้ำ','temperature'=>'อุณหภูมิ','relativeHumidity'=>'ความชื้น','rain'=>'ปริมาณน้ำฝน','batteryVoltage'=>'แรงดันแบตเตอรี');
	$ret.='<div class="toolbar main">';
	$ret.='<form method="get"><input type="hidden" name="basin" value="'.$basin.'" /><span style="font-weight:bold;background:#f60;display:inline-block;padding:2px;"><label for="edit-sensorName">ชนิดข้อมูล</label> <select id="edit-sensorName" name="sensorName" class="form-select"><option value="">เลือกชนิดข้อมูล</option>';
	foreach ($cfg as $key => $value) {
		$ret.='<option value="'.$key.'"'.($key==$sensorName?' selected="selected"':'').'>'.$value['name'].'</option>';
	}
	$ret.='</select></span> <label>ช่วงวันที่</label> <input type="text" name="from" class="form-text sg-datepicker" size="10" value="'.$from.'" /> - <input type="text" name="to" class="form-text sg-datepicker" size="10" value="'.$to.'" /> <button class="btn -primary" type="submit"><i class="icon -viewdoc -white"></i><span>ดู</span></button> '
			.'<button class="btn" type="submit" name="export" value="Export to Excel"><i class="icon -download"></i><span>Export to Excel</span></button> '
			.'<button class="btn" type="submit" name="print" value="Print to PDF" onclick="window.print();return false;"><i class="icon -print"></i><span>Print to PDF</span></button>'
			.'</form>';
	$ret.='</div>';

	$stmt='SELECT s.*, st.`title`
						FROM %flood_sensor% s
							LEFT JOIN %flood_station% st USING(`station`)
						WHERE `station` IN (:station) AND `sensorName`=:sensorName AND `timeRec` BETWEEN :from AND :to
						ORDER BY `timeStamp` ASC, `nodeAddress` DESC, `endpointId` ASC';
	$dbs=mydb::select($stmt,':station',$station, ':sensorName',$sensorName, ':from',sg_date($from,'Y-m-d 00:00:00'), ':to',sg_date($to,'Y-m-d 23:59:59'));

	//$ret.=print_o($dbs,'$dbs');

	$graph=NULL;
	$graph->title=$cfg[$sensorName]['name'].' ('.$cfg[$sensorName]['unit'].')';
	$graphHeader[]='Date';
	if ($sensorName=='voltage') {
		foreach ($tdrList as $item) $graphHeader[]='TDR'.$item;
	} else {
		$graphHeader[]=$sensorName;
	}
	$graph->items[]=$graphHeader;

	$tables = new Table();
	$tables->thead['date']='วันที่';
	$tables->thead['date time']='เวลา';
	if ($sensorName=='voltage') {
		$tables->thead['amt TDR1']='TDR1';
		$tables->thead['amt TDR2']='TDR2';
		$tables->thead['amt TDR3']='TDR3';
		$tables->thead['amt TDR4']='TDR4';
		$tables->thead['amt TDR5']='TDR5';
		$tables->thead['amt FLOW']='Flow Rate';
	} else {
		if ($sensorName=='level') $unit=$item->levelref;
		else if ($sensorName=='temperature') $unit='องศาเซลเซียส';
		else if ($sensorName=='relativeHumidity') $unit='%';
		else if ($sensorName=='rain') $unit='มม.';
		else if ($sensorName=='batteryVoltage') $unit='โวลท์';
		$tables->thead['amt '.$item->station]=$sensorName.'<br />('.$unit.')';
	}
	foreach ($dbs->items as $rs) {
		$value=$rs->value;
		if ($sensorName=="rain") {
			$time=sg_date($rs->timeStamp,'d-m-ปปปป H:i:s');
		} else {
			$time=sg_date($rs->timeStamp,'d-m-ปปปป H:i:s');
		}
		if (!$tables->rows[$time]) {
			list($date,$hr)=explode(' ', $time);
			$tables->rows[$time]['Date']=$date;
			$tables->rows[$time]['Time']=$hr;
			if ($sensorName=='voltage') {
				foreach ($tdrList as $key => $item) {
					$tables->rows[$time][$key]='';
				}
			} else {
				$tables->rows[$time][substr($rs->nodeAddress,-2).':'.$rs->endpointId]='';
				//foreach ($stations as $item) $tables->rows[$time][$item->nodeAddress.$item->endpointId]='';
			}
		}
		$tables->rows[$time][substr($rs->nodeAddress,-2).':'.$rs->endpointId]=number_format($value,$cfg[$sensorName]['digit']);//.'<br />'.substr($rs->nodeAddress,-2).'['.$rs->endpointId.']';
	}

	foreach ($tables->rows as $key=>$item) {
		unset($graphItem);
		$graphItem[]=$key;
		$i=1;
		if ($sensorName=='voltage') {
			foreach ($tdrList as $tdrKey=>$tdrNo) {
				$sid=$tdrKey;
				//if ($sensorName=='rain') {
				//	$tables->rows[$key][$sid]=$graphItem[]=$item[$sid]!=''?floatval($item[$sid])-$prevItem[$i]:(is_null($prevItem[$i])?0:$prevItem[$i]);
				//} else {
					$graphItem[]=$item[$sid]!=''?floatval(sg_strip_money($item[$sid])):(is_null(sg_strip_money($prevItem[$i]))?0:$prevItem[$i]);
				//}
				++$i;
			}
		} else {
			$sid='9A:1';
			$graphItem[]=$item[$sid]!=''?floatval(sg_strip_money($item[$sid])):(is_null(sg_strip_money($prevItem[$i]))?0:$prevItem[$i]);
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
			$graph->items[$key]=array(
				$value[0],
				$value[1]-$prev[1],
			);
			$row=array(
				$value[0],
				$tables->rows[$value[0]]['Time'],
				number_format($value[1]-$prev[1],1),
			);
			$tables->rows[$value[0]]=$row;
			$prev=$cur;
		}
	}


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

	$ret.='<style type="text/css">
	body#flood #main {margin:0;}
	</style>';
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