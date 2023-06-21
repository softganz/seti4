<?php
/**
* Save Document File
*
* @param Array $docFiles
* @param Object $data
* @return Object $options
*/
function r_doc_upload($docFiles, $data = NULL, $options = '{}') {
	$defaults = '{debug: false, removeOldFile: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$useSourceFilename = $options->useSourceFilename;

	$result = (Object) [
		'link' => NULL,
		'items' => [],
	];

	$uploadFolder = cfg('paper.upload.document.folder');
	$docPrename = \SG\getFirst($data->prename, 'paper_'.$data->tpid.'_');
	$docFilenameLength = \SG\getFirst($options->fileNameLength, 30);
	$isUploadSingleFile = true;

	$deleteurl = $data->deleteurl;
	//$ret='Upload document of orgid '.$orgid.' tagName='.$tagName.' docPrename '.$docPrename.'<br />';

	if ($debug) {
		debugMsg($options, '$options');
		debugMsg($docFiles, '$docFiles');
	}

	// Multidoc file upload
	if (is_array($docFiles['name'])) {
		$isUploadSingleFile = false;
		foreach ($docFiles['name'] as $key => $value) {
			$uploadDocFiles[$key] = array(
				'name' => $docFiles['name'][$key],
				'type' => $docFiles['type'][$key],
				'tmp_name' => $docFiles['tmp_name'][$key],
				'error' => $docFiles['error'][$key],
				'size' => $docFiles['size'][$key],
			);
		}
	} else {
		$uploadDocFiles[] = $docFiles;
	}

	//$ret.=print_o(post(),'post').print_o($uploadDocFiles,'$uploadDocFiles');

	foreach ($uploadDocFiles as $postFile) {
		//$ret.=print_o($postFile,'$postFile');
		if (!is_uploaded_file($postFile['tmp_name'])) {
			$result->error[] = 'Upload error : No upload file ('.$postFile['name'].')<br />';
			continue;
		}

		$ext = strtolower(sg_file_extension($postFile['name']));

		// Upload document
		$upload = new classFile($postFile, $uploadFolder, cfg('topic.doc.file_ext'));
		//debugMsg($upload, '$upload');
		if (!$upload->valid_extension()) {
			$result->error[] = 'Upload error : Invalid document format ('.$postFile['name'].')';
			continue;
		}

		if ($useSourceFilename) {
			$upload->filename = $postFile['name'];
		} else {
			$upload->generate_nextfile($docPrename, $docFilenameLength);
		}
		$doc_upload = $upload->filename;

		$docData = (Object) [
			'fid' => empty($data->fid) ? NULL : $data->fid,
			'tpid' => \SG\getFirst($data->nodeId, $data->tpid),
			'cid' => empty($data->cid) ? NULL : $data->cid,
			'type' => 'doc',
			'title' => empty($data->title) ? $postFile['name'] : $data->title,
			'tagname' => $data->tagname,
			'orgid' => $data->orgid,
			'uid' => i()->uid,
			'file' => $doc_upload,
			'refid' => $data->refid,
			'description' => $data->description,
			'timestamp' => 'func.NOW()',
			'ip' => ip2long(GetEnv('REMOTE_ADDR')),
		];

		$linkInfo = '';

		if ($upload->copy()) {
			//$ret.='<p>Upload file '.$postFile['name'].' save complete.</p>';
			if ($options->removeOldFile && $docData->fid) {
				$oldFile = mydb::select('SELECT `file` FROM %topic_files% WHERE `fid` = :fid LIMIT 1', [':fid' => $docData->fid])->file;
				if ($oldFile && $oldFile != $docData->file) {
					unlink($uploadFolder.$oldFile);
				}
			}

			mydb::query(
				'INSERT INTO %topic_files%
				(
					`fid`
				, `tpid`
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
				`file` = :file',
				$docData
			);
			if (empty($docData->fid)) $fid = $docData->fid = mydb()->insert_id;

			$result->_query[] = mydb()->_query;


			$uploadUrl = cfg('paper.upload.document.url').$upload->filename;
			$linkInfo .= '<a href="'.$uploadUrl.'"><img class="doc-logo -pdf" src="http://img.softganz.com/icon/pdf-icon.png" /><span class="-title">'.$docData->title.'</span></a>';
			$ui = new Ui('span');
			if ($deleteurl) {
				$ui->add('<a class="sg-action" href="'.url($deleteurl.$fid).'" title="ลบไฟล์นี้" data-confirm="ยืนยันว่าจะลบไฟล์นี้?" data-rel="notify" data-removeparent="li"><i class="icon -material">cancel</i></a>');
			}
			$linkInfo .= '<nav class="nav iconset -hover -no-print">'.$ui->build().'</nav>';

			$docData->link = $linkInfo;
			$docData->_FILES = $postFile;
			$result->items[] = $docData;
		} else {
			$result->error[] = 'Upload error : Cannot save upload file ('.$postFile['name'].')<br />';
		}
		$result->link .= $linkInfo.'</li><li class="ui-item -hover-parent">';
	}


	if ($result->link)
		$result->link = rtrim($result->link,'</li><li class="ui-item -hover-parent">');

	$result->docfile = $docFiles;
	$result->uploadfile = $uploadDocFiles;
	return $result;
}
?>