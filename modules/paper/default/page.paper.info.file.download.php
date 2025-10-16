<?php
/**
* paper   :: Show download file
* Created :: 2021-01-06
* Modify  :: 2025-06-13
* Version :: 2
*
* @param String $nodeInfo
* @return Widget
*
* @usage paper/{nodeId}/info.file.download/{fileId}
*/

use Softganz\DB;

class paperInfoFileDownload extends Page {
	var $nodeId;
	var $fileId;
	var $nodeInfo;

	function __construct($nodeInfo = NULL, $fileId = NULL) {
		parent::__construct([
			'nodeId' => $nodeInfo->nodeId,
			'fileId' => $fileId,
			'nodeInfo' => $nodeInfo,
			'fileId' => $fileId,
		]);
	}

	function rightToBuild() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่มีหัวข้อ');
		if (empty($this->fileId)) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่มีไฟล์');
		return true;
	}

	function build() {
		$ret = '';

		$fileInfo = $this->getFileInfo($this->nodeId, $this->fileId);

		if (!$fileInfo->fileId) return message('error', 'ไม่พบไฟล์');

		$this->increaseDownloadTime();

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
		}
		
		$ret .= message('error','Download file '
			. (user_access('access debugging program') ? '('.$file.') ' : '')
			. 'not exists.');

		return $ret;

		
		// return new Scaffold([
		// 	'appBar' => new AppBar([
		// 		'title' => 'Title',
		// 	]), // AppBar
		// 	'body' => new Widget([
		// 		'children' => [], // children
		// 	]), // Widget
		// ]);
	}

	private function getFileInfo($nodeId, $fileId) {
		return DB::select([
			'SELECT f.`fid` `fileId`, f.*
			FROM %topic_files% f
			WHERE f.`tpid` = :tpid AND f.`type` = "doc" AND f.`tagname` IS NULL AND f.`fid` = :fid LIMIT 1',
			'var' => [
				':tpid' => $this->nodeId,
				':fid' => $this->fileId,
			]
		]);
	}

	private function increaseDownloadTime() {
		DB::query([
			'UPDATE %topic_files% SET `download` = `download` + 1, `last_download` = :now
			WHERE `fid` = :fid
			LIMIT 1',
			'var' => [':fid' => $this->fileId, ':now' => date('U')]
		]);
	}
}
?>