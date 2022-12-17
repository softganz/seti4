<?php
/**
* Save Photo File
*
* @param Array $photoFiles
* @param Object $data
* @return Object $options
*/
function r_photo_upload($photoFiles, $data = NULL, $options = '{}') {
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

		$picsData->orgid = SG\getFirst($data->orgId, $data->orgid);
		$picsData->uid = SG\getFirst($data->uid,i()->uid);
		$picsData->file = $photo_upload;
		$picsData->refid = SG\getFirst($data->refId, $data->refid);
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
				$picsData->photo = $photo = CommonModel::get_photo_property($upload->filename);

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
					$ui->add('<a class="sg-action -no-print" href="'.url($deleteurl.$fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -material -gray">cancel</i></a>');
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
					$ui->add('<a class="sg-action -no-print" href="'.url($deleteurl.$fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -material -gray">cancel</i></a>');
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
?>