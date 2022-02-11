<?php
/**
* Project fund paid document
*
* @param Object $self
* @param Integer $tpid
* @param String $action
* @param Integer $actid
* @return String
*/
function garage_job_photo_download($self, $jobInfo) {
	if (!($jobId = $jobInfo->tpid)) return message('error', 'PROCESS ERROR');

	$shopInfo = R::Model('garage.get.shop');

	$stmt = 'SELECT
		f.*, j.`plate`
		FROM %topic_files% f
			LEFT JOIN %garage_job% j USING(`tpid`)
		WHERE f.`tpid` = :tpid AND f.`type` = "photo"
		';
	$photoRs = mydb::select($stmt, ':tpid', $jobId, ':fid', $photoId);


	$isEditable = i()->uid == $photoRs->uid || in_array($jobInfo->shopInfo->iam, array('ADMIN','MANAGER','ACCOUNTING'));
	$isViewable = $jobInfo->is->viewable;

	if (!$isViewable) return message('error', 'Access Denied');

	$ret = '';

	new Toolbar( $self, 'ภาพถ่าย'.' - '.$jobInfo->oinfo->plate, 'job', $jobInfo);

	if ($photoRs->_empty) return message('notify', 'ไม่มีไฟล์ภาพของใบสั่งซ่อม');


	// create a temp file & open it
	$tmp_file = tempnam('./tmp','job_'.$jobId.'_');


	$zip = new ZipArchive();
	$zip->open($tmp_file, ZipArchive::CREATE);

	$photoTypeList = array('photo1' => 'ภาพเคาะ-ดึง', 'photo2' => 'ภาพโป๊ว', 'photo3' => 'ภาพพื้น', 'photo4' => 'ภาพพ่นสี', 'photo5' => 'ภาพคู่ซาก1', 'photo6' => 'ภาพคู่ซาก2');

	// loop through each file
	foreach($photoRs->items as $rs) {
		$file = $rs->file;

		list(,$photoType) = explode(',', $rs->tagname);

		$photoType = $photoTypeList[$photoType];

		$folder = 'ใบสั่งซ่อม_'.$jobId.'_'.$jobInfo->plate.'/'.$photoType.'/';

		// download file
		$download_file = file_get_contents('upload/pics/'.$file);

		// add it to the zip
		$zip->addFromString($folder.basename($file),$download_file);

    //$zip->addFile('upload/pics/'.$file, $folder.$file);

	}

	// close zip
	$zip->close();

	$ret .= 'FILESIZE = '.filesize ($tmp_file).'<br />';


	// send the file to the browser as a download
	header('Cache-Control: no-cache, must-revalidate'); // HTTP/1.1
	header('Content-Type: application/zip;');
	header('Content-Transfer-Encoding: Binary');
	header('Content-Disposition: attachment; filename="ใบสั่งซ่อม_'.$jobId.'_'.$jobInfo->plate.'.zip"');
	header("Content-Length:". filesize ($tmp_file));
	readfile($tmp_file);

	unlink($tmp_file);

	// Process MUST DIE to avoid after output
	die;

	return $ret;
}
?>