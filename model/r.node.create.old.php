<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

// TODO : NODE CREATE IS NOT COMPLETE

function r_node_create($data, $options = '{}') {
	$defaults = '{debug: false; simulate: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$result->tpid = NULL;
	$result->complete = false;
	$result->error = false;

	$error = $field_missing = $document_error = array();

	$result->process[] = 'r_node_create '.($options->simulate ? '<strong>simulation</strong> ' : '').'request';

	// do external module post form
	// TODO : Call onNodeCreate of module
	/*
	if ($data->type->module!='paper') do_class_method($data->type->module,'__post_check',$this,$topic,$result);
	*/

	//Save Upload file document
	// TODO : Do it later
	/*
	if (user_access('upload document') && is_uploaded_file($_FILES['document']['tmp_name'])) {
		$document=(object)$_FILES['document'];

		$upload_folder=cfg('paper.upload.document.folder');
		$document->_property=sg_explode_filename($upload_folder.$document->name,'doc');
		$document->title = SG\getFirst($data->post->document_title,sg_explode_filename($upload_folder.$document->name)->basename);
		$document->name=$document->_property->name;

		if (cfg('topic.doc.file_ext')) {
			if (!in_array($document->_property->ext,cfg('topic.doc.file_ext'))) $document_error[]='Invalid file format : Only <strong>'.implode(',',cfg('topic.doc.file_ext')).'</strong> file can be send.';
		} else if (cfg('topic.doc.file_type')) {
			if (!in_array($document->type,cfg('topic.doc.file_type'))) $document_error[]='Invalid file format';
		}

		// check duplicate filename and rename to next number
		if (file_exists($document->_property->location) && is_file($document->_property->location)) {
			$new_filename=sg_generate_nextfile($upload_folder,'doc',$document->_property->ext);
			$document->_property=sg_explode_filename($new_filename);
			$document->name=$document->_property->name;
		}

		if ($document_error) {
			$error[]='Document upload error : <ul><li>'.implode('</li><li>',$document_error).'</li></ul>';
		} else {
			$document->file = $document->name;
		}
		$result->document=$document;
	}
	*/

	// Save upload video
	// TODO : Do it later
	/*
	if (cfg('topic.video.allow') && user_access('upload video') && is_uploaded_file($_FILES['video']['tmp_name'])) {
		$video=(object)$_FILES['video'];

		$folder=sg_user_folder(i()->username);
		$video->_property=sg_explode_filename($folder.$video->name,'flv');
		$video->name=$video->_property->name;

		if (!(in_array($video->_property->ext,array('flv','f4v')) || in_array($video->type,array('video/x-flv')))) $video_error[]='Invalid video format. Support FLV video format only.';

		// check duplicate filename and rename to next number
		if (file_exists($video->_property->location) && is_file($video->_property->location)) {
			$new_filename=sg_generate_nextfile($folder,'flv',$video->_property->ext);
			$video->_property=sg_explode_filename($new_filename);
			$video->name=$video->_property->name;
		}

		if ($video_error) {
			$error[]='Video upload error : <ul><li>'.implode('</li><li>',$video_error).'</li></ul>';
		} else {
			$video->file = $video->name;
		}
		$result->video=$video;
	}
	*/


	$result->process[]='Complete of check invalid input';

	if ($error) {
		$result->process[] = 'There is invalid input';
		$result->error = message('error',$error);
		return $result;
	}




	// START CREATE NODE DATA
	$result->process[] = 'Start <strong>saving</strong> process';

	$topicType = mydb::clearprop(model::get_topic_type('icar'));

	$data->type = SG\getFirst($data->type,'');

	$data->promote = $data->promote ? 1 : 0;
	/*
	if ($data->require_approve == 'yes' || !user_access('post paper without approval')) {
		$data->status = _WAITING;
	} else if ($_POST['draft']) {
		$data->status = _DRAFT;
	} else {
		$data->status = _PUBLISH;
	}
	*/

	$data->status = SG\getFirst($data->status,_PUBLISH);

	$data->created = $data->timestamp = date('Y-m-d H:i:s');
	$data->ip = SG\getFirst(ip2long(GetEnv('REMOTE_ADDR')));
	$data->uid = i()->uid;
	if ($data->poster === i()->name) unset($data->poster);
	$data->comment = SG\getFirst($data->comment, _COMMENT_READWRITE);

	// topic poperty
	// TODO : Do it later
	/*
	$data->post->property=(object)$data->post->property;
	if (!isset($data->post->property->show_photo)) $data->post->property->show_photo=cfg('topic.property.show_photo');
	if (!user_access('administer contents') && $data->post->property->input_format=='php') unset($data->post->property->input_format);
	$data->post->property=serialize($data->post->property);
	*/


	$data->revid = NULL;

	$error = NULL;

	// check for clear sticky
	// TODO : Do it later
	/*
	if ($_POST['clear_sticky'] && $data->post->sticky && user_access('administer contents,administer papers')) {
		$sticky=cfg('sticky');
		$result->process[]='Clear sticky of '.$sticky[$data->post->sticky];
		$sql_cmd='UPDATE %topic% SET sticky=0 WHERE sticky='.$data->post->sticky;
		mydb::query($sql_cmd,$simulate);
		$result->process[]=mydb()->_query;
	}
	*/

	// save title into topic
	$stmt = mydb::create_insert_cmd('%topic%',$data);
	mydb::query($stmt,$data);

	$result->process[]=mydb()->_query;

	if (mydb()->_error) {
		$error['topic'] = 'Error on create topic query command'.(user_access('access debugging program')?'<br />'.mydb()->_error:'');
		return $result;
	}




	$tpid = $result->tpid = $data->tpid = mydb()->insert_id;

	if ( $simulate ) {
		$tpid = $data->tpid = $data->post->tpid = db_query_one_cell('SELECT MAX(tpid)+1 from %topic%');
	}

	// save detail into revision and update revision in topic
	$stmt = mydb::create_insert_cmd('%topic_revisions%',$data);

	mydb::query($stmt,$data);

	if (mydb()->_error) $error['detail'] = 'Error on create detail query command'.(user_access('access debugging program')?'<br />'.mydb()->_error:'');

	$result->process[]=mydb()->_query;

	$revid = mydb()->insert_id;

	// update revision id into reference topic
	$stmt = 'UPDATE %topic% SET `revid` = :revid WHERE tpid = :tpid LIMIT 1';
	mydb::query($stmt, ':revid', $revid, ':tpid',$tpid);
	$result->process[] = mydb()->_query;





	// add taxonomy into topic_tag table
	// TODO : Do it later
	/*
	if ($data->post->taxonomy) {
		$taxonomy = $data->post->taxonomy;
		if (array_key_exists('tags',$taxonomy)) {
			$tags=$taxonomy['tags'];
			unset($taxonomy['tags']);
		} else $tags=array();
		foreach ($taxonomy as $vid=>$tids) {
			if (is_array($tids)) {
				foreach ($tids as $tid) if (!empty($tid)) $topic_tag[$tid]=$tpid .' , '.$vid.' , '. $tid;
			} else if (!empty($tids)) $topic_tag[$tids]=$tpid .' , '.$vid.' , '. $tids;
		}
		foreach ($tags as $vid=>$tag_desc) {
			if (empty($tag_desc)) continue;
			foreach (explode(',',$tag_desc) as $tag_name) {
				$tag_name=trim($tag_name);
				$tag_db=db_query_one_cell('SELECT tid FROM %tag% WHERE vid='.$vid.' AND name="'.addslashes($tag_name).'" LIMIT 1');
				$result->process[]=db_query_cmd();
				$tid =  $tag_db ? $tag_db : model::add_taxonomy($vid,$tag_name);
				$topic_tag[$tid] = $tpid .' , '.$vid.' , '. $tid;
			}
		}
		if ($topic_tag) {
			mydb::query('INSERT INTO %tag_topic% ( `tpid` , `vid` , `tid` ) VALUES ( ' . implode(' ) , ( ',$topic_tag) .' ) ',$simulate);
			$result->process[]=mydb()->_query;
			$tags=mydb::query('SELECT tid,name FROM %tag% WHERE tid IN ('.implode(',',array_keys($topic_tag)).')');
			$data->tags=$tags->items;
		}
		$result->process[] = print_o($taxonomy, '$taxonomy');
	}
	*/





	// Save upload photo to folder and add to table
	// TODO : Do it later
	/*
	if (user_access('upload photo') && !$error) {
		$desc=(object)post('topic[photo_desc]',_TRIM+_STRIPTAG);
		$desc->tpid=$tpid;
		$desc->cid=0;
		$desc->type='photo';
		$desc->timestamp='func.NOW()';
		$desc->ip = ip2long(GetEnv('REMOTE_ADDR'));
		$desc->uid = i()->uid;

		$photos= array();
		// convert multiple upload file to each upload file
		if (is_string($_FILES['photo']['name'])) {
			$photos[]=(object)$_FILES['photo'];
		} elseif (is_array($_FILES['photo']['name'])) {
			foreach ($_FILES['photo']['name'] as $key=>$name) {
				$photos[$key]->name=$_FILES['photo']['name'][$key];
				$photos[$key]->type=$_FILES['photo']['type'][$key];
				$photos[$key]->tmp_name=$_FILES['photo']['tmp_name'][$key];
				$photos[$key]->error=$_FILES['photo']['error'][$key];
				$photos[$key]->size=$_FILES['photo']['size'][$key];
			}
		}

		$uploads=array();
		foreach ($photos as $photo) {
			if (!is_uploaded_file($photo->tmp_name)) continue;
			$photo_result=$result->photo[]=module::__save_upload_photo_file($photo,null,$simulate);
			if ($photo_result->complete && $photo_result->save->_file) {
				$desc->file=$photo_result->save->_file;
				$sql_cmd = mydb::create_insert_cmd('%topic_files%',$desc);
				mydb::query($sql_cmd,$desc);
				$result->process[]=mydb()->_query;
			}
		}
	}

	// Save document file to folder and add to table
	if ($document->file && !$error) {
		if (!$simulate) {
			if (copy($document->tmp_name,$document->_property->location)) {
				if (cfg('upload.file.chmod')) chmod($document->dest,cfg('upload.file.chmod'));
			}
			$document->tpid = $tpid;
			$document->cid = 0;
			$document->type = 'doc';
			$document->description = $data->post->document_description;
			$document->timestamp='func.NOW()';
			$document->ip = ip2long(GetEnv('REMOTE_ADDR'));
			$document->uid = i()->uid;
			$sql_doc = mydb::create_insert_cmd('%topic_files%',$document);
			$result->process[]='Saving upload document to '.$document->dest;
			mydb::query($sql_doc,$document);
			$result->process[]=mydb()->_query;
			$result->document=$document;
		}
	}

	if (cfg('topic.video.allow') && $video->file && !$error) {
		if (!$simulate) {
			if (copy($video->tmp_name,$video->_property->location)) {
				if (cfg('upload.file.chmod')) chmod($video->_property->location,cfg('upload.file.chmod'));
			}
			$video->tpid = $tpid;
			$video->cid = 0;
			$video->type = 'movie';
			$video->title = $data->post->document_title;
			$video->description = $data->post->document_description;
			$video->timestamp='func.NOW()';
			$video->ip = ip2long(GetEnv('REMOTE_ADDR'));
			$video->uid = i()->uid;
			$sql_doc = mydb::create_insert_cmd('%topic_files%',$video);
			$result->process[]='Saving upload document to '.$video->_property->location;
			mydb::query($sql_doc,$video);
			$result->process[]=mydb()->_query;
			$result->video=$video;
		}
	}
	*/


	if ($error) {
		$result->error = 'Create topic error : <ul><li>'.implode('</li><li>',(array)$error).'</li></ul>';
	} else {
		$result->complete = true;
	}
	$result->process[] = 'r_node_create :: save_topic complete';

	return $result;
}
?>