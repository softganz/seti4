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

menu('sensor','Sensor Application Main Page','sensor','__controller',1,true,'static','home');
menu('sensor/report','Sensor Application Report Page','sensor.report','__controller',2,true,'static','home');

cfg('sensor.version','0.00.0');
cfg('sensor.release','13.9.6');

class sensor extends module {

	public function __construct() {
		parent::__construct();
	}

	function permission() { return 'administer sensors,create sensors,edit own sensors content,access sensors,access full sensors';}

	/**
	 * Method _home
	 * Home page of package gis
	 *
	 * @return String
	 */

	public function _home() {
		$para=para(func_get_args());
		$refresh=SG\getFirst($_REQUEST['refresh'],60);
		$items=SG\getFirst($_REQUEST['items'],100);
		$showRawData=post('rawdata');

		$refreshPara['items']=$items;
		if ($_REQUEST['n']) $refreshPara['n']=$_REQUEST['n'];
		if ($_REQUEST['e']) $refreshPara['e']=$_REQUEST['e'];
		if ($_REQUEST['s']) $refreshPara['s']=$_REQUEST['s'];
		if ($_REQUEST['log']) $refreshPara['log']=$_REQUEST['log'];
		if ($_REQUEST['rawdata']) $refreshPara['rawdata']=$_REQUEST['rawdata'];

		$isAdmin=user_access('administer sensors');

		$this->theme->title='Sensor';
		$ret.='<form method="get"><input type="hidden" name="n" value="'.$_REQUEST['n'].'" /><input type="hidden" name="e" value="'.$_REQUEST['e'].'" />';
				$stmt='SELECT `station`, `title` FROM %flood_station%	 ORDER BY `station` ASC';
		$dbs=mydb::select($stmt);
		$ret.='<select name="s" class="form-select"><option value="">ทุกสถานี(ยกเว้น TDR01)</option>';
		foreach ($dbs->items as $item) $ret.='<option value="'.$item->station.'" '.($item->station==$_REQUEST['s']?'selected="selected"':'').'>'.$item->title.'</option>';
		$ret.='</select> ';
		$ret.='<input type="hidden" name="log" value="'.$_REQUEST['log'].'" />Refresh time <input type="text" name="refresh" class="form-text form-number" size="2" value="'.$refresh.'" /> วินาที <input type="text" name="items" class="form-text form-number" size="4" value="'.$items.'" /> รายการ <button type="submit" class="btn -primary" value="Refresh"><i class="icon -refresh -white"></i><span>Refresh</span></button>';
		if ($isAdmin) $ret.='<br /><input type="checkbox" name="rawdata" value="1" '.($showRawData?'checked="checked"':'').' /> Show raw data';
		$ret.='</form>'._NL;
		$ret.='<div id="sensor-log" data-load="'.url('sensor/log',$refreshPara).'">Loading...</div>';

		/*
		head('jspi','<script type="text/javascript" src="https://www.google.com/jsapi"></script>');
			$ret.='<script type="text/javascript">
			google.load("visualization", "1", {packages:["corechart"]});
			google.setOnLoadCallback(drawChart);
			function drawChart() {
				if (graph) {
					var data = google.visualization.arrayToDataTable(graph.items);
					var options = {title: graph.title,};
					var chart = new google.visualization.LineChart(document.getElementById("chart_div"));
					chart.draw(data, options);
				}
			}
			</script>';
			*/

			$ret.='<script>
			 $(document).ready(function() {
			// $("#responsecontainer").load("response.php");
			   var refreshId = setInterval(function() {
			      $("#sensor-log").load($("#sensor-log").data("load"));
			   }, '.$refresh.'*1000);
			   $.ajaxSetup({ cache: false });
				$("body").on("click","a.sensor-photo",function(){
					var $this = $(this);
					$this.colorbox({href:$this.attr("href"), width:"75%"});
				});
			 });
			</script>';
		$ret.='<style type="text/css">
		.sensor--log em {font-size:0.85em;color:#ccc;font-style:normal;}</style>';
		return $ret;
	}

	function _log() {
		$getNodeAddress = post('n');
		$getEndPointId = post('e');
		$getStation = post('s');
		$getSensorName = post('sn');

		$items = SG\getFirst(post('items'),100);
		$showRawData = date('Y-m-d') == '2019-12-14' || post('rawdata');
		$getLog = post('log');

		$isAdmin=user_access('administrator floods,operator floods');

		$ret.='<h3>Sensor log @'.date('Y-m-d H:i:s').'</h3>';


		if ($getNodeAddress) mydb::where('`nodeAddress` = :nodeAddress',':nodeAddress',$getNodeAddress);
		if ($getEndPointId) mydb::where('`endpointId` = :endpointId',':endpointId',$getEndPointId);
		if ($getStation) {
			mydb::where('`station` = :station',':station',$getStation);
		} else {
			mydb::where('`station` != "TDR01"');
		}

		$stmt='SELECT s.*, st.`title`, st.`mmsl`, st.`levelfactor`
						FROM %flood_sensor% s
							LEFT JOIN %flood_station% st USING(`station`)
						%WHERE%
						 ORDER BY `sid` DESC LIMIT '.$items;
		$dbs=mydb::select($stmt,$where['value']);
		//$ret.=mydb()->_query;
		//$ret.=print_o($dbs,'$dbs');

		$graph=NULL;

		if ($getSensorName == 'level') {
			unset($tables);
			$tables = new Table();
			$tables->addClass('sensor--log');
			$tables->caption='Water Level';
			$tables->thead=array('Date','Time', 'Station', 'ระดับน้ำ (ม.รทก.)','Reference Level','Raw Data');
			$graph->title='ระดับน้ำ (ม.รทก.)';
			$graph->items[]=array('Date','Water level');
			foreach (array_reverse($dbs->items) as $rs) {
				$compute='';
				if ($rs->mmsl) $compute=$rs->mmsl-$rs->value/1000+$rs->levelfactor;
				$tables->rows[]=array(
															sg_date($rs->timeStamp,'ว ดด ปป'),
															sg_date($rs->timeStamp,'H:i'),
															$rs->title.' ('.$rs->station.')',
															$compute,
															$rs->mmsl,
															$rs->value,
															);
				if ($showRawData) $tables->rows[]=array('','','','<td colspan="6">'.$rs->data.'</td>');
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

		/*
		$ret.='<script type="text/javascript">
		var graph='.json_encode($graph).'
		 $(document).ready(function() {
		 	drawChart();
		 });
		</script>';
		*/

		if (!$getLog) {
			unset($tables);
			$tables = new Table();
			$tables->addClass('sensor--log');
			$tables->caption='Sensor data';
			$tables->thead=array('timeStamp','Station','sensorName', 'Value', 'Reference Level','Raw Data','Level Factor', 'compute -hover-parent'=>'Compute');
			foreach ($dbs->items as $rs) {
				$compute='';
				if ($rs->sensorName=='level' && $rs->mmsl && $rs->rawdata!=65534) $compute=$rs->mmsl-$rs->rawdata/1000+$rs->levelfactor;
				$tables->rows[]=array(
													$rs->timeStamp.'<br />'.
													'<em>'.sg_date($rs->created,'Y-m-d H:i:s').'</em>',
													'<b>'.$rs->title.'</b><br /><em>'.$rs->station.':'.$rs->nodeAddress.'('.$rs->endpointId.')</em>',
													$rs->sensorName,
													($rs->sensorName=='camera'?'<a class="sg-action sensor-photo" href="'.url('upload/sensor/'.$rs->value).'" data-rel="img" title="ภาพจากกล้อง '.$rs->value.'"><img src="'.url('upload/sensor/'.$rs->value).'" width="100" /></a>':$rs->value),
													$rs->sensorName=='level'?$rs->mmsl:'',
													$rs->sensorName!='camera'?$rs->rawdata:'',
													$rs->levelfactor,
													$compute
													. ($isAdmin?'<nav class="nav -icons -hover"><a class="sg-action" href="'.url('flood/monitor/log',array('action'=>'delete','id'=>$rs->sid)).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน" data-removeparent="tr" data-rel="none"><i class="icon -cancel -gray"></i></a></nav>':''),
													);
				if ($showRawData) $tables->rows[]=array('<td colspan="8">'.$rs->data.'</td>');
			}
			$ret .= $tables->build();
		} else if ($getLog) {
			$dbs=mydb::select('SELECT * FROM %watchdog% WHERE `module`="sensor" ORDER BY `wid` DESC LIMIT 100');
			unset($tables);
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

		$isDebug = false;
		$isWatchLog = false;
		$this->theme->title = 'Recieve data from sensor';

		$data = post('d') ? post('d') : file_get_contents("php://input");

		/*
		$data='{"timeStamp":"2015-08-06 22:38:27","nodeAddress":"00:13:A2:00:40:70:B7:4C","endpointId":1,"status":{"level":9691}}';
		$data='{"timeStamp":"2014-11-04 16:06:24","nodeAddress":"00:13:A2:00:40:70:B7:4C","endpointId":1,"status":{"level":7344,"batteryVoltage":4.90}}';
		*/

		$log = 'Data='.$data._NL;

		$ret .= 'DATA = '.$data.'<br />';
		$value = json_decode($data, true);

		if (empty($value['nodeAddress'])) return 'Error: nodeAddress is empty.';
		if (empty($value['endpointId'])) return 'Error: endpointId is empty.';

		$post->timeRec = sg_date($value['timeStamp'],'Y-m-d H:น15:00');
		$post->timeStamp = $value['timeStamp'];
		$post->nodeAddress = $value['nodeAddress'];
		$post->endpointId = $value['endpointId'];
		$post->rawdata = NULL;
		$post->created = date('U');
		$post->data = $data;

		$station = mydb::select('SELECT sid.`nodeAddress`, s.* FROM %flood_sensorid% sid LEFT JOIN %flood_station% s USING(`station`) WHERE sid.`nodeAddress`=:nodeAddress LIMIT 1',':nodeAddress',$post->nodeAddress);

		if ($isDebug) $ret .= print_o($station,'$station');

		$post->station = $station->station;

		foreach ($value['status'] as $k=>$v) {
			if (empty($k)) continue;

			$post->sensorName = $k;
			$post->value = $v;
			$post->rawdata = $v;
			// if level < levelmin then not record

			//$ret.=print_o($station,'$station');
			if ($post->sensorName=='level') {
				// value 65534 is invalid data

				if ($post->value > 10000) $post->value = 10000;
				$level = $station->mmsl - ($post->value/1000) + $rs->levelfactor;

				if ($station->levelmin && $station->mmsl) {
					$log.='<br />Caculate Level='.$level;
					/*
					if ($level<$station->levelmin) {
						mydb::query('UPDATE %flood_station% SET `waterupdate`=:waterupdate WHERE `station`=:station LIMIT 1',':station',$station->station, ':waterupdate',sg_date($post->timeRec,'U'));
						$ret.='Low level, update time only.<br />'.mydb()->_query.'<br />';
						continue;
					}
					*/
				}
				$post->value=$level;
				$ret.='Record level = '.$level.'<br />';
			}

			mydb::query('INSERT INTO %flood_sensor% (`station`, `timeRec`, `timeStamp`, `nodeAddress`, `endpointId`, `sensorName`, `value`, `rawdata`, `created`, `data`) VALUES (:station, :timeRec, :timeStamp, :nodeAddress, :endpointId, :sensorName, :value, :rawdata, :created, :data)',$post);

			if ($isDebug) $ret .= mydb()->_query.'<br />';

			if ($post->sensorName=='level') {
				mydb::query('UPDATE %flood_station% SET `water60min`=`water45min`, `water45min`=`water30min`, `water30min`=`water15min`, `water15min`=`waterlevel`, `waterlevel`=:waterlevel, `waterupdate`=:waterupdate WHERE `station`=:station LIMIT 1',':station',$station->station,':waterlevel',$level, ':waterupdate',sg_date($post->timeRec,'U'), ':last_updated',sg_date($post->timeRec,'U'));
				if ($isDebug) $ret.='Update level : '.mydb()->_query.'<br />';
			} else if ($post->sensorName=='batteryVoltage') {
				mydb::query('UPDATE %flood_station% SET `batterylevel`=:batterylevel WHERE `station`=:station LIMIT 1',':station',$station->station,':batterylevel',$post->value,':last_updated',sg_date($post->timeRec,'U'));
				if ($isDebug) $ret.='Update batteryVoltage : '.mydb()->_query.'<br />';
			} else if ($post->sensorName=='temperature') {
				mydb::query('UPDATE %flood_station% SET `temperature`=:temperature WHERE `station`=:station LIMIT 1',':station',$station->station,':temperature',$post->value);
				if ($isDebug) $ret.='Update temperature : '.mydb()->_query.'<br />';
			} else if ($post->sensorName=='relativeHumidity') {
				mydb::query('UPDATE %flood_station% SET `humidity`=:humidity WHERE `station`=:station LIMIT 1',':station',$station->station,':humidity',$post->value);
				if ($isDebug) $ret.='Update relativeHumidity : '.mydb()->_query.'<br />';
			}

		}

		$stmt = 'UPDATE %flood_station% SET `sensorupdate`=:date WHERE `station`=:station LIMIT 1';
		mydb::query($stmt,':station',$station->station,':date',date('U'));

		if ($isDebug) $ret.='Update station update : '.mydb()->_query.'<br />';

		if ($isWatchLog) {
			$log.='<br />';
			$log.=print_o($_GET,'$_GET');
			$log.=print_o($_POST,'$_POST');
			$log.=print_o($_FILES,'$_FILES');
			$log.=print_o($_REQUEST,'$_REQUEST');
		}

		model::watch_log('sensor',$station->station,$log);
		if ($isDebug) $ret.=$log.'<br />';

		
		if ($station->pushdataurl) {
			$urlList=$station->pushdataurl;
			if (is_string($urlList)) $urlList=array($urlList);
			$push['d']=$data;
			foreach ($urlList as $url) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $push);
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 300);
				curl_setopt($ch,CURLOPT_USERAGENT,'Softganz/1.0 (Web API 1.0) FloodSensor/1.0');
				curl_setopt($ch, CURLOPT_AUTOREFERER, true);
				curl_setopt($ch, CURLOPT_REFERER, cfg('domain'));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				$page = curl_exec($ch);
				curl_close($ch);

				$ret.='<p>Push data to '.$url.'</p>';
				$ret.='Error = '.curl_errno($ch);
				$ret.='Result '.($page===false?' False':($page===true?'True':'N/A')).'<br />';
				$ret.='Error='.curl_errno().'<br />';
				$ret.='$Page of '.$url.' is <br />'.htmlview($page).'<hr />';
			}
		}
		return $ret;
	}



	function _photo() {
		$this->theme->title='Recieve photo from sensor';
		$post = (object)post();
		$log = 'post = '.json_encode((array)$post).'<br />'._NL;
		$log .= 'file = '.json_encode($_FILES).'<br />'._NL;

		$uploadFolder = 'upload/sensor/';

		$debug = false;
		//$srcFile=$uploadFolder.$post->value;
		//echo $srcFile.'<br />';

		//$ret .= print_o($_FILES, '$_FILES');

		$error = array();

		if ($_FILES) {
			$photoKeyName = key($_FILES);
			$photo = $_FILES[$photoKeyName];
			if ($debug) $ret .= print_o($photo,'photo');
			if ($photo['error']==0) {
				$ret .= 'GET FROM KEY = '.$photoKeyName.'<br />'._NL;
				if (!is_uploaded_file($photo['tmp_name'])) $error[]='Upload error : No upload file';
				$ext = strtolower(sg_file_extension($photo['name']));
				if ($debug) $ret .= 'EXT = '.$ext.'<br />';
				if (in_array(strtoupper($ext),array('JPG','JPEG','JFIF'))) {
					// Upload photo
					$upload = new classFile($photo,$uploadFolder,cfg('photo.file_type'));
					//$upload->replace = true;
					//if ($photo['type'] && !$upload->valid_format()) $error[]="Upload error : Invalid photo format";
					if (!$error) {
						if ($upload->duplicate()) $upload->generate_nextfile();
						$photo_upload=$upload->filename;
					}
				} else $error[] = 'File extension not allow (jpg, jpeg)';

				$stmt = 'SELECT sid.`nodeAddress`, s.* FROM %flood_sensorid% sid LEFT JOIN %flood_station% s USING(`station`) WHERE sid.`nodeAddress`=:nodeAddress LIMIT 1';
				$stationRs = mydb::select($stmt,':nodeAddress',$post->cameraAddress);

				if ($stationRs->_empty) $error[] = 'No Camera Address';
				if (!$stationRs->station) $error[] = 'No Sensor Station';

				if ($debug) $ret .= mydb()->_query.'<br />';
				if ($debug) $ret .= print_o($stationRs,'$stationRs');

				$ret .= 'STATION = '.$stationRs->station.' ADDRESS = '.$stationRs->nodeAddress.'<br />'._NL;



				if (!$error) {
					$ret .= 'START SAVE FILE<br />';

					if ($upload->copy()) {
						$ret .= 'COPY FILE COMPLETE<br />';
						$post->timeStamp=SG\getFirst($post->timeStamp,date('Y-m-d H:i:s'));
						$post->timeRec=sg_date($post->timeStamp,'Y-m-d H:น15:00');
						$post->nodeAddress=$post->cameraAddress;
						$post->endpointId=1;//$value['endpointId'];
						$post->created=date('U');
						$post->sensorName='camera';
						$post->rawdata=$photo['name'];
						$post->value=str_replace('%', '', $photo['name']);
						$post->data = htmlspecialchars($log);


						$post->station = $stationRs->station;

						mydb::query('INSERT INTO %flood_sensor% (`station`, `timeRec`, `timeStamp`, `nodeAddress`, `endpointId`, `sensorName`, `value`, `rawdata`, `created`, `data`) VALUES (:station, :timeRec, :timeStamp, :nodeAddress, :endpointId, :sensorName, :value, :rawdata, :created, :data)',$post);
						if ($debug) $ret .= mydb()->_query.'<br />';
						$query .= mydb()->_query.'<br />';

						mydb::query('UPDATE %flood_station% SET `last_photo`=:last_photo , `last_updated`=:last_updated WHERE `station`=:station LIMIT 1',':last_photo',$post->value, ':last_updated',sg_date($post->timeRec,'U'), ':station',$post->station);
						if ($debug) $ret .= mydb()->_query.'<br />';
						$query .= mydb()->_query.'<br />';
						//$log='<p>'.mydb()->_query.'</p>'._NL.$log;

						// Copy photo file to lastphoto file on each station
						$srcFile = $uploadFolder.$post->value;
						$lastfile = $uploadFolder.'nadrec.lastphoto.'.$post->station.'.jpg';
						copy($srcFile,$lastfile);
						chmod($lastfile,0666);

						// Push file to other server
						if ($stationRs->pushphotourl) {
							$urlList=$stationRs->pushphotourl;
							if (is_string($urlList)) $urlList=array($urlList);
							$push['timeStamp']=$post->timeStamp;
							$push['cameraAddress']=$post->cameraAddress;
							//$push['photo']='@'.$photo['tmp_name'].';filename='.$photo['name'].';type='.$photo['type'];
							$push['photo'] = new CurlFile($photo['tmp_name'],$photo['type'],$photo['name']);
							foreach ($urlList as $url) {
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, $url);
								curl_setopt($ch, CURLOPT_POST, true);
								curl_setopt($ch, CURLOPT_POSTFIELDS, $push);
								curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
								curl_setopt($ch,CURLOPT_USERAGENT,'Softganz/1.0 (Web API 1.0) FloodSensor/1.0');
								curl_setopt($ch, CURLOPT_AUTOREFERER, true);
								curl_setopt($ch, CURLOPT_REFERER, cfg('domain'));
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

								$page = curl_exec($ch);
								curl_close($ch);

								$ret.='<p>Push data to '.$url.'</p>';
								$ret.='Error = '.curl_errno($ch);
								$ret.='Result '.($page===false?' False':($page===true?'True':'N/A')).'<br />';
								$ret.='Error='.curl_errno().'<br />';
								$ret.='$Page of '.$url.' is <br />'.htmlview($page).'<hr />';
							}
						}

						$image = '<img src="'.url('upload/sensor/'.$upload->filename).'" height="60" alt="" /><br />'._NL;
						$ret.='อัพโหลดไฟล์เรียบร้อย<br />'._NL;
					} else {
						$error.='Upload error : Cannot save upload file';
					}
					if ($debug) $ret .= print_o($upload, '$upload');
				}
			}
		} else {
			$error[] = 'No file to save';
		}

		$log = $query.$log;
		if ($error) $log = '<p>ERROR : '.implode('<br />'._NL.'ERROR : ',$error).'</p>'._NL.$log;

		model::watch_log('sensor','Recieve photo',htmlspecialchars($log));

		//$ret .= $log;

		$result = new stdClass();
		$result->query = $query;
		$result->error = $error;
		$result->message = $ret;
		$result->image = $image;
		$result->post = $post;
		$result->file = (Object) $_FILES;
		//$result->log = $log;

		if (post('test')) {
			die(print_o($result));
		} else {
			die(json_encode($result));
		}

		return $ret;
	}

	function _list() {
		$this->theme->title='Sensor List';

		return $ret;
	}

	function _testphoto() {
		$ret = '<form method="post" action="'.url('sensor/photo').'" enctype="multipart/form-data">'
			. '<input type="hidden" name="test" value="1" />'
			. '<input type="text" name="timeStamp" placeholder="timeStamp" value="'.date('Y-m-d H:i:s').'" /> '
			. '<input type="text" name="cameraAddress" placeholder="cameraAddress" value="E0:DC:A0:57:85:AF" /> '
			. '<input type="file" name="photo" /> '
			. '<button class="btn -primary" type="submit" value="UPLOAD"><i class="icon -material">cloud_upload</i><span>UPLOAD</span></button>'
			. '</form>';
		return $ret;
	}

	function _photos($station) {
		$nodeAddress=mydb::select('SELECT `nodeAddress` FROM %flood_sensorid% WHERE `station`=:station',':station',$station)->lists->qoute;
		//$ret.=print_o($nodeAddress,'$nodeAddress');
		if ($nodeAddress) {
			$dbs=mydb::select('SELECT `sid`, `timeStamp`, `value` FROM %flood_sensor% s WHERE `nodeAddress` IN ('.$nodeAddress.') AND `sensorName`="camera" ORDER BY `sid` DESC LIMIT 50');
			$ret.='<ul class="sensor-photo-thumbnail">';
			foreach ($dbs->items as $rs) {
				$imgSrc='/upload/sensor/'.$rs->value;
				$ret.='<li><a href="'.$imgSrc.'"><img src="'.$imgSrc.'" width="200" /></a><p>'.sg_date($rs->timeStamp,'d/m/ปป H:i').' น.</li>';
			}
			$ret.='</ul>';
			//			$ret.=print_o($dbs,'$dbs');
		}

		return $ret;
	}

	function _testpushphoto() {
		// Push file to other server
		$post->timeStamp=date('Y-m-d H:i:s');
		$post->cameraAddress='00:27:22:61:8B:1D';
		$photo['tmp_name']='/tmp/phpzQStIT';
		$photo['name']='Cam-KlongLuek%402015-07-13T15-42-28GMT%2B0700.jpg';
		$photo['type']='image/jpeg';
		$photo['error']=0;
		$photo['size']='88788';
		$ret.=print_o($photo,'$photo');

		if (cfg('flood.sensor.push.photo')) {
			$urlList=cfg('flood.sensor.push.photo');
			if (is_string($urlList)) $urlList=array($urlList);
			$push['timeStamp']=SG\getFirst($post->timeStamp,date('Y-m-d H:i:s'));
			$push['nodeAddress']=$post->cameraAddress;
			//$push['photo']='@'.$photo['tmp_name'].';filename='.$photo['name'].';type='.$photo['type'];
			$push['photo'] = new CurlFile($photo['tmp_name'],$photo['type'],$photo['name']);
			foreach ($urlList as $url) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $push);
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
				curl_setopt($ch,CURLOPT_USERAGENT,'Softganz/1.0 (Web API 1.0) FloodSensor/1.0');
				curl_setopt($ch, CURLOPT_AUTOREFERER, true);
				curl_setopt($ch, CURLOPT_REFERER, cfg('domain'));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

				$page = curl_exec($ch);
				curl_close($ch);

				$ret.='<p>Push data to '.$url.'</p>';
				$ret.='Error = '.curl_errno($ch);
				$ret.='Result '.($page===false?' False':($page===true?'True':'N/A')).'<br />';
				$ret.='Error='.curl_errno().'<br />';
				$ret.='Push data='.print_o($push,'$push');
				$ret.='$Page of '.$url.' is <br />'.$page.htmlview($page).'<hr />';
			}
		}
		return $ret;
	}
} // end of class sensor
?>