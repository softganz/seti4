<?php
/**
 * Get image from public camera
 *
 * @param String $camera use 1,2,3,... for many camera
 * @return String
 */
function flood_realtime_update($self,$camid=NULL) {
	$cameraInfo=R::Model('flood.camera.get',$camid);
	R::View('flood.toolbar',$self,'Flood Public Camera Realtime Update '.$cameraInfo->title,NULL,$cameraInfo);

	$resizeToWidth = cfg('flood')->resizeToWidth;
	$resizeToQuality = cfg('flood')->resizeToQuality;

	$cameras=mydb::select('SELECT * FROM %flood_cam% WHERE `imgurl` IS NOT NULL');

	$ret.='<div id="flood-camera-update-menu"><h3>IP Camera list</h3><ul>'._NL;
	foreach ($cameras->items as $rs) {
		$ret.='<li><a href="'.url('flood/realtime/update/'.$rs->camid).'">'.$rs->title.'</a></li>'._NL;
	}
	$ret.='</ul></div>'._NL;

	// Save photo from camera to file and database
	if ($camid) {
		$camlist=explode(',',$camid);
		foreach ($camlist as $camid) {
			foreach ($cameras->items as $rs) if ($rs->camid==$camid) break;
			if ($rs->camid != $camid) continue;
			// Camera was exists
			$camname = $rs->name;
			$filename = $camname.'-lastphoto.jpg';

			$uploadFolder=_FLOOD_UPLOAD_FOLDER.'realtime/';

			//$uploadFilename=$uploadFolder.$filename;
			$uploadFilename = $uploadFolder.$camname.'-lastphoto.jpg';
			$lastFilename = $uploadFolder.$camname.'-lastphoto.jpg';
			$uploadTmpFilename = $uploadFolder.$camname.'-lastphoto-tmp.jpg';

			//$ret .= 'uploadFilename = '.$uploadFilename.'<br />lastFilename='.$lastFilename.'<br />';

			if (!is_dir($uploadFolder)) {
				mkdir($uploadFolder, 0777, true);
			}


			foreach (explode('|', $rs->imgurl) as $cameraUrl) {
				$cameraUrl = trim($cameraUrl);

				$lastFileSize = filesize($uploadFilename);

				$fh = fopen($uploadTmpFilename, 'w');
				if (!$fh) {
					$error[]='Unable to create '.$camname.' file.';
					continue;
				}
				chmod($uploadFilename,0666);

				// Get file from camera with curl function
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $cameraUrl);
				curl_setopt($ch, CURLOPT_USERPWD, $rs->uname.':'.$rs->passwd);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
				curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_ALL);
				curl_setopt($ch, CURLOPT_PORT, $rs->port);
				curl_setopt($ch, CURLOPT_TIMEOUT, 60);
				curl_setopt($ch, CURLOPT_FILE, $fh);
				curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');

				$success = curl_exec($ch);
				$info = curl_getinfo($ch);
				curl_close($ch);
				fclose($fh);

				//				$filesize=filesize($file);

				//				$success=filesize($file)>0;

				// $ret .= print_o($info,'$info');

				$size = getimagesize($uploadTmpFilename);

				// Add photo information to database

				if (substr($info['content_type'],0,6) == 'image/' || in_array($size['mime'],array('image/jpeg', 'image/gif'))) {
					// Code from - Image Watermarking Using PHP ,Tuesday October 12, 2010 by Ivan Kristianto
					// Code uri - http://www.ivankristianto.com/web-development/programming/image-watermarking-using-php/1485/

					//Using imagecopymerge() to create a translucent watermark
					// Load the image on which watermark is to be applied
					$original_image = imagecreatefromjpeg($uploadTmpFilename);

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
					imagejpeg($original_image, $uploadTmpFilename);
					imagedestroy($original_image);

					if ($resizeToWidth) {
						sg_photo_resize($uploadTmpFilename, $resizeToWidth, null, $uploadTmpFilename , true, $resizeToQuality);
					}

					$newFileSize = filesize($uploadTmpFilename);
					// $ret.=$newFileSize.'='.$lastFileSize.'<br />';

					if ($newFileSize != $lastFileSize) {

						$post->uid=SG\getFirst(i()->uid,NULL);
						$post->camid=$rs->camid;
						$post->photo=$filename;
						$post->ip=ip2long(i()->ip);
						$post->created=date('U');
						$post->url=_FLOOD_UPLOAD_URL.'realtime/'.$filename;

						/*
						$stmt='INSERT INTO %flood_photo% (`uid`, `camid`, `photo`, `ip`, `created`) VALUES (:uid, :camid, :photo, :ip, :created)';
						mydb::query($stmt,$post);
						mydb::query('UPDATE %flood_cam% SET `last_photo`=:last_photo, `last_updated`=:last_updated WHERE `camid`=:camid LIMIT 1',':camid',$post->camid,':last_photo',$post->photo,':last_updated',$post->created);
						*/

						// Create Thumbnail
						/*
						$thumbfile=_CACHE_FOLDER.$camname.'-'.$filename;
						$thumburl=_CACHE_URL.$camname.'-'.$filename;
						sg_photo_resize($file, 200, null, $thumbfile , true, 60);
						chmod($thumbfile,0666);
						*/

						copy($uploadTmpFilename,$uploadFilename);
						unlink($uploadTmpFilename);
						//chmod($lastFilename,0666);
						// $ret.='Copy file '.$file.' to '.$last_file.'<br />';

						$complete[$camname] = '<h3>Update photo from camera '.$rs->title.'</h3>'._NL.'<a href="'.$post->url.'" target="_blank"><img id="photo-update" src="'.$post->url.'" /></a>'._NL.'<p id="filename-update">'.$filename.' size '.number_format($newFileSize,0).' bytes.</p>'._NL;

						// Update Google Firebase
						$firebaseCfg = cfg('firebase');
						$firebase = new Firebase($firebaseCfg['projectId'], $firebaseCfg['flood'].'camera');
						$data = array(
							'camid' => intval($camid),
							'name' => $camname,
							'photo' => $post->photo,
							'url' => _DOMAIN.$post->url,
							'thumb' => _DOMAIN.$post->url,
							'date' => sg_date($post->created,'ว ดด ปป'),
							'time' => sg_date($post->created,'H:i'),
							'timestamp' => array('.sv' => 'timestamp')
						);

						$fbresult = $firebase->put($camname,$data);
						//$ret.=print_o($fbresult,'$fbresult');
					} else {
						$error[$camname]='<p>Get image from <strong>'. $camname.'</strong> was  same lastphoto.!!</p>'._NL;
					}
				} else {
					$error[$camname]='<p>Get image from <strong>'. $camname.'</strong> was  error!!</p>'._NL;
				}
			} // Each camera url
		}



		// Show photo result
		$ret.='<div id="flood-camera-update-result">'._NL;
		$ret.=implode('',$complete);

		if (count($camlist)==1) {
			if ($_SERVER['HTTP_HOST']=='localhost') {
				$refresh=array(1,5,10,30,60,300,600,900,1800,3600);
			} else {
				$refresh=array(60,300,600,900,1800,3600);
			}
			$ret.='<form method="get"><label>Update every (Seconds)</label><select name="timer" class="form-select">'._NL;
			foreach ($refresh as $sec) $ret.='<option value="'.$sec.'"'.($_REQUEST['timer']==$sec?' selected="selected"':'').'>'.$sec.'</option>'._NL;
			$ret.='</select>'._NL;
			$ret.='<button class="btn -primary" type="submit" name="start" value="Start" /><i class="icon -save -white"></i><span>Start</span></button> <button class="btn" type="submit" name="stop" value="Stop"><i class="icon -cancel"></i><span>Stop</span></button></form>'._NL;
		}

		$ret.=$error?implode('',$error):'';
		if (_AJAX) {
			$json=(array)$post;
			return json_encode($json);
		}
		$ret.='<p>Content type='.$info['content_type'].' download time='.$info['total_time'].'</p>';
		$ret.='</div>';
		//			$ret.='Success code = '.$success.'<br />'.print_o($info,'$info');
	}

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
			$.getJSON("'.url('flood/camera/update/'.$camid).'",function(json) {
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