<?php
/**
 * sensor class for sensor management
 *
 * @package sensor
 * @version 0.00.0
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2013-09-06
 * @modify 2013-09-06
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

$GLOBALS['nodes']=array(
	'NW'=>array(
		'ลุ่มน้ำนาทวี',
		array('NW10','NW30','NW41'),
		array('NW10'=>'บ้านคลองลึก','NW30'=>'ตลาดนาทวี','NW41'=>'ท่าประดู่'),
	),
	'RP'=>array(
		'ลุ่มน้ำรัตภูมิ',
		array('RP20','RP30','RP21'),
		array('RP20'=>'ฝายชะมวง','RP30'=>'วัดเขาตกน้ำ','RP21'=>'สะพานเขาพระ'),
	),
	'HY'=>array(
		'ลุ่มน้ำคลองอู่ตะเภา',
		array('HY30','HY40'),
		array('HY30'=>'วัดม่วงก็อง','HY40'=>'บางศาลา'),
	),
);

class sensor_report extends module {

	public function __construct() {
		parent::__construct();
	}

	public function _home() {
		$ret.='Sensor report';
		return $ret;
	}

	function _level($node) {
		global $nodes;
		$this->theme->title='รายงานระดับน้ำ '.$nodes[$node][0];

		$date=SG\getFirst(post('d'),date('Y-m-d'));
		$station=$nodes[$node][1];
		$stmt='SELECT * FROM %flood_station% WHERE `station` IN (:station)';
		$stationDbs=mydb::select($stmt, ':station','SET-STRING:'.implode(',',$station));
		foreach ($stationDbs->items as $rs) {
			$stations[$rs->station]=$rs;
		}

		$stmt='SELECT s.*, sid.`station`, st.`mmsl`,
				DATE_FORMAT(`timeStamp`,"%Y-%m-%d") `date`,
				DATE_FORMAT(`timeStamp`,"%H:%i") `time`
			FROM %flood_sensor% s
				LEFT JOIN %flood_sensorid% sid USING(`nodeAddress`)
				LEFT JOIN %flood_station% st ON st.`station`=sid.`station`
			WHERE `sensorName`="level" AND st.`station` IN (:station) AND DATE_FORMAT(`timeStamp`,"%Y-%m-%d")=:date
			ORDER BY `timeStamp` ASC
			';
		$dbs=mydb::select($stmt, ':date', $date, ':station','SET-STRING:'.implode(',',$station));

		$tables = new Table();
		$tables->addClass('sensor-zone-level');
		$thead='<thead><tr><th colspan="7"><form method="get">วันที่ <input type="text" name="d" class="form-text" size="10" maxlength="10" value="'.$date.'" /></form></th></tr>';
		$thead.='<tr><th colspan="7">'.$nodes[$node][0].'</th></tr>';
		$thead.='<tr>';
		$thead.='<th rowspan="2">Time</th>';
		foreach ($station as $name) {
			$thead.='<th colspan="2">'.$nodes[$node][2][$name].' ('.$name.')</th>';
		}
		$thead.='</tr>';
		$thead.='<tr>';
		foreach ($nodes[$node][1] as $n) {
			$thead.='<th>Level ('.$stations[$n]->levelref.')</th><th>Flow (ลบ.ม./วินาที)</th>';
		}
		$thead.='</tr>';

		$thead.='</thead>';

		$tables->thead=$thead;

		for ($i=0; $i<24*60; $i+=15) {
			$time=sprintf('%02d',floor($i/60)).':'.sprintf('%02d',$i%60);
			$tables->rows[$i]['time']=$time;//.'['.$i.']';
			foreach ($station as $value) {
				$tables->rows[$i][$value.'-level']='-';
				$tables->rows[$i][$value.'-flow']='-';
			}
		}

		foreach ($dbs->items as $rs) {
			list($h,$m)=explode(':',$rs->time);
			$min=floor(($h*60+$m)/15)*15;
			$time=floor($min/15);
			//			$tables->rows[$time]['time']=$time.'('.$min.')';
			$level=$rs->mmsl ? number_format($rs->mmsl-$rs->value/1000+$rs->levelfactor,3):'('.number_format($rs->value/1000,3).')';

			$debugStr=$min.' '.$time.' '.$rs->station.' : '.$rs->time.' - ';
			$tables->rows[$min][$rs->station.'-level']=$level;
		}

		$ret .= $tables->build();

		$ret.='<script type="text/javascript">

		</script>';

		//		$ret.=print_o($dbs,'$dbs');

		return $ret;
	}

	function _graph($node) {
		global $nodes;
		$this->theme->title='รายงานระดับน้ำ '.$nodes[$node][0];

		$dateFrom=SG\getFirst(post('df'),date('Y-m-d',date('U')-6*24*60*60));
		$dateTo=SG\getFirst(post('dt'),date('Y-m-d'));
		$station=$nodes[$node][1];
		$stmt='SELECT s.*, sid.`station`, st.`mmsl`,
				DATE_FORMAT(`timeStamp`,"%Y-%m-%d") `date`,
				DATE_FORMAT(`timeStamp`,"%H:%i") `time`
			FROM %flood_sensor% s
				LEFT JOIN %flood_sensorid% sid USING(`nodeAddress`)
				LEFT JOIN %flood_station% st USING(`station`)
			WHERE `sensorName`="level" AND `station` IN (:station) AND DATE_FORMAT(`timeStamp`,"%Y-%m-%d") BETWEEN :dateFrom AND :dateTo
			ORDER BY `timeStamp` ASC
			';
		$dbs=mydb::select($stmt, ':dateFrom', $dateFrom, ':dateTo',$dateTo, ':station','SET-STRING:'.implode(',',$station));

		head('jspi','<script type="text/javascript" src="https://www.google.com/jsapi"></script>');

		$ret.='<form action="" method="get">วันที่ <input type="text" name="df" class="form-text" size="10" maxlength="10" value="'.$dateFrom.'" /> - <input type="text" name="dt" class="form-text" size="10" maxlength="10" value="'.$dateTo.'" /> <input type="submit" value="Go" /></form>';
		$ret.='<div id="chart_div" style="width: 100%; height: 600px;"></div>';

		$tables->class='item sensor-zone-level';
		$thead='<thead><tr><th colspan="7"><form action="" method="get">วันที่ <input type="text" name="d" class="form-text" size="10" maxlength="10" value="'.$date.'" /></form></th></tr>';
		$thead.='<tr><th colspan="7">'.$nodes[$node][0].'</th></tr>';
		$thead.='<tr>';
		$thead.='<th rowspan="2">Time</th>';
		foreach ($station as $name) {
			$thead.='<th colspan="2">'.$nodes[$node][2][$name].' ('.$name.')</th>';
		}
		$thead.='</tr>';
		$thead.='<tr>';
		foreach ($nodes[$node][1] as $n) {
			$thead.='<th>Level (ม.รทก.)</th><th>Flow (ลบ.ม./วินาที)</th>';
		}
		$thead.='</tr>';

		$thead.='</thead>';

		$tables->thead=$thead;

		$date=$dateFrom;
		do {
			for ($i=0; $i<24*60; $i+=15) {
				$time=sprintf('%02d',floor($i/60)).':'.sprintf('%02d',$i%60);
				$tables->rows[$date.' '.$i]['time']=$date.' '.$time;//.'['.$i.']';
				foreach ($station as $value) {
					$tables->rows[$date.' '.$i][$value.'-level']=0;
					$tables->rows[$date.' '.$i][$value.'-flow']='-';
				}
			}

			list($y,$m,$d)=explode('-', $date);
			$y=intval($y);
			$m=intval($m);
			$d=intval($d);
			$date=date('Y-m-d',mktime(0,0,0,$m,$d+1,$y));

		} while ($date<=$dateTo);

		$graph=NULL;
		$graph->title='ระดับน้ำ (ม.รทก.)';
		$graph->items[]=array_merge(array('Date'),array_values($nodes[$node][2]));

		foreach ($dbs->items as $rs) {
			list($h,$m)=explode(':',$rs->time);
			$min=floor(($h*60+$m)/15)*15;
			$time=floor($min/15);
			//			$tables->rows[$time]['time']=$time.'('.$min.')';
			$level=$rs->mmsl ? $rs->mmsl-$rs->value/1000+$rs->levelfactor:$rs->value/1000;

			$debugStr=$min.' '.$time.' '.$rs->station.' : '.$rs->time.' - ';
			$tables->rows[sg_date($rs->timeStamp,'Y-m-d').' '.$min][$rs->station.'-level']=$level;
		}
		foreach ($tables->rows as $rs) {
			unset($row);
			$row[]=$rs['time'];
			foreach ($station as $n) {
				$row[]=$rs[$n.'-level'];
			}
			$graph->items[]=$row;
		}
	
		$ret.='<script type="text/javascript">
		google.load("visualization", "1", {packages:["corechart"]});
		google.setOnLoadCallback(drawChart);
		var graph='.json_encode($graph).'
		function drawChart() {
			if (graph) {
				var data = google.visualization.arrayToDataTable(graph.items);
				var options = {title: graph.title,};
				var chart = new google.visualization.LineChart(document.getElementById("chart_div"));
				chart.draw(data, options);
			}
		}
		</script>';

		//		$ret.=print_o($dbs,'$dbs');

		return $ret;
	}

	function _log() {
		$getNodeAddress = post('n');
		$getEndPointId = post('e');
		$getStation = post('s');
		$getSensorName = post('sn');
		$getLog = post('log');

		$items = SG\getFirst(post('items'),100);

		$ret.='<h3>Sensor log @'.date('Y-m-d H:i:s').'</h3>';

		$stmt = 'SELECT
			`station`
			, `title`
			, `nodeAddress`
			, `endpointId`
			, `sensorName`
			, COUNT(*) `amt`
			FROM %flood_sensor%
				LEFT JOIN %flood_sensorid% sid USING(`nodeAddress`)
				LEFT JOIN %flood_station% st USING(`station`)
			GROUP BY `nodeAddress`, `endpointId`, `sensorName`
			ORDER BY `station`, `nodeAddress`, `endpointId`, `sensorName`';

		$dbs = mydb::select($stmt);

		$tables = new Table();
		$tables->caption='Sensor data count';
		$tables->thead=array('Station', 'Station Name', 'nodeAddress','endpointId','sensorName', 'items');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				$rs->station,
				$rs->title,
				$rs->nodeAddress,
				$rs->endpointId,
				$rs->sensorName,
				$rs->amt,
				'<a href="'.url('sensor',array('items'=>SG\getFirst($_REQUEST['items'],100),'n'=>$rs->nodeAddress,'e'=>$rs->endpointId,'s'=>$rs->sensorName)).'">View</a>'
			);
		}

		$ret .= $tables->build();

		if ($getNodeAddress) mydb::where('`nodeAddress` = :nodeAddress ',':nodeAddress',$getNodeAddress);
		if ($getEndPointId) mydb::where('`endpointId` = :endpointId ',':endpointId',$getEndPointId);
		if ($getStation) mydb::where('`sensorName` = :sensorName ',':sensorName',$getStation);

		$stmt = 'SELECT
				s.*
			, sid.`station`
			, st.`mmsl`
			, st.`levelfactor`
			FROM %flood_sensor% s
				LEFT JOIN %flood_sensorid% sid USING(`nodeAddress`)
				LEFT JOIN %flood_station% st USING(`station`)
			%WHERE%
			 ORDER BY `sid` DESC LIMIT '.$items;

		$dbs=mydb::select($stmt);

		$graph=NULL;

		if ($getSensorName == 'level') {
			$tables = new Table();
			$tables->caption='Water Level';
			$tables->thead=array('วันที่','เวลา', 'Station', 'ระดับน้ำ (ม.รทก.)');
			$graph->title='ระดับน้ำ (ม.รทก.)';
			$graph->items[]=array('Date','Water level');
			foreach (array_reverse($dbs->items) as $rs) {
				$compute='';
				if ($rs->mmsl) $compute=$rs->mmsl-$rs->value/1000+$rs->levelfactor;
				$tables->rows[]=array(sg_date(
					$rs->timeStamp,'ว ดด ปป'),
					sg_date($rs->timeStamp,'H:i'),
					$rs->station,
					$compute
				);
				$graph->items[]=array(sg_date($rs->timeStamp,'d-m-y H:i'), $compute);
			}
		/*
			$graphitems=array_reverse($dbs->items);
			$ret.=print_o($graphitems,'$graphitems');
			ksort($graph->items);
			$ret.=print_o($graph,'$graph');
		*/
			$ret.='<div id="chart_div" style="width: 100%; height: 400px;"></div>';

			$ret .= $tables->build();
		}

		$ret.='<script type="text/javascript">
		var graph='.json_encode($graph).'
		 $(document).ready(function() {
		 	drawChart();
		 });
		</script>';

		if (!$getLog) {
			$tables = new Table();
			$tables->caption='Sensor data';
			$tables->thead=array('timeStamp','Station','nodeAddress','endpointId','sensorName', 'compute', 'value');
			foreach ($dbs->items as $rs) {
				$compute='';
				if ($rs->sensorName=='level' && $rs->mmsl) $compute=$rs->mmsl-$rs->value/1000+$rs->levelfactor;
				$tables->rows[]=array(
					$rs->timeStamp,
					$rs->station,
					$rs->nodeAddress,
					$rs->endpointId,
					$rs->sensorName,
					$compute,
					($rs->sensorName=='camera'?'<a class="sensor-photo" href="upload/sensor/'.$rs->value.'" target="_blank" title="ภาพจากกล้อง '.$rs->value.'">'.$rs->value.'</a>':$rs->value)
				);
			}
			$ret .= $tables->build();
		} else if ($getLog) {
			$dbs=mydb::select('SELECT * FROM %watchdog% WHERE `module`="sensor" ORDER BY `wid` DESC LIMIT 100');

			$tables = new Table();
			$tables->caption='Sensor log';
			$tables->thead=array('date','ip','message','browser');
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array($rs->date,long2ip($rs->ip),$rs->message,'Browser : '.$rs->browser.'<br />Referer : '.$rs->referer);
			}

			$ret .= $tables->build();
		}
		return $ret;
	}

	function _data() {
		$isWatchLog=false;
		$this->theme->title='Recieve data from sensor';
		$data = file_get_contents("php://input");
		$log='<p>data='.$data.'</p>';
		$value=json_decode($data, true);
		if (empty($value['nodeAddress'])) return 'Error: nodeAddress is empty.';
		if (empty($value['endpointId'])) return 'Error: endpointId is empty.';
		$log.=print_o($value,'$value');
		$post->timeStamp=$value['timeStamp'];
		$post->nodeAddress=$value['nodeAddress'];
		$post->endpointId=$value['endpointId'];
		$post->created=date('U');
		$post->data=$data;
		foreach ($value['status'] as $k=>$v) {
			if (empty($k)) continue;
			$post->sensorName=$k;
			$post->value=$v;
			mydb::query('INSERT INTO %flood_sensor% (`timeStamp`, `nodeAddress`, `endpointId`, `sensorName`, `value`, `created`, `data`) VALUES (:timeStamp, :nodeAddress, :endpointId, :sensorName, :value, :created, :data)',$post);
		}
		if ($isWatchLog) {
			$log.=print_o($_GET,'$_GET');
			$log.=print_o($_POST,'$_POST');
			$log.=print_o($_FILES,'$_FILES');
			$log.=print_o($_REQUEST,'$_REQUEST');
		}
		model::watch_log('sensor','Recieve data',$log);
		$ret.=$log;
		return $ret;
	}

	function _photo_old() {
		$this->theme->title='Recieve photo from sensor';
		$log=print_o($_GET,'$_GET');
		$log.=print_o($_POST,'$_POST');
		$log.=print_o($_FILES,'$_FILES');
		$log.=print_o($_REQUEST,'$_REQUEST');

		if ($_FILES) {
			$photoKeyName=key($_FILES);
			$photo=$_FILES[$photoKeyName];
			if ($photo['error']==0) {
				if (!is_uploaded_file($photo['tmp_name'])) $error='Upload error : No upload file';
				$ext=strtolower(sg_file_extension($photo['name']));
				if (in_array($ext,array('jpg','jpeg'))) {
					// Upload photo
					$upload=new classFile($photo,'upload/sensor/',cfg('photo.file_type'));
					if ($photo['type'] && !$upload->valid_format()) $error="Upload error : Invalid photo format";
					if (!$error) {
						if ($upload->duplicate()) $upload->generate_nextfile();
						$photo_upload=$upload->filename;
						$pics_desc['type'] = 'photo';
						$pics_desc['title']=$rs->activityname;
					}
				}

				if (!$error) {
					$pics_desc['tpid'] = 0;
					$pics_desc['cid'] = 0;
					$pics_desc['gallery'] = $gallery;
					$pics_desc['uid']=i()->ok?i()->uid:'func.NULL';
					$pics_desc['file']=$photo_upload;
					$pics_desc['timestamp']='func.NOW()';
					$pics_desc['ip'] = ip2long(GetEnv('REMOTE_ADDR'));

					if ($upload->copy()) {
						$post->timeStamp=SG\getFirst($_REQUEST['timeStamp'],'func.NOW()');
						$post->nodeAddress=$_REQUEST['cameraAddress'];
						$post->endpointId=1;//$value['endpointId'];
						$post->created=date('U');
						$post->data=$log.print_o($upload,'$upload');
						$post->sensorName='camera';
						$post->value=str_replace('%', '', $photo['name']);
						mydb::query('INSERT INTO %flood_sensor% (`timeStamp`, `nodeAddress`, `endpointId`, `sensorName`, `value`, `created`, `data`) VALUES (:timeStamp, :nodeAddress, :endpointId, :sensorName, :value, :created, :data)',$post);
						if ($pics_desc['type']=='photo') {
							$photo=model::get_photo_property($upload->filename);
							$ret.='<img src="'.$photo->_url.'" height="60" alt="" />';
						} else {
							$ret.='อัพโหลดไฟล์เรียบร้อย';
						}
					} else {
						$error.='Upload error : Cannot save upload file';
					}
				}
			}
		}

		if ($error) $log='<p>'.$error.'</p>'._NL.$log;
		model::watch_log('sensor','Recieve photo',$log);
		$ret.=$log;
		die($ret);
		return $ret;
	}

	function _list() {
		$this->theme->title='Sensor List';

		return $ret;
	}

	function _photos($node) {
		global $nodes;
		$this->theme->title='ภาพจากกล้อง '.$nodes[$node][0];
		$station=$nodes[$node][1];
		$ret.='<ul class="tabs">';
		foreach ($station as $name) {
			$ret.='<li><a class="sg-action" href="'.url('sensor/report/photo/'.$name).'" data-rel="sensor-photo">'.$nodes[$node][2][$name].' ('.$name.')</a></li>';
		}
		$ret.='</ul>';

		$ret.='<div id="sensor-photo" data-load="'.url('sensor/report/photo/'.$station[0]).'"></div>';
		$ret.='<script>
		$(document).ready(function() {
			$("body").on("click","a.sensor-photo",function(){
				var $this = $(this);
				$this.colorbox({href:$this.attr("href"), width:"75%"});
			});
		 });
		</script>';
		return $ret;
	}

	function _photo($station) {
		$ret.='<h3>ภาพจากกล้อง '.$station.'</h3>';

		$station=SG\getFirst(post('station'),$station);

		$stmt='SELECT s.*, sid.`station`, st.`mmsl`,
				DATE_FORMAT(`timeStamp`,"%Y-%m-%d") `date`,
				DATE_FORMAT(`timeStamp`,"%H:%i") `time`
			FROM %flood_sensor% s
				LEFT JOIN %flood_sensorid% sid USING(`nodeAddress`)
				LEFT JOIN %flood_station% st USING(`station`)
			WHERE `sensorName`="camera" AND `station`=:station
			ORDER BY `timeStamp` DESC
			LIMIT 120
			';
		$dbs=mydb::select($stmt, ':date', $date, ':station',$station);

		$ret.='<ul class="sensor-photo-thumbnail">';
		foreach ($dbs->items as $rs) {
			$imgSrc='http://www.nadrec.psu.ac.th/upload/sensor/'.$rs->value;
			$ret.='<li><a class="sensor-photo" href="'.$imgSrc.'"><img src="'.$imgSrc.'" width="200" /></a><p>'.sg_date($rs->timeStamp,'d/m/ปป H:i').' น.</li>';
		}
		$ret.='</ul>';
//		$ret.=print_o($dbs,'$dbs');

		return $ret;
	}
} // end of class sensor_report
?>