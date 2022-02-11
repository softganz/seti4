<?php
/**
 * Get photo from ftp folder
 *
 * @param String $camid use 1,2,3,... for many camera
 * @return String
 * @info Upload filename pattern cameraname-timestamp.jpg
 */
function flood_camera_ftp($self, $camid = NULL) {
	$cameraInfo = R::Model('flood.camera.get',$camid);
	R::View('flood.toolbar',$self,'Flood Public Camera Update From FTP '.$cameraInfo->title,NULL,$cameraInfo);

	if ($_SERVER['HTTP_HOST'] == 'localhost') {
	}

	$allCameras = mydb::select('SELECT * FROM %flood_cam%; -- {key:"camid"}')->items;

	$ret .= '<div id="flood-camera-update-menu"><h3>IP Camera list</h3><ul>'._NL;
	foreach ($allCameras as $rs) {
		$ret .= '<li><a href="'.url('flood/camera/ftp/'.$rs->camid).'">'.$rs->title.'</a></li>'._NL;
	}
	$ret .= '</ul></div>'._NL;

	$ret .= '<div id="flood-camera-update-result">';

	if ($camid) {
		$cameraList = explode(',',$camid);
		$filename = date('Ymd-His').'.jpg';
		$ftpFolder = _FLOOD_FTP_FOLDER_SRC;
		//debugmsg('Get file from folder : '.$ftpFolder);
		foreach ($cameraList as $camid) {
			if (!array_key_exists($camid, $allCameras)) continue;
			$rs = $allCameras[$camid];
			//$ret.=print_o($rs,'$rs');
			// Camera was exists
			$camname = $rs->name;

			$ret .= '<h3>'.$rs->title.'</h3>';

			if ($_SERVER['HTTP_HOST'] == 'localhost') {
				$refresh = array(1,5,10,30,60,300,600,900,1800,3600);
			} else {
				$refresh = array(60,300,600,900,1800,3600);
			}

			$all_photos = __flood_camera_ftp_get_all_image($ftpFolder);

			foreach ($all_photos as $photo) {
				preg_match('/(\w+)[\-](.*)/',$photo['file'],$out);
				if (!$out) continue;
				$file_camera_name = strtolower($out[1]);
				if ($camname != $file_camera_name) continue;

				$filename = date ('Ymd-His', $photo['time']).'.jpg';

				$photoRs = new stdClass;
				$photoRs->name = $camname;
				$photoRs->photo = $filename;
				$photoRs->created = $photoRs->atdate = $photo['time'];
				$photoRs->uid = SG\getFirst(i()->uid,'func.NULL');
				$photoRs->camid = $camid;
				$photoRs->ip = ip2long(i()->ip);
				$photoRs->url = flood_model::photo_url($photoRs);

				$photoFile = flood_model::photo_loc($photoRs);
				$photoFileFolder = dirname($photoFile);

				if (!is_dir($photoFileFolder)) {
					mkdir($photoFileFolder, 0777, true);
				}

				//$ret .= 'photoFolder = '.$photoFileFolder.'<br />';
				//$ret .= 'photoFile = '.$photoFile.'<br />';

				$stmt = 'INSERT INTO %flood_photo% (`uid`, `camid`, `photo`, `ip`, `created`) VALUES (:uid, :camid, :photo, :ip, :created)';
				mydb::query($stmt, $photoRs);

				//debugMsg(mydb()->_query);

				$stmt = 'UPDATE %flood_cam% SET `last_photo` = :photo, `last_updated` = :created WHERE `camid` = :camid LIMIT 1';
				mydb::query($stmt, $photoRs);

				//debugMsg(mydb()->_query);
				//debugMsg($out,'$out');
				//debugMsg($post,'$post');
				//debugMsg($photo,'$photo');

				if (!mydb()->_error) {
					$move_complete = rename($photo['loc'],$photoFile);

					// Code from - Image Watermarking Using PHP ,Tuesday October 12, 2010 by Ivan Kristianto
					// Code uri - http://www.ivankristianto.com/web-development/programming/image-watermarking-using-php/1485/

					//Using imagecopymerge() to create a translucent watermark
					// Load the image on which watermark is to be applied
					$original_image = imagecreatefromjpeg($photoFile);

					// First we create our watermark image manually from GD
					$watermark = imagecreatetruecolor(250, 40);
					//Set the hex color code for your watermark and dimension
					imagestring($watermark, 5, 20, 10, 'Hatyai City Climate', 0xFFFFF);
					imagestring($watermark, 3, 20, 25, cfg('domain'), 0xFFFFF);

					// Set the margins for the watermark and get the height/width of the watermark image
					$marge_right = 10;
					$marge_bottom = 10;
					$sx = imagesx($watermark);
					$sy = imagesy($watermark);

					// Merge the watermark onto our photo with an opacity (transparency) of 50%
					imagecopymerge($original_image, $watermark, imagesx($original_image) - $sx - $marge_right, imagesy($original_image) - $sy - $marge_bottom, 0, 0, imagesx($watermark), imagesy($watermark), 20);

					 $watermark_logo = imagecreatefrompng('file/ad/ACCCRN.png');
					$sx = imagesx($watermark_logo);
					$sy = imagesy($watermark_logo);
					imagecopymerge($original_image, $watermark_logo, imagesx($original_image) - $sx - $marge_right, imagesy($original_image) - $sy - $marge_bottom-40, 0, 0, imagesx($watermark_logo), imagesy($watermark_logo), 7);

					// Save the image to file and free memory
					imagejpeg($original_image, $photoFile);
					imagedestroy($original_image);

					// Create Thumbnail
					$thumbFile = flood_model::thumb_loc($photoRs);
					$thumbFolder = dirname($thumbFile);
					if (!file_exists($thumbFolder)) {
						mkdir($thumbFolder, 0777, true);
					}

					sg_photo_resize($photoFile, 200, null, $thumbFile , true, 60);
					chmod($thumbFile,0666);

					$thumbUrl = flood_model::thumb_url($photoRs);

					//$ret .= 'thumbFolder = '.$thumbFolder.'<br />';
					//$ret .= 'thumburl = '.$thumbUrl.'<br />';

					// Create last photo of camera
					$lastFile = _FLOOD_LASTPHOTO_FOLDER.$photoRs->name.'.jpg';

					copy($photoFile,$lastFile);
					chmod($lastFile,0666);

					//						$move_complete=copy($photo['loc'],$photoFileFolder.$filename);
					//						unlink($photo['loc']);
					//						$ret.='<p>Save '.$photo['loc'].' => '.$photoFileFolder.$filename.'</p>';
					//						$ret.=$photo['loc'].'<br />'.$photoFileFolder.$photo['file'].'<br />';


					// Update Google Firebase
					$firebaseCfg = cfg('firebase');
					$firebase = new Firebase($firebaseCfg['projectId'], $firebaseCfg['flood'].'camera');
					$data = array(
						'camid'=>intval($camid),
						'name'=>$camname,
						'photo'=>$photoRs->photo,
						'url'=>_DOMAIN.$photoRs->url,
						'thumb'=>_DOMAIN.$thumbUrl,
						'date'=>sg_date($photoRs->created,'ว ดด ปป'),
						'time'=>sg_date($photoRs->created,'H:i'),
						'timestamp'=>array('.sv'=>'timestamp')
					);
					$fbresult=$firebase->put($camname,$data);
					//$ret.=print_o($fbresult,'$fbresult');
				}
				$ret .= '<a href="'.$photoRs->url.'" target="_blank"><img id="photo-update" src="'.$photoRs->url.'" /></a><p id="filename-update">'.$photoRs->photo.'</p>';
			}
		}
		//$ret.=print_o($all_photos,'photo');
		//			$ret.='<p>Get image from <strong>'. $camid.'</strong> was '.($success?'successfull':'error!!').'.</p>';
		if (_AJAX) {
			$json=(array)$photoRs;
			return json_encode($json);
		}
	}
	$ret .= '<form method="get"><label>Update every (Seconds)</label><select class="form-select" name="timer">'._NL;
	foreach ($refresh as $sec) {
		$ret .= '<option value="'.$sec.'"'.($_REQUEST['timer']==$sec?' selected="selected"':'').'>'.$sec.'</option>'._NL;
	}
	$ret .= '</select> '._NL;
	$ret .= '<button class="btn" type="submit" name="start" value="Start"><i class="icon -save"></i><span>START</span></button> ';
	$ret .= '<button class="btn" type="submit" name="stop" value="Stop"><i class="icon -cancel"></i><span>STOP</span></button></form>'._NL;

	$ret .= '</div>';

	if ($_REQUEST['timer'] && $_REQUEST['start']) {
		header('refresh:'.$_REQUEST['timer'].';url='.url(q(),'timer='.$_REQUEST['timer'].'&start='.$_REQUEST['start']));
	}
	$ret.='<script type="text/javascript">
		$(document).ready(function() {
		var timerRunning = false; // boolean flag
		var myTimer = null;
			$("input[name=start]").click(function() {
				(function request() {
					notify("Image updating........");
					var timer=$("select").val()*1000;
					$.getJSON("'.url('flood/camera/ftp/'.$camid).'",function(json) {
						notify("Updating complete...",3000);
						$("#photo-update").attr("src",json.url);
						$("#filename-update").html(json.photo);
					});
					//calling the anonymous function after 10000 milli seconds
					if (timerRunning)
						clearTimeout(myTimer);
					myTimer=setTimeout(request, timer);  //second
					timerRunning=true;
				})(); //self Executing anonymous function
				return false;
			});
			$("input[name=stop]").click(function() {
				if (timerRunning) {
					clearTimeout(myTimer);
					notify("Stop image update",5000);
				}
				return false;
			});
		});
		</script>';
	return $ret;
}

/**
 * Get all image in folder and subfolder
 *
 * @param String $folder
 * @return Array
 */
function __flood_camera_ftp_get_all_image($folder='./') {
	$photos=array();
	if (substr($folder,-1)!='/') $folder.='/';
	if ( $d = dir($folder) ) {
		while($entry=$d->read()) {
			if (substr($entry,0,1)=='.') continue;
			if (is_file($folder.$entry) && strtolower(substr($entry,-4))=='.jpg') {
				$photos[]=array('file'=>$entry,'loc'=>$folder.$entry,'time'=>filectime($folder.$entry));
			}
			if (is_dir($folder.$entry)) $photos=array_merge($photos,__flood_camera_ftp_get_all_image($folder.$entry));
		}
		$d->close();
	}
	return $photos;
}
?>