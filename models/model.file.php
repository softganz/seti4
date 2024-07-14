<?php
/**
* Model   :: File Model
* Created :: 2021-12-21
* Modify  :: 2024-07-01
* Version :: 6
*
* @return Object
*
* @usage new FileModel()
* @usage FileModel::method()
*/

use Softganz\DB;

class FileModel {
	/**
	 * @param Int/Array $fileId
	 * @return Object/NULL
	 */
	public static function get($fileId) {
		if (is_array($fileId)) {
			$nodeId = $fileId['nodeId'];
			$fileId = $fileId['fileId'];
		}

		if (empty($fileId)) return NULL;

		$rs = DB::select([
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
			, f.`folder`
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
			, f.`last_download` `lastDownloadDate`
			, f.`timeStamp`
			, f.`ip`
			FROM %topic_files% f
			%WHERE%
			LIMIT 1',
			'where' => [
				'%WHERE%' => [
					['`fid` = :fileId', ':fileId' => $fileId],
					$nodeId ? ['`tpid` = :nodeId', ':nodeId' => $nodeId] : NULL,
				],
			],
		]);

		if (empty($rs->id)) return NULL;

		$result = (Object) [
			'fileId' => $rs->id,
			'fileName' => $rs->fileName,
			'folder' => $rs->folder,
			'title' => $rs->title,
			'info' => $rs,
			'property' => $rs->type == 'photo' ? FileModel::photoProperty($rs->fileName, $rs->folder) : ($rs->type == 'doc' ? FileModel::docProperty($rs->fileName, $rs->folder) : NULL),
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
			, f.`folder`
			, f.`file` `fileName`
			, f.`title`
			, f.`description`
			, f.`comment` `commentCount`
			, f.`votes` `voteCount`
			, f.`view` `viewCount`
			, f.`last_view` `lastViewDate`
			, f.`reply` `replyCount`
			, f.`download` `downloadCount`
			, f.`last_download` `lastDownloadDate`
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
		$defaults = '{debug: false, showDetail: true, useSourceFilename: false, fileNameLength: 30, showDetail: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$useSourceFilename = $options->useSourceFilename;

		$data = (Object) array_merge(
			[
				'nodeId' => NULL, // Int
				'folder' => NULL, // String
				'preName' => NULL, // String
				'deleteUrl' => NULL, // String,
				'fileId' => NULL, // Int,
				'cid' => NULL, // Int
				'title' => NULL, // String
				'tagName' => NULL, // String
				'orgId' => NULL, // Int
				'uid' => NULL, // Int
				'refId' => NULL, // Int
				'link' => NULL, // String
				'description' => NULL, // String
				'onComplete' => function($data) {}
			],
			(Array) $data
		);

		$data->nodeId = SG\getFirst($data->nodeId, $data->tpid);

		if ($data->folder && !preg_match('/\//$', $data->folder)) $data->folder .= '/';

		$result = (Object) [
			'link' => NULL,
			'photofile' => NULL,
			'uploadfile' => NULL,
			'error' => [],
			'items' => [],
			'_query' => [],
		];

		$photoPrename = SG\getFirst($data->preName, $data->prename, 'paper_'.$data->nodeId.'_');
		$photoFilenameLength = SG\getFirst($options->fileNameLength, 30);
		$isUploadSingleFile = true;

		$deleteUrl = SG\getFirst($data->deleteUrl, $data->deleteurl);
		// debugMsg('Upload photo of orgId '.$data->orgId.' tagName='.$data->tagName.' photoPrename '.$photoPrename);


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

		$docExtension = ['pdf', 'doc', 'docx'];
		$photoExtension = ['jpg', 'jpeg', 'png'];
		foreach ($uploadPhotoFiles as $postFile) {
			//debugMsg($postFile,'$postFile');
			if (!is_uploaded_file($postFile['tmp_name'])) {
				$result->error[] = 'Upload error : No upload file ('.$postFile['name'].')<br />';
				continue;
			}

			$ext = strtolower(sg_file_extension($postFile['name']));

			if (!in_array($ext, $docExtension) && !in_array($ext, $photoExtension)) {
				$result->error[] = 'Upload error : Invalid File Type ('.$postFile['name'].')<br />';
				continue;
			}

			// Create folder if not exists
			if ($data->folder && !(file_exists($data->folder) && !is_file($data->folder))) {
				mkdir($data->folder);
			}

			if (in_array($ext, $docExtension)) {
				// Upload document file
				$uploadFolder = SG\getFirst($data->folder, cfg('paper.upload.document.folder'));
				$upload = new classFile($postFile, $uploadFolder);
			} else if (in_array($ext, $photoExtension)) {
				// Upload image file
				$uploadFolder = SG\getFirst($data->folder, cfg('paper.upload.photo.folder'));
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
				'fileId' => SG\getFirst($data->fileId, $data->fid),
				'nodeId' => $data->nodeId,
				'tpid' => $data->nodeId,
				'cid' => SG\getFirst($data->cid),
				'type' => in_array($ext, $docExtension) ? 'doc' : 'photo',
				'title' => SG\getFirst($data->title, $postFile['name']),
				'tagName' => SG\getFirst($data->tagName, $data->tagname),
				'folder' => SG\getFirst($data->folder),
				'orgId' => SG\getFirst($data->orgId, $data->orgid),
				'userId' => SG\getFirst($data->userId, $data->uid,i()->uid),
				'file' => $photo_upload,
				'refId' => SG\getFirst($data->refId, $data->refid),
				'description' => SG\getFirst($data->description),
				'timestamp' => 'func.NOW()',
				'ip' => ip2long(GetEnv('REMOTE_ADDR')),
				'link' => NULL,
			];

			$linkInfo = '';

			if (!$upload->copy()) {
				$result->error[] = 'Upload error : Cannot save upload file ('.$postFile['name'].')<br />';
				continue;
			}

			//$ret.='<p>Upload file '.$postFile['name'].' save complete.</p>';

			mydb::query(
				'INSERT INTO %topic_files%
				(
					`fid`, `tpid`, `cid`, `type`, `orgId`, `uid`, `refId`
				, `tagName`
				, `folder`, `file`
				, `title`, `description`
				, `timestamp`, `ip`
				) VALUES (
				  :fileId, :nodeId, :cid, :type, :orgId, :userId, :refId
				, :tagName
				, :folder, :file
				, :title, :description
				, :timestamp, :ip
				) ON DUPLICATE KEY UPDATE
				`file` = :file',
				$picsData
			);

			if (empty($picsData->fileId)) $fileId = $picsData->fileId = mydb()->insert_id;

			$result->_query[] = mydb()->_query;


			if ($picsData->type == 'photo') {
				$picsData->photo = $photo = FileModel::photoProperty($upload->filename, $data->folder);

				if ($data->link == 'href') {
					$uploadUrl = url('project/'.$data->nodeId.'/info.photo/'.$fileId);
					$linkInfo .= '<a class="sg-action" data-rel="box" href="'.$uploadUrl.'" data-width="840" data-height="80%">';
				} else {
					$uploadUrl = $photo->url;
					$linkInfo .= '<a class="sg-action" data-rel="img" data-group="photo" href="'.$photo->url.'" title="">';
				}

				$linkInfo .= '<img class="photoitem -photo" src="'.$photo->url.'" alt="" width="100%" />';
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
			$result->link .= $linkInfo.'</li><li id="photo-'.$fileId.'" class="ui-item -item -hover-parent">';
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

		$fileInfo = FileModel::get($fileId);

		if (empty($fileInfo->fileId)) return (Object) ['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'msg' => 'File not found'];

		if ($fileInfo->info->type == 'photo') {
			// Delete file record
			if ($options->deleteRecord) {
				mydb::query('DELETE FROM %topic_files% WHERE fid = :fid LIMIT 1', [':fid' => $fileId]);
				$result->_query[] = mydb()->_query;
			}

			// Delete photo file
			if ($options->deleteFile && $fileInfo->property->exists) {
				$result->photoInused = !empty(FileModel::getFileInUse($fileId, $fileInfo->fileName, $fileInfo->folder));
				if (!$result->photoInused) unlink($fileInfo->property->name);
				$result->msg = $result->photoInused ? 'ภาพถูกใช้โดยคนอื่น' : 'ลบภาพเรียบร้อยแล้ว';
			}

			BasicModel::watch_log('photo', 'remove photo', 'Photo id '.$fileId.' - '.$fileInfo->info->file.' was removed from topic '.$fileInfo->info->tpid.' by '.i()->name.'('.i()->uid.')');
		} else if ($fileInfo->info->type == 'doc') {
			// Delete doc record
			if ($options->deleteRecord) {
				mydb::query(
					'DELETE FROM %topic_files% WHERE fid = :fid LIMIT 1',
					[':fid' => $fileId]
				);
				$result->_query[] = mydb()->_query;
			}

			// Delete doc file
			// $fileName = cfg('paper.upload.document.folder').$fileInfo->fileName;
			// $a = FileModel::docProperty($fileInfo->fileName, $fileInfo->info->folder);
			// debugMsg($a, '$a');
			// debugMsg('$fileName = '.$fileName);
			// debugMsg($fileInfo, '$fileInfo');
			if ($options->deleteFile && $fileInfo->property->exists) {
				unlink($fileInfo->property->name);
			}

			// Create log
			BasicModel::watch_log('photo', 'remove doc', 'File id '.$fileInfo->info->fid.' - '.$fileInfo->info->file.' was removed from topic '.$fileInfo->info->tpid.' by '.i()->name.'('.i()->uid.')');
		}
		return $result;
	}

	public static function getFileInUse($fileId = NULL, $fileName, $folder) {
		\mydb::where('`file` = :fileName', ':fileName', $fileName);
		if ($folder) {
			\mydb::where('`folder` = :folder', ':folder', $folder);
		} else {
			\mydb::where('`folder` IS NULL');
		}
		if ($fileId) \mydb::where('`fid` != :fileId', ':fileId', $fileId);
		return \mydb::select(
			'SELECT `fid`, `tpid` `nodeId`, `folder`, `file`
			FROM %topic_files%
			%WHERE%;
			-- {key: "fid"}'
		)->items;
	}

	public static function photoProperty($file, $folder = NULL) {
		$folder = preg_replace('/\/$/', '', $folder);

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

		// debugMsg('fileName='.$fileName.' , dirname='.$dirName.' , folder='.$folder.' , cfg(upload.url)='.cfg('upload.url').' , cfg(upload_folder) = '.cfg('upload_folder'));

		// if ($dirName) {
		// 	$property->src = $dirName.'/'.sg_urlencode($fileName);
		// 	$property->name = cfg('folder.abs').$dirName.'/'.sg_tis620_file($fileName);
		// } else if ($folder && preg_match('/^upload/', $folder)) {
		// 	$property->src = $folder.'/'.sg_urlencode($fileName);
		// 	$property->url = cfg('url.abs').$folder.'/'.sg_urlencode($fileName);
		// 	$property->name = cfg('folder.abs').$folder.'/'.sg_tis620_file($fileName);
		// } else if ($folder) {
		// 	$property->src = cfg('upload.url').$folder.'/'.sg_urlencode($fileName);
		// 	$property->name = cfg('upload.folder').$folder.'/'.sg_tis620_file($fileName);
		// } else {
		// 	$property->src = _URL.cfg('upload_folder').'pics/'.$folderName.sg_urlencode($fileName);
		// 	$property->name = cfg('folder.abs').cfg('upload_folder').'pics/'.$folderName.$fileName;
		// }

		if ($folder) {
			$property->name = cfg('folder.abs').$folder.'/'.sg_tis620_file($fileName);
			$property->src = _URL.$folder.'/'.sg_urlencode($fileName);
		} else {
			$property->name = cfg('folder.abs').cfg('upload_folder').'pics/'.$folderName.$fileName;
			$property->src = _URL.cfg('upload_folder').'pics/'.$folderName.sg_urlencode($fileName);
		}

		if (file_exists($property->name)) {
			$property->size = filesize($property->name);
			if (!isset($property->url)) $property->url = $property->src; // sg_urlencode($fileName);
			$size = getimagesize($property->name);
			$property->exists = true;
			$property->width = $size[0];
			$property->height = $size[1];
			$property->mime = $size['mime'];
		}

		return $property;
	}

	public static function docProperty($file, $folder = NULL) {
		$folder = preg_replace('/\/$/', '', $folder);
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

		// debugMsg('fileName='.$fileName.' , dirname='.$dirName.' , folder='.$folder.' , cfg(upload.url)='.cfg('upload.url').' , cfg(upload_folder) = '.cfg('upload_folder'));

		// if ($dirName) {
		// 	$property->name = cfg('folder.abs').$dirName.'/'.sg_tis620_file($fileName);
		// 	$property->src = $dirName.'/'.sg_urlencode($fileName);
		// } else if ($folder && preg_match('/^upload/', $folder)) {
		// 	$property->name = cfg('folder.abs').$folder.'/'.sg_tis620_file($fileName);
		// 	$property->src = $folder.'/'.sg_urlencode($fileName);
		// 	$property->url = cfg('url.abs').$folder.'/'.sg_urlencode($fileName);
		// } else if ($folder) {
		// 	$property->name = cfg('upload.folder').$folder.'/'.sg_tis620_file($fileName);
		// 	$property->src = cfg('upload.url').$folder.'/'.sg_urlencode($fileName);
		// } else {
		// 	$property->name = $photo_location = cfg('folder.abs').cfg('upload_folder').$subFolder.$folderName.$fileName;
		// 	$property->src = _URL.cfg('upload_folder').$subFolder.$folderName.sg_urlencode($fileName);
		// }

		if ($folder) {
			$property->name = cfg('folder.abs').$folder.'/'.sg_tis620_file($fileName);
			$property->src = _URL.$folder.'/'.sg_urlencode($fileName);
		} else {
			$property->name = $photo_location = cfg('folder.abs').cfg('upload_folder').$subFolder.$folderName.$fileName;
			$property->src = _URL.cfg('upload_folder').$subFolder.$folderName.sg_urlencode($fileName);
		}

		if (file_exists($property->name)) {
			$property->size = filesize($property->name);
			if (!isset($property->url)) $property->url = $property->src; // sg_urlencode($fileName);
			$property->exists = true;
		}

		return $property;
	}
}
?>