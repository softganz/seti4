<?php
/**
* paper :: Show download file
* Created 2021-01-06
* Modify  2021-01-06
*
* @param Object $self
* @param Object $topicInfo
* @return String
*
* @usage paper/{id}/info.file
*/

$debug = true;

function paper_info_file_download($self, $topicInfo = NULL, $fid = NULL) {
	// Data Model
	$tpid = $topicInfo->tpid;

	if (!$tpid || !$fid) {
		return new ErrorMessage([
			'responseCode' => _HTTP_ERROR_NOT_FOUND,
			'text' => 'ไม่พบไฟล์',
		]);
	}

	$ret = '';

	$fileInfo = mydb::select(
		'SELECT f.*
		FROM %topic_files% f
		WHERE f.`tpid` = :tpid AND f.`type` = "doc" AND f.`tagname` IS NULL AND f.`fid` = :fid LIMIT 1',
		[
			':tpid' => $tpid,
			':fid' => $fid,
		]
	);

	if (!$fileInfo->count()) return message('error', 'ไม่พบไฟล์');

	$stmt = 'UPDATE %topic_files% SET `download` = `download` + 1, `last_download` = :now
		WHERE `fid` = :fid
		LIMIT 1';

	mydb::query($stmt, ':fid', $fid, ':now', date('U'));

	// View Model
	//$doc_file = cfg('paper.upload.document.folder').$doc->file;
	$file = cfg('paper.upload.document.folder').$fileInfo->file;
	//$ret .= $file;

	if (file_exists($file) && is_file($file)) {
		$ext = sg_file_extension($file);
		header('Content-type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.$fileInfo->title.'.'.$ext.'"');
		readfile($file);
		die;
	} else {
		$ret .= message('error','Download file '
			. (user_access('access debugging program')?'('.$file.') ':'')
			. 'not exists.');
	}

	//$ret .= print_o($fileInfo);

	//$ret .= print_o($topicInfo);
	return $ret;
}
?>