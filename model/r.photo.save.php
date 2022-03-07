<?php
/**
* Save upload photo file to upload folder
* Created 2019-05-24
* Modify  2019-05-24
*
* @param Object $conditions
* @param String $folder
* @param Object $options
* @return Object $result
*/

$debug = true;

function r_photo_save($upload, $folder = NULL, $options = '{}') {
	$defaults = '{debug: false, simulate: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;
	$is_simulate = $options->simulate;

	$result = NULL;

	$result->error = false;
	$result->complete = false;

	$result->process[] = 'Model photo_save '.($is_simulate?'simulation ':'').'request';

	$maxFileSize = SG\getFirst($upload->maxFileSize,cfg('photo.max_file_size'));
	$resizeWidth = SG\getFirst($upload->resizeWidth,cfg('photo.resize.width'));
	$resizeQuality = SG\getFirst($upload->resizeQuality,cfg('photo.resize.quality'));

	if (empty($folder)) $folder = cfg('paper.upload.photo.folder');
	$upload->_property = sg_explode_filename($folder.$upload->name,'pic');

	if (!is_uploaded_file($upload->tmp_name)) $result->error[] = 'No upload file';
	if ($upload->error) $result->error[] = 'upload file error';
	if (empty($upload->tmp_name)) $result->error[] = 'Invalid temporary name';
	if ($upload->size <= 0) $result->error[] = 'empty file size';
	if (!(in_array($upload->type,cfg('photo.file_type')) || ($upload->type=='application/octet-stream' && $upload->_property->ext=='jpg')) ) $result->error[] = 'Invalid file format';

	if ($result->error) return $result;


	$result->process[]='Start saving upload file <em>'.$upload->name.'</em> to folder <em>'.$folder.'</em>';

	// if sender is admin , do not resize picture file
	//		if ( !user_access('administer contents,administer papers') &&
	if ($upload->size > $maxFileSize*1024) {
		sg_photo_resize($upload->tmp_name,$resizeWidth,NULL,NULL,true,$resizeQuality);
		$result->process[] = 'Resize photo file to '.$resizeWidth.' pixel';
	}

	$is_copynewfile = true;
	if (file_exists($upload->_property->location)) {
		// check duplicate file
		$old_filesize = filesize($upload->_property->location);
		$new_filesize = filesize($upload->tmp_name);
		$result->process[] = 'Check file size of old file '.$old_filesize.' bytes and new file '.$new_filesize.' bytes';
		if ($new_filesize != $old_filesize) {
			$new_filename = $upload->overwrite?$folder.$upload->name:sg_generate_nextfile($folder,'pic',$upload->_property->ext);
			$upload->_property = sg_explode_filename($new_filename);
			$result->process[] = 'Set upload file to new name <em>'.$upload->_property->name.'</em>';
		} else $is_copynewfile = false;
	}

	$result->save = sg_clone($upload);
	$result->save->_file = $upload->_property->name;
	$result->save->_location = $upload->_property->location;
	if ( $is_copynewfile ) {
		$result->process[] = 'Save upload file to <em>'.$result->save->_file.'</em>';
		if (!$is_simulate) {
			if (copy($upload->tmp_name, $result->save->_location)) {
				// change mode to config->upload.file.chmod
				if (cfg('upload.file.chmod')) chmod($result->save->_location,cfg('upload.file.chmod'));
				$result->save->type = 'new';
			} else $result->error[] = 'Save upload error';
		}
	} else {
		$result->process[] = 'Upload file <em>'.$result->save->_file.'</em> is same old file , no need to save new file';
		$result->save->type = 'same';
	}

	$result->complete = true;
	$result->process[] = 'Model photo_save complete';
	return $result;
}
?>