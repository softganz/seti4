<?php
/**
* Model :: File Model
* Created 2021-12-21
* Modify  2021-12-21
*
* @param Int $fileId
* @return Object
*
* @usage new FileModel($fileId)
*/

class FileModel {
	var $fileId;

	function __construct($fileId = NULL) {
		$this->fileId = $fileId;
	}

	public static function get($fileId, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		if ($debug) debugMsg(mydb()->_query);

		mydb::where('`fid` = :fileId', ':fileId', $fileId);
		$rs = mydb::select(
			'SELECT
			f.`fid` `id`
			, f.`fkey` `key`
			, f.`tpid` `nodeId`
			, f.`cid` `commentId`
			, f.`uid`
			, f.`orgId`
			, f.`refId`
			, f.`type`
			, f.`tagName`
			, f.`cover`
			, f.`gallery`
			, f.`file` `fileName`
			, f.`title`
			, f.`description`
			, f.`comment` `commentCount`
			, f.`votes` `voteCount`
			, f.`view` `viewCount`
			, f.`last_view` `lastViewDate`
			, f.`reply` `replyCount`
			, f.`download` `downloadCount`
			, f.`last_doanload` `lastDownloadDate`
			, f.`timeStamp`
			, f.`ip`
			FROM %topic_files% f
			%WHERE%
			LIMIT 1'
		);

		if ($rs->_empty) return NULL;

		$result = (Object) [
			'fileId' => $rs->id,
			'fileName' => $rs->fileName,
			'title' => $rs->title,
			'info' => mydb::clearprop($rs),
			'property' => $rs->type == 'photo' ? FileModel::photoProperty($rs->fileName) : ($rs->type == 'doc' ? FileModel::docProperty($rs->fileName) : NULL),
		];

		return $result;
	}

	public static function items($conditions, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) [
			'count' => 0,
			'items' => [],
		];

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$conditions = (Object) ['id' => $conditions];
		}

		// TODO: Code for get items

		if ($conditions->nodeId) mydb::where('f.`tpid` = :nodeId', ':nodeId', $conditions->nodeId);
		if ($conditions->tagName) mydb::where('f.`tagName` = :tagName', ':tagName', $conditions->tagName);
		if ($conditions->type) mydb::where('f.`type` = :type', ':type', $conditions->type);
		if ($conditions->refId) mydb::where('f.`refId` = :refId', ':refId', $conditions->refId);

		$dbs = mydb::select(
			'SELECT
			f.`fid` `id`
			, f.`fkey` `key`
			, f.`tpid` `nodeId`
			, f.`cid` `commentId`
			, f.`uid`
			, f.`orgId`
			, f.`refId`
			, f.`type`
			, f.`tagName`
			, f.`cover`
			, f.`gallery`
			, f.`file` `fileName`
			, f.`title`
			, f.`description`
			, f.`comment` `commentCount`
			, f.`votes` `voteCount`
			, f.`view` `viewCount`
			, f.`last_view` `lastViewDate`
			, f.`reply` `replyCount`
			, f.`download` `downloadCount`
			, f.`last_doanload` `lastDownloadDate`
			, f.`timeStamp`
			, f.`ip`
			FROM %topic_files% f
			%WHERE%
			'
		);

		$result->count = count($dbs->items);
		$result->items = $dbs->items;

		return $result;
	}

	public static function photoProperty($file, $folder = null) {
		if (is_object($file)) {
			$property = $file;
		} else if (is_string($file)) {
			$property = (Object) [
				'name' => $file,
				'src' => NULL,
				'url' => NULL,
				'exists' => false,
				'size' => NULL,
				'width' => NULL,
				'height' => NULL,
				'mime' => NULL,
			];
		} else {
			return false;
		}

		if (substr($property->name,0,2) == './') {
			$folderName = substr(dirname($property->name),2).'/';
			$fileName = basename($property->name);
		} else {
			$dirName = dirname($property->name);
			$fileName = basename($property->name);
		}

		if ($dirName == '.') unset($dirName);

		//debugMsg('fileName='.$fileName.' , dirname='.$dirName.' , folder='.$folder.' , filename='.$fileName.' , cfg(upload.url)='.cfg('upload.url').' , cfg(upload_folder) = '.cfg('upload_folder'));

		if ($dirName) {
			$property->src = $dirName.'/'.sg_urlencode($fileName);
			$property->name = cfg('folder.abs').$dirName.'/'.sg_tis620_file($fileName);
		} else if ($folder && preg_match('/^upload/', $folder)) {
			$property->src = $folder.'/'.sg_urlencode($fileName);
			$property->url = cfg('url.abs').$folder.'/'.sg_urlencode($fileName);
			$property->name = cfg('folder.abs').$folder.'/'.sg_tis620_file($fileName);
		} else if ($folder) {
			$property->src = cfg('upload.url').$folder.'/'.sg_urlencode($fileName);
			$property->name = cfg('upload.folder').$folder.'/'.sg_tis620_file($fileName);
		} else {
			$property->src = _url.cfg('upload_folder').'pics/'.$folderName.sg_urlencode($fileName);
			$property->name = $photo_location = cfg('folder.abs').cfg('upload_folder').'pics/'.$folderName.$fileName;
		}

		if (file_exists($property->name)) {
			$property->size = filesize($photo_location);
			if (!isset($property->url)) $property->url=cfg('url.abs').cfg('upload_folder').'pics/'.$folderName.sg_urlencode($fileName);
			$size = getimagesize($property->name);
			$property->exists = true;
			$property->width = $size[0];
			$property->height = $size[1];
			$property->mime = $size['mime'];
		}
		return $property;
	}

	public static function docProperty($file, $folder = null) {
		$subFolder = 'forum/';
		if (is_object($file)) {
			$property = $file;
		} else if (is_string($file)) {
			$property = (Object) [
				'name' => $file,
				'src' => NULL,
				'url' => NULL,
				'exists' => false,
				'size' => NULL,
				'mime' => NULL,
			];
		} else {
			return false;
		}

		if (substr($property->name,0,2) == './') {
			$folderName = substr(dirname($property->name),2).'/';
			$fileName = basename($property->name);
		} else {
			$dirName = dirname($property->name);
			$fileName = basename($property->name);
		}

		if ($dirName == '.') unset($dirName);

		//debugMsg('fileName='.$fileName.' , dirname='.$dirName.' , folder='.$folder.' , filename='.$fileName.' , cfg(upload.url)='.cfg('upload.url').' , cfg(upload_folder) = '.cfg('upload_folder'));

		if ($dirName) {
			$property->name = cfg('folder.abs').$dirName.'/'.sg_tis620_file($fileName);
			$property->src = $dirName.'/'.sg_urlencode($fileName);
		} else if ($folder && preg_match('/^upload/', $folder)) {
			$property->name = cfg('folder.abs').$folder.'/'.sg_tis620_file($fileName);
			$property->src = $folder.'/'.sg_urlencode($fileName);
			$property->url = cfg('url.abs').$folder.'/'.sg_urlencode($fileName);
		} else if ($folder) {
			$property->name = cfg('upload.folder').$folder.'/'.sg_tis620_file($fileName);
			$property->src = cfg('upload.url').$folder.'/'.sg_urlencode($fileName);
		} else {
			$property->name = $photo_location = cfg('folder.abs').cfg('upload_folder').$subFolder.$folderName.$fileName;
			$property->src = _url.cfg('upload_folder').$subFolder.$folderName.sg_urlencode($fileName);
		}

		if (file_exists($property->name)) {
			$property->size = filesize($photo_location);
			if (!isset($property->url)) $property->url = cfg('url.abs').cfg('upload_folder').$subFolder.$folderName.sg_urlencode($fileName);
			$size = getimagesize($property->name);
			$property->exists = true;
		}
		return $property;
	}
}
?>