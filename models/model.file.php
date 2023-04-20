<?php
/**
* Model   :: File Model
* Created :: 2021-12-21
* Modify  :: 2023-04-20
* Version :: 3
*
* @return Object
*
* @usage new FileModel()
* @usage FileModel::method()
*/

class FileModel {
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
			'info' => mydb::clearProp($rs),
			'property' => $rs->type == 'photo' ? FileModel::photoProperty($rs->fileName) : ($rs->type == 'doc' ? FileModel::docProperty($rs->fileName) : NULL),
		];

		return $result;
	}

	public static function items($attributes = []) {
		$defaults = [
			'nodeId' => NULL,
			'type' => NULL,
			'refId' => NULL,
			'orgId' => NULL,
			'tagName' => NULL,
			'tagNameLike' => NULL,
			'orderBy' => NULL,
			'resultGroup' => NULL,
		];

		$result = (Object) [
			'count' => 0,
			'items' => [],
		];

		$attributes = (Object) array_replace_recursive($defaults, $attributes);

		if ($attributes->nodeId) mydb::where('f.`tpid` = :nodeId', ':nodeId', $attributes->nodeId);
		if ($attributes->type) mydb::where('f.`type` = :type', ':type', $attributes->type);
		if ($attributes->refId) mydb::where('f.`refId` = :refId', ':refId', $attributes->refId);
		if ($attributes->orgId) mydb::where('f.`orgId` = :orgId', ':orgId', $attributes->orgId);
		if ($attributes->tagName) mydb::where('f.`tagName` = :tagName', ':tagName', $attributes->tagName);
		if ($attributes->tagNameLike) mydb::where('f.`tagName` LIKE :tagNameLike', ':tagNameLike', $attributes->tagNameLike);

		mydb::value('$ORDER$', '');
		if ($attributes->orderBy) mydb::value('$ORDER$', 'ORDER BY '.$attributes->orderBy, false);

		$queryOption = [];
		if ($attributes->resultGroup) $queryOption['group'] = $attributes->resultGroup;

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
			$ORDER$;
			'.($queryOption ? '-- '.json_encode($queryOption) : '')
			// Must json_encode for json single line
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

		$deleteUrl = SG\getFirst($data->deleteUrl, $data->deleteurl);
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
			if (!in_array($ext, array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'))) {
				$result->error[] = 'Upload error : Invalid File Type ('.$postFile['name'].')<br />';
				continue;
			}

			if (in_array($ext, ['pdf', 'doc', 'docx'])) {
				// Upload document file
				$uploadFolder = cfg('paper.upload.document.folder');
				$upload = new classFile($postFile, $uploadFolder);
			} else {
				// Upload image file
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

			$picsData = (Object) [
				'fid' => SG\getFirst($data->fid),
				'nodeId' => SG\getFirst($data->nodeId, $data->tpid),
				'tpid' => SG\getFirst($data->nodeId, $data->tpid),
				'cid' => SG\getFirst($data->cid),
				'type' => $ext == 'pdf' ? 'doc' : 'photo',
				'title' => SG\getFirst($data->title, $postFile['name']),
				'tagName' => SG\getFirst($data->tagName, $data->tagname),
				'orgId' => SG\getFirst($data->orgId, $data->orgid),
				'uid' => SG\getFirst($data->uid,i()->uid),
				'file' => $photo_upload,
				'refId' => SG\getFirst($data->refId, $data->refid),
				'description' => SG\getFirst($data->description),
				'timestamp' => 'func.NOW()',
				'ip' => ip2long(GetEnv('REMOTE_ADDR')),
			];

			$linkInfo = '';

			if ($upload->copy()) {
				//$ret.='<p>Upload file '.$postFile['name'].' save complete.</p>';

				mydb::query(
					'INSERT INTO %topic_files%
					(
						`fid`
					, `tpid`
					, `cid`
					, `type`
					, `orgId`
					, `uid`
					, `refId`
					, `tagName`
					, `file`
					, `title`
					, `description`
					, `timestamp`
					, `ip`
					) VALUES (
					  :fid
					, :nodeId
					, :cid
					, :type
					, :orgId
					, :uid
					, :refId
					, :tagName
					, :file
					, :title
					, :description
					, :timestamp
					, :ip
					) ON DUPLICATE KEY UPDATE
					`file` = :file',
					$picsData
				);

				if (empty($picsData->fid)) $fileId = $picsData->fid = mydb()->insert_id;

				$result->_query[] = mydb()->_query;


				if ($picsData->type == 'photo') {
					$picsData->photo = $photo = BasicModel::get_photo_property($upload->filename);

					if ($data->link == 'href') {
						$uploadUrl = url('project/'.$data->tpid.'/info.photo/'.$fileId);
						$linkInfo .= '<a class="sg-action" data-rel="box" href="'.$uploadUrl.'" data-width="840" data-height="80%">';
					} else {
						$uploadUrl = $photo->_url;
						$linkInfo .= '<a class="sg-action" data-rel="img" data-group="photo" href="'.$photo->_url.'" title="">';
					}

					$linkInfo .= '<img class="photoitem" src="'.$photo->_url.'" alt="" width="100%" />';
					$linkInfo .= '</a>';
					if ($options->showDetail) $linkInfo .= '<span class="photodetail">คำอธิบายภาพ</span>';

					$ui = new Ui('span');
					if ($deleteUrl) {
						$ui->add('<a class="sg-action -no-print" href="'.url($deleteUrl.$fileId).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-done="remove:parent li"><i class="icon -material -gray">cancel</i></a>');
					}
					$linkInfo .= '<nav class="nav -icons -hover">'.$ui->build().'</nav>'._NL;
				} else {
					$uploadUrl = cfg('paper.upload.document.url').$upload->filename;
					$linkInfo .= '<a href="'.$uploadUrl.'" target="_blank">'
						. '<img class="photoitem -doc" src="//img.softganz.com/icon/icon-file.png" width="63" />'
						. '<span class="title">'.$picsData->title.'</span>'
						. '</a>';
					$ui = new Ui('span');
					if ($deleteUrl) {
						$ui->add('<a class="sg-action -no-print" href="'.url($deleteUrl.$fileId).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-done="remove:parent li"><i class="icon -material -gray">cancel</i></a>');
					}
					$linkInfo .= '<nav class="nav -icons -hover">'.$ui->build().'</nav>'._NL;
				}

				$picsData->link = $linkInfo;
				$picsData->_FILES = $postFile;
				$result->items[] = $picsData;
			} else {
				$result->error[] = 'Upload error : Cannot save upload file ('.$postFile['name'].')<br />';
			}
			$result->link .= $linkInfo.'</li><li id="photo-'.$fileId.'" class="ui-item -hover-parent">';
		}


		if ($result->link)
			$result->link = rtrim($result->link,'</li><li id="photo-'.$fileId.'" class="ui-item -hover-parent">');

		$result->photofile = $photoFiles;
		$result->uploadfile = $uploadPhotoFiles;
		return $result;
	}

	/**
	* Delete File
	*
	* @param Int $fileId
	* @param Object $options
	* @return String
	*/
	public static function delete($fileId, $options = '{}') {
		$defaults = '{debug: false, deleteRecord: true, deleteFile: true}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) [
			'photoInused' => false,
			'msg' => NULL,
			'_query' => [],
		];


		$rs = mydb::select(
			'SELECT * FROM %topic_files% f WHERE f.`fid` = :fid LIMIT 1',
			[':fid' => $fileId]
		);

		$result->_query[] = mydb()->_query;

		if ($rs->file) {
			if ($rs->type == 'photo') {
				if ($options->deleteRecord) {
					$stmt = 'DELETE FROM %topic_files% WHERE fid = :fid LIMIT 1';
					mydb::query($stmt, ':fid', $fileId);
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

				BasicModel::watch_log('photo', 'remove photo', 'Photo id '.$rs->fid.' - '.$rs->file.' was removed from topic '.$rs->tpid.' by '.i()->name.'('.i()->uid.')');
			} else if ($rs->type == 'doc') {
				if ($options->deleteRecord) {
					$stmt = 'DELETE FROM %topic_files% WHERE fid = :fid LIMIT 1';
					mydb::query($stmt, ':fid', $fileId);
					$result->_query[] = mydb()->_query;
				}

				$filename = cfg('paper.upload.document.folder').$rs->file;
				if ($options->deleteFile && file_exists($filename) and is_file($filename)) {
					unlink($filename);
				}

				BasicModel::watch_log('photo', 'remove doc', 'File id '.$rs->fid.' - '.$rs->file.' was removed from topic '.$rs->tpid.' by '.i()->name.'('.i()->uid.')');
			}
		}
		return $result;
	}

	public static function photoProperty($file, $folder = NULL) {
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
			$property->src = _URL.cfg('upload_folder').'pics/'.$folderName.sg_urlencode($fileName);
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

	public static function docProperty($file, $folder = NULL) {
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
			$property->src = _URL.cfg('upload_folder').$subFolder.$folderName.sg_urlencode($fileName);
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