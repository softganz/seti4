<?php
/**
 * Get photo from ftp folder
 *
 * @param String $camid use 1,2,3,... for many camera
 * @return String
 * @info Upload filename pattern cameraname-timestamp.jpg
 */
function flood_camera_ftp_scan($self) {
	$getDelay = SG\getFirst(post('delay'), 45);
	if ($getDelay) sleep($getDelay);
	$debug = debug('yes');

	$ftpFolder = _FLOOD_FTP_FOLDER_SRC;
	//debugmsg('Get file from folder : '.$ftpFolder);

	$allPhotos = __flood_camera_ftp_get_all_image($ftpFolder);

	foreach ($allPhotos as $cameraId => $photoList) {
		if ($debug) $ret .= 'Photo Count of '.$cameraId.' = '.count($photoList).'<br />';

		// Only file contain "-" will be process
		if (count($photoList) == 1) continue;
		if ($debug) $ret .= '<b>PROCESS File Group '.$cameraId.'</b><br />';

		$usePhoto = min($photoList);
		$lastPhoto = max($photoList);

		$srcFile = $ftpFolder.$usePhoto;
		$destFile = $ftpFolder.$cameraId.'.jpg';

		$srcFileSize = filesize($srcFile);
		if ($debug) $ret .= $srcFile.' size '.number_format($srcFileSize).' bytes.<br />';

		// Check fiel size, if = 0 then not save
		//if ($srcFileSize == 0) continue;

		if ($srcFileSize > 0) {
			$copyToLastPhoto = copy($srcFile,$destFile);
			if ($debug) $ret .= 'MOVE to '.$destFile.'<br />';
		} else {
			unlink($srcFile);
		}

		if (1 || $copyToLastPhoto) {
			foreach ($photoList as $filename) {
				if ($filename == $lastPhoto) continue;

				$deleteFile = $ftpFolder.$filename;
				if ($debug) $ret .= 'DELETE PHOTO IN SERIES '.$filename.' size '.number_format(filesize($deleteFile)).' bytes.<br />';
				unlink($deleteFile);
			}
		}
		if ($debug) $ret .= '<b>GROUP '.$cameraId.' was processed.</b><br />';
		$ret .= '<hr />';
	}
	if ($debug) $ret .= print_o($allPhotos,'$allPhotos');

	return $ret;
}

/**
 * Get all image in folder and subfolder
 *
 * @param String $folder
 * @return Array
 */
function __flood_camera_ftp_get_all_image($folder = './') {
	$photos = array();
	if (substr($folder,-1)!='/') $folder .= '/';
	if ( $d = dir($folder) ) {
		while($entry = $d->read()) {
			//debugMsg('$entry = '.$folder.$entry.' = '.is_dir($folder.$entry));
			if (substr($entry,0,1) == '.') continue;
			if (is_dir($folder.$entry)) {
				//debugMsg('Sub Folder '.$entry);
				//$photos[$entry] = __flood_camera_ftp_get_all_image($folder.$entry);
			}
			if (preg_match('/(\w+)[\-](.*)/',$entry,$out)) {
				//debugMsg($out,'$out');
				$photos[strtolower($out[1])][] = $entry;
			} else {
				//debugMsg($entry);
				// Not contain "-"
				//$photos[$entry] = $entry;
			}
			/*
			if (is_file($folder.$entry) && strtolower(substr($entry,-4))=='.jpg') {
				$photos[$entry]=array('file'=>$entry,'loc'=>$folder.$entry,'time'=>filectime($folder.$entry));
			}
			if (is_dir($folder.$entry)) $photos=array_merge($photos,__flood_camera_ftp_get_all_image($folder.$entry));
			*/
		}
		$d->close();
	}
	return $photos;
}
?>