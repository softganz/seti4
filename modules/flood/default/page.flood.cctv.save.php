<?php
/**
 * Get image from public camera
 *
 * @param String $camera use 1,2,3,... for many camera
 * @return String
 */
function flood_cctv_save($self, $camname = NULL, $photoUrl = NULL) {
	$cameraInfo=R::Model('flood.camera.get',$camid);
	R::View('flood.toolbar',$self,'Flood Public Camera Realtime Update '.$cameraInfo->title,NULL,$cameraInfo);

	//https://storage.googleapis.com/sparkcam/preview/CCB8A8B47440_Camera/camera_20191013_010507.jpeg
	//https://storage.googleapis.com/sparkcam/pub/CCB8A8B47440_Camera/camera_20191013_010507.jpeg

	//$photoUrl = 'https://storage.googleapis.com/sparkcam/pub/CCB8A8B47440_Camera/camera_20191013_010508.jpeg';

	$cameras=mydb::select('SELECT * FROM %flood_cam% WHERE `imgurl` IS NOT NULL');

	// Save photo from camera to file and database
	if ($photoUrl) {
		//$camname = 'psu-01';
		$filename = $camname.'-lastphoto.jpg';

		$uploadFolder = _FLOOD_UPLOAD_FOLDER.'realtime/';

		//$uploadFilename=$uploadFolder.$filename;
		$uploadFilename = $uploadFolder.$camname.'-lastphoto.jpg';
		$lastFilename = $uploadFolder.$camname.'-lastphoto.jpg';
		$uploadTmpFilename = $uploadFolder.$camname.'-lastphoto-tmp.jpg';

		//$ret .= 'uploadFilename = '.$uploadFilename.'<br />lastFilename='.$lastFilename.'<br />';

		if (!is_dir($uploadFolder)) {
			mkdir($uploadFolder, 0777, true);
		}

		$lastFileSize = filesize($uploadFilename);

		$fh = fopen($uploadTmpFilename, 'w');
		if (!$fh) {
			$error[]='Unable to create '.$camname.' file.';
			return;
		}
		chmod($uploadFilename,0666);

		// Get file from camera with curl function
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $photoUrl);
		curl_setopt($ch, CURLOPT_USERPWD, $rs->uname.':'.$rs->passwd);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_ALL);
		curl_setopt($ch, CURLOPT_PORT, $rs->port);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_FILE, $fh);
			curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Safari/537.36');

		/*
		$success = curl_exec($ch);

		$cloud = json_decode(curl_exec ($ch), true);
		$googleheader = array();
		foreach($cloud['_requiredHeaders'] as $key=>$value)
		{
		    $googleheader[] = $key . ': ' . $value;
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $googleheader );
		*/

		$success = curl_exec($ch);
		$info = curl_getinfo($ch);

		curl_close($ch);
		fclose($fh);

		//				$filesize=filesize($file);

		//				$success=filesize($file)>0;

		$ret .= print_o($info,'$info');
		return $ret;

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

			$newFileSize = filesize($uploadTmpFilename);
			//					$ret.=$newFileSize.'='.$lastFileSize.'<br />';

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
			chmod($lastFilename,0666);
			//					$ret.='Copy file '.$file.' to '.$last_file.'<br />';

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
			//$ret.=print_o($data,'$data');


			// Show photo result
			$ret.='<div id="flood-camera-update-result">'._NL;
			$ret.=implode('',$complete);
			$ret .= $error;

			$ret.='<p>Content type='.$info['content_type'].' download time='.$info['total_time'].'</p>';
			$ret.='</div>';
			//			$ret.='Success code = '.$success.'<br />'.print_o($info,'$info');
		} else {
			//unlink($uploadTmpFilename);
			$ret = false;
		}
	}

	return $ret;
}
?>