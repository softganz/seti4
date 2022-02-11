<?php
/**
 * Get image from public camera
 *
 * @param String $camera use 1,2,3,... for many camera
 * @return String
 */
function flood_camera_update($self, $camId = NULL) {
	R::View('flood.toolbar',$self,'Flood Public Camera Update '.$cameraInfo->title,NULL,$cameraInfo);

	$cameraInfo = R::Model('flood.camera.get',$camId);
	$resizeToWidth = cfg('flood')->resizeToWidth;
	$resizeToQuality = cfg('flood')->resizeToQuality;

	$cameras = mydb::select(
		'SELECT `camid`, `name`, `title`, `replaceid`, `camip`, `imgurl`, `port`, `uname`, `passwd`
		FROM %flood_cam%
		WHERE `imgurl` IS NOT NULL;
		-- {key: "camid"}'
	)->items;

	$ret = '<div id="flood-camera-update-menu"><h3>IP Camera list</h3><ul>'._NL;
	foreach ($cameras as $rs) {
		$ret .= '<li><a href="'.url('flood/camera/update/'.$rs->camid).'">'.$rs->title.'</a></li>'._NL;
	}
	$ret .= '</ul></div>'._NL;

	//$ret .= print_o($cameras);

	// Save photo from camera to file and database
	if (!$camId) return $ret;

	$cameraList = explode(',',$camId);

	foreach ($cameraList as $cameraId) {
		if (!isset($cameras[$cameraId])) continue;

		$rs = $cameras[$cameraId];

		if ($rs->replaceid) {
			$replaceCam = $cameras[$rs->replaceid];
			$rs->camip = $replaceCam->camip;
			$rs->imgurl = $replaceCam->imgurl;
			$rs->port = $replaceCam->port;
			$rs->uname = $replaceCam->uname;
			$rs->passwd = $replaceCam->passwd;
		}

		$camname = $rs->name;

		foreach (explode('|', $rs->imgurl) as $cameraUrl) {
			$cameraUrl = trim($cameraUrl);
			$filename = date('Ymd-His').'.jpg';


			$photoRs = (Object) [
				'camid' => $rs->camid,
				'name' => $rs->name,
				'photo' => $filename,
				'created' => date('U'),
				'atdate' => date('U'),
				'uid' => i()->uid,
				'ip' => ip2long(i()->ip),
			];
			$photoRs->url = flood_model::photo_url($photoRs);

			$photoFile = flood_model::photo_loc($photoRs);
			$photoFileFolder = dirname($photoFile);
			if (!is_dir($photoFileFolder)) {
				mkdir($photoFileFolder, 0777, true);
			}

			//debugMsg('Camera URL = '.$cameraUrl);
			//$ret .= 'photoFolder = '.$photoFileFolder.'<br />';
			//$ret .= 'photoFile = '.$photoFile.'<br />';

			$lastFile = _FLOOD_LASTPHOTO_FOLDER.$photoRs->name.'.jpg';
			$lastFileFolder = dirname($lastFile);
			if (!is_dir($lastFileFolder)) {
				mkdir($lastFileFolder, 0777, true);
			}


			$fh = fopen($photoFile, 'w');
			if (!$fh) {
				$error[] = 'Unable to create '.$camname.' file.';
				continue;
			}
			chmod($photoFile,0666);



			// Get file from camera with curl function
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $cameraUrl);
			curl_setopt($ch, CURLOPT_USERPWD, $rs->uname.':'.$rs->passwd);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_ALL);
			curl_setopt($ch, CURLOPT_PORT, $rs->port);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			curl_setopt($ch, CURLOPT_FILE, $fh);
			curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36');

			$success = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			fclose($fh);

			//$ret .= 'filename = '.basename($photoFile).'<br />';

			$originalImageProperty = getimagesize($photoFile);
			$originalFilesize = filesize($photoFile);

			// $ret .= print_o($originalImageProperty, '$originalImageProperty');
			//$ret .= print_o($info,'$info');

			// Add photo information to database

			if (substr($info['content_type'],0,6) != 'image/' || !in_array($originalImageProperty['mime'],array('image/jpeg', 'image/gif'))) {
				unlink($photoFile);
				$error[] = '<p>Get image from <strong>'. $camname.'</strong> was  error!!</p>'._NL;
				continue;
			}


			// Code from - Image Watermarking Using PHP ,Tuesday October 12, 2010 by Ivan Kristianto
			// Code uri - http://www.ivankristianto.com/web-development/programming/image-watermarking-using-php/1485/

			if ($originalImageProperty['mime'] == 'image/jpeg') {
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
				imagecopymerge(
					$original_image,
					$watermark,
					imagesx($original_image) - $sx - $marge_right,
					imagesy($original_image) - $sy - $marge_bottom,
					0,
					0,
					imagesx($watermark),
					imagesy($watermark),
					20
				);

				$watermark_logo = imagecreatefrompng('file/ad/ACCCRN.png');
				$sx = imagesx($watermark_logo);
				$sy = imagesy($watermark_logo);

				imagecopymerge(
					$original_image,
					$watermark_logo,
					imagesx($original_image) - $sx - $marge_right,
					imagesy($original_image) - $sy - $marge_bottom-40,
					0,
					0,
					imagesx($watermark_logo),
					imagesy($watermark_logo),
					7
				);

				// Save the image to file and free memory
				imagejpeg($original_image, $photoFile);
				imagedestroy($original_image);

				if ($resizeToWidth && $originalImageProperty[0] > $resizeToWidth) {
					sg_photo_resize($photoFile, $resizeToWidth, null, $photoFile , true, $resizeToQuality);
				}
			}


			$lastFilesize = filesize($lastFile);
			$filesize = filesize($photoFile); // If check filesize befor lastFileSize, will error value

			// $ret .= $filesize.'='.$lastFilesize.'<br />';

			// Save photo if size not equal
			if ($filesize === $lastFilesize) {
				unlink($photoFile);
				$complete[] = '<h3>Photo from camera '.$rs->title.' same as last photo!!!</h3>'._NL;
				$error[] = '<p>Get image from <strong>'. $camname.' ( '.$filesize.' bytes )'.'</strong> was  same lastphoto.!!</p>'._NL;
				continue;
			}

			mydb::query(
				'INSERT INTO %flood_photo%
				(`uid`, `camid`, `photo`, `ip`, `created`)
				VALUES
				(:uid, :camid, :photo, :ip, :created)',
				$photoRs
			);

			mydb::query(
				'UPDATE %flood_cam%
				SET `last_photo` = :last_photo, `last_updated` = :last_updated
				WHERE `camid` = :camid
				LIMIT 1',
				[
					':camid' => $photoRs->camid,
					':last_photo' => $photoRs->photo,
					':last_updated' => $photoRs->created,
				]
			);

			// Create Thumbnail
			$thumbFile = flood_model::thumb_loc($photoRs);
			$thumbFolder = dirname($thumbFile);
			if (!file_exists($thumbFolder)) {
				mkdir($thumbFolder, 0777, true);
			}

			sg_photo_resize($photoFile, 200, null, $thumbFile , true, 50);
			chmod($thumbFile,0666);

			$thumbUrl = flood_model::thumb_url($photoRs);

			// $ret .= 'photoFolder = '.$photoFileFolder.'<br />';
			// $ret .= 'photoFile = '.$photoFile.'<br />';
			// $ret .= 'thumbFolder = '.$thumbFolder.'<br />';
			// $ret .= 'thumburl = '.$thumbUrl.'<br />';

			// Create last photo of camera
			copy($photoFile, $lastFile);
			chmod($lastFile, 0666);

			// $ret.='Copy file '.$photoFile.' to '.$last_file.'<br />';

			$imageProperty = getimagesize($photoFile);
			$filesize = filesize($photoFile);

			$complete[]='<h3>Updated photo from camera '.$rs->title.' complete!!!</h3>'._NL
				. '<a href="'.$photoRs->url.'" target="_blank"><img id="photo-update" src="'.$photoRs->url.'" /></a>'._NL
				. '<p id="filename-update"><b>'.$filename.'</b><br />'
				. '<b>Original</b> size <b>'.$originalImageProperty[0].'x'.$originalImageProperty[1].'</b> pixel, file size <b>'.number_format($originalFilesize).'</b> bytes.<br />'
				. '<b>Resize</b> size <b>'.$imageProperty[0].'x'.$imageProperty[1].'</b> pixel, file size <b>'.number_format($filesize).'</b> bytes.<br />'
				. '<b>Last</b> file size <b>'.number_format($lastFilesize).'</b> bytes.'
				. '</p>'._NL;

			// Update Google Firebase
			$domain = _DOMAIN;
			$firebaseCfg = cfg('firebase');
			$firebase = new Firebase($firebaseCfg['projectId'], $firebaseCfg['flood'].'camera');
			$data = [
				'camid' => intval($cameraId),
				'name' => $camname,
				'photo' => $photoRs->photo,
				'url' => $domain.$photoRs->url,
				'thumb' => $domain.$thumbUrl,
				'date' => sg_date($photoRs->created,'ว ดด ปป'),
				'time' => sg_date($photoRs->created,'H:i'),
				'timestamp' => array('.sv' => 'timestamp')
			];
			$fbresult = $firebase->put($camname,$data);

			//$ret.=print_o($fbresult,'$fbresult');
		} // Each camera url




		$ret .= '<div id="flood-camera-update-result">'._NL;
		$ret .= implode('',$complete);

		if (count($cameraList)==1) {
			if ($_SERVER['HTTP_HOST']=='localhost') {
				$refresh = [1,5,10,30,60,300,600,900,1800,3600];
			} else {
				$refresh = [60,300,600,900,1800,3600];
			}
			$ret.='<form method="get"><label>Update every (Seconds)</label><select name="timer" class="form-select">'._NL;
			foreach ($refresh as $sec) $ret.='<option value="'.$sec.'"'.($_REQUEST['timer']==$sec?' selected="selected"':'').'>'.$sec.'</option>'._NL;
			$ret.='</select>'._NL;
			$ret.='<button class="btn -primary" type="submit" name="start" value="Start" /><i class="icon -save -white"></i><span>Start</span></button> <button class="btn" type="submit" name="stop" value="Stop"><i class="icon -cancel"></i><span>Stop</span></button></form>'._NL;
		}

		$ret .= $error ? implode('',$error) : '';
		if (_AJAX) {
			$json = (Array) $photoRs;
			return json_encode($json);
		}
		$ret .= '<p>Content type='.$info['content_type'].' download time='.$info['total_time'].'</p>';
		$ret .= '</div>';
		// $ret.='Success code = '.$success.'<br />'.print_o($info,'$info');

	} // Each Camera Id

	if ($getTimer = post('timer') && $getTimerStart = post('start')) {
		header('refresh:'.$getTimer.';url = '.url(q(),'timer='.$getTimer.'&start='.$getTimerStart));
	}

	$ret .= '<script type="text/javascript">
$(document).ready(function() {
var timerRunning = false; // boolean flag
var myTimer = null;
	$("input[name=start]").click(function() {
		(function request() {
			notify("Image updating........");
			var timer=$("select").val()*1000;
			$.getJSON("'.url('flood/camera/update/'.$camId).'",function(json) {
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
?>