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

	public static function upload($photoFiles, $data = NULL, $options = '{}') {
		$defaults = '{debug: false, showDetail: true;}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$useSourceFilename = $options->useSourceFilename;

		$result = (Object) [
			'link' => NULL,
			'photofile' => NULL,
			'uploadfile' => NULL,
			'error' => [],
			'items' => [],
			'_query' => [],
		];

		$uploadFolder = cfg('paper.upload.photo.folder');
		$photoPrename = SG\getFirst($data->prename, 'paper_'.$data->tpid.'_');
		$photoFilenameLength = SG\getFirst($options->fileNameLength, 30);
		$isUploadSingleFile = true;

		$deleteurl = $data->deleteurl;
		//$ret='Upload photo of orgid '.$orgid.' tagName='.$tagName.' photoPrename '.$photoPrename.'<br />';


		// Multiphoto file upload
		if (is_array($photoFiles['name'])) {
			$isUploadSingleFile = false;
			foreach ($photoFiles['name'] as $key => $value) {
				$uploadPhotoFiles[$key] = array(
					'name' => $photoFiles['name'][$key],
					'type' => $photoFiles['type'][$key],
					'tmp_name' => $photoFiles['tmp_name'][$key],
					'error' => $photoFiles['error'][$key],
					'size' => $photoFiles['size'][$key],
				);
			}
		} else {
			$uploadPhotoFiles[] = $photoFiles;
		}

		//$ret.=print_o(post(),'post').print_o($uploadPhotoFiles,'$uploadPhotoFiles');

		foreach ($uploadPhotoFiles as $postFile) {
			//debugMsg($postFile,'$postFile');
			if (!is_uploaded_file($postFile['tmp_name'])) {
				$result->error[] = 'Upload error : No upload file ('.$postFile['name'].')<br />';
				continue;
			}

			$ext = strtolower(sg_file_extension($postFile['name']));
			//$ret.='ext='.$ext;
			if (!in_array($ext, array('jpg', 'jpeg', 'png', 'pdf'))) {
				$result->error[] = 'Upload error : Invalid File Type ('.$postFile['name'].')<br />';
				continue;
			}

			// Upload photo
			if ($ext == 'pdf') {
				$uploadFolder = cfg('paper.upload.document.folder');
				$upload = new classFile($postFile, $uploadFolder);
				//$ret.=print_o($upload,'$upload');
			} else {
				$upload = new classFile($postFile, $uploadFolder, cfg('photo.file_type'));
				if (!$upload->valid_format()) {
					$result->error[] = 'Upload error : Invalid photo format ('.$postFile['name'].')';
					continue;
				}
				if (!$upload->valid_size(cfg('photo.max_file_size')*1024)) {
					sg_photo_resize($upload->upload->tmp_name, cfg('photo.resize.width'), NULL, NULL,true, cfg('photo.resize.quality'));
				}
			}

			if ($useSourceFilename) {
				$upload->filename = $postFile['name'];
			} else {
				$upload->generate_nextfile($photoPrename, $photoFilenameLength);
			}

			$photo_upload = $upload->filename;

			$picsData = new stdClass();

			$picsData->fid = empty($data->fid) ? NULL : $data->fid;
			$picsData->tpid = empty($data->tpid) ? NULL : $data->tpid;
			$picsData->cid = empty($data->cid) ? NULL : $data->cid;
			$picsData->type = $ext == 'pdf' ? 'doc' : 'photo';
			$picsData->title = SG\getFirst($data->title, $postFile['name']);
			$picsData->tagname = empty($data->tagname) ? NULL : $data->tagname;

			$picsData->orgid = empty($data->orgid) ? NULL : $data->orgid;
			$picsData->uid = SG\getFirst($data->uid,i()->uid);
			$picsData->file = $photo_upload;
			$picsData->refid = empty($data->refid) ? NULL : $data->refid;
			$picsData->description = $data->description;
			$picsData->timestamp = 'func.NOW()';
			$picsData->ip = ip2long(GetEnv('REMOTE_ADDR'));

			$linkInfo = '';

			if ($upload->copy()) {
				//$ret.='<p>Upload file '.$postFile['name'].' save complete.</p>';
				$stmt = 'INSERT INTO %topic_files%
					(
						`fid`
					, `tpid`
					, `cid`
					, `type`
					, `orgid`
					, `uid`
					, `refid`
					, `tagname`
					, `file`
					, `title`
					, `description`
					, `timestamp`
					, `ip`
					) VALUES (
					  :fid
					, :tpid
					, :cid
					, :type
					, :orgid
					, :uid
					, :refid
					, :tagname
					, :file
					, :title
					, :description
					, :timestamp
					, :ip
					) ON DUPLICATE KEY UPDATE
					`file` = :file';

				mydb::query($stmt, $picsData);

				if (empty($picsData->fid)) $fid = $picsData->fid = mydb()->insert_id;

				$result->_query[] = mydb()->_query;


				if ($picsData->type == 'photo') {
					$picsData->photo = $photo = model::get_photo_property($upload->filename);

					if ($data->link == 'href') {
						$uploadUrl = url('project/'.$data->tpid.'/info.photo/'.$fid);
						$linkInfo .= '<a class="sg-action" data-rel="box" href="'.$uploadUrl.'" data-width="840" data-height="80%">';
					} else {
						$uploadUrl = $photo->_url;
						$linkInfo .= '<a class="sg-action" data-rel="img" data-group="photo" href="'.$photo->_url.'" title="">';
					}

					$linkInfo .= '<img class="photoitem" src="'.$photo->_url.'" alt="" width="100%" />';
					$linkInfo .= '</a>';
					if ($options->showDetail) $linkInfo .= '<span class="photodetail">คำอธิบายภาพ</span>';

					$ui = new Ui('span');
					if ($deleteurl) {
						$ui->add('<a class="sg-action -no-print" href="'.url($deleteurl.$fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -cancel -gray"></i></a>');
					}
					$linkInfo .= '<nav class="nav -icons -hover">'.$ui->build().'</nav>'._NL;
				} else {
					$uploadUrl = cfg('paper.upload.document.url').$upload->filename;
					$linkInfo .= '<a href="'.$uploadUrl.'" target="_blank">'
						. '<img class="photoitem -doc" src="//img.softganz.com/icon/icon-file.png" width="63" />'
						. '<span class="title">'.$picsData->title.'</span>'
						. '</a>';
					$ui = new Ui();
					if ($deleteurl) {
						$ui->add('<a class="sg-action -no-print" href="'.url($deleteurl.$fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -cancel -gray"></i></a>');
					}
					$linkInfo .= $ui->build();
				}

				$picsData->link = $linkInfo;
				$picsData->_FILES = $postFile;
				$result->items[] = $picsData;
			} else {
				$result->error[] = 'Upload error : Cannot save upload file ('.$postFile['name'].')<br />';
			}
			$result->link .= $linkInfo.'</li><li id="photo-'.$fid.'" class="ui-item -hover-parent">';
		}


		if ($result->link)
			$result->link = rtrim($result->link,'</li><li id="photo-'.$fid.'" class="ui-item -hover-parent">');

		$result->photofile = $photoFiles;
		$result->uploadfile = $uploadPhotoFiles;
		return $result;
	}

	/**
	* Delete photo
	*
	* @param Integer $fid - file id
	* @param Object $options
	* @return String
	*/
	public static function delete($fid, $options = '{}') {
		$defaults = '{debug: false, deleteRecord: true, deleteFile: true}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) [
			'photoInused' => false,
			'msg' => NULL,
			'_query' => [],
		];


		$stmt = 'SELECT * FROM %topic_files% f WHERE f.`fid` = :fid LIMIT 1';
		$rs = mydb::select($stmt,':fid',$fid);
		$result->_query[] = mydb()->_query;

		if ($rs->file) {
			if ($rs->type == 'photo') {
				if ($options->deleteRecord) {
					$stmt = 'DELETE FROM %topic_files% WHERE fid = :fid LIMIT 1';
					mydb::query($stmt, ':fid', $fid);
					$result->_query[] = mydb()->_query;
				}

				$filename = cfg('folder.abs').cfg('upload_folder').'pics/'.$rs->file;

				if ($options->deleteFile && file_exists($filename) and is_file($filename)) {
					$stmt = 'SELECT COUNT(*) `total` FROM %topic_files% WHERE `file` = :file AND `fid` != :fid LIMIT 1';
					$is_photo_inused = mydb::select($stmt, $rs)->total;
					$result->_query[] = mydb()->_query;

					$result->photoInused = $is_photo_inused;
					if (!$is_photo_inused) unlink($filename);
					$result->msg = $is_photo_inused?'ภาพถูกใช้โดยคนอื่น':'ลบภาพเรียบร้อยแล้ว';
				}

				model::watch_log('photo', 'remove photo', 'Photo id '.$rs->fid.' - '.$rs->file.' was removed from topic '.$rs->tpid.' by '.i()->name.'('.i()->uid.')');
			} else if ($rs->type == 'doc') {
				if ($options->deleteRecord) {
					$stmt = 'DELETE FROM %topic_files% WHERE fid = :fid LIMIT 1';
					mydb::query($stmt, ':fid', $fid);
					$result->_query[] = mydb()->_query;
				}

				$filename = cfg('paper.upload.document.folder').$rs->file;
				if ($options->deleteFile && file_exists($filename) and is_file($filename)) {
					unlink($filename);
				}

				model::watch_log('photo', 'remove doc', 'File id '.$rs->fid.' - '.$rs->file.' was removed from topic '.$rs->tpid.' by '.i()->name.'('.i()->uid.')');
			}
		}
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