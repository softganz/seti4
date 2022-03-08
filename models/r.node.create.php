<?php
/**
* Paper Create
* Created 2019-06-10
* Modify  2019-06-10
*
* @param Object $topic
* @param Boolean $simulate
* @return Object $result
*/

$debug = true;

function r_node_create($topic, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = (Object) [
		'tpid' => NULL,
		'complete' => false,
		'error' => false,
		'document' => (Object) [],
		'photo' => [],
		'process' => [],
	];

	$error=$field_missing=$document_error=array();

	$result->process[]='paper_model::save_topic '.($simulate?'<strong>simulation</strong> ':'').'request';

	// Get node type on wmpty
	if (!$topic->type->type) $topic->type = model::get_topic_type($topic->post->type);

	if (sg_invalid_poster_name($topic->post->poster)) $error[]= 'Duplicate poster name';
	if (!i()->ok && !sg_valid_daykey(5,$_POST['daykey'])) $error[]='Invalid Anti-spam word';

	if (!i()->ok && empty($_POST['daykey'])) $topic->field_missing[]='Anti-spam word';
	if (empty($topic->post->title)) $topic->field_missing[]= 'Title (หัวข้อ)';
	if ($topic->type->min_word_count>0 && strlen($topic->post->body)<$topic->type->min_word_count) $topic->field_missing[]='Body (รายละเอียด อย่างน้อย '.$topic->type->min_word_count.' ตัวอักษร)';
	if (empty($topic->post->poster)) $topic->field_missing[]='Sender (ชื่อผู้ส่ง)';
	if (!i()->ok && cfg('topic.require.mail') && (empty($topic->post->email) || !sg_is_email($topic->post->email))) $topic->field_missing[] = 'E-mail - อีเมล์';
	if (!i()->ok && cfg('topic.require.homepage') && empty($topic->post->website)) $topic->field_missing[] = 'Homepage - เว็บไซท์';

	if (!user_access('upload photo') && preg_match('/\[img\]|<img|\&lt\;img|\!\[.*?\]/i',$topic->post->body)) $error[] = 'ขออภัย ท่านไม่มีสิทธิ์ในการส่งภาพ';

	// do external module post form
	R::On($topic->type->module.'.paper.post.check',$self,$topic,$result);
	//if ($topic->type->module!='paper') do_class_method($topic->type->module,'__post_check',$this,$topic,$result);


	if ($topic->field_missing) $error[]='กรุณาป้อนข้อมูลต่อไปนี้ให้ครบถ้วน : <ul><li>'.implode('</li><li>',$topic->field_missing).'</li></ul>';

	if (sg::is_spam_word($topic->post)) $error[]='มีข้อความที่ไม่เหมาะสมอยู่ในสิ่งที่ป้อนมา';

	//up load file document
	if (user_access('upload document') && is_uploaded_file($_FILES['document']['tmp_name'])) {
		$document = (Object) $_FILES['document'];

		$upload_folder=cfg('paper.upload.document.folder');
		$document->_property=sg_explode_filename($upload_folder.$document->name,'doc');
		$document->title = SG\getFirst($topic->post->document_title,sg_explode_filename($upload_folder.$document->name)->basename);
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

	// Save upload video
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


	$result->process[]='Complete of check invalid input';

	if ($error) {
		$result->process[]='There is invalid input';
		$result->error=message('error',$error);
		return $result;
	}

	$result->process[]='Start <strong>saving</strong> process';



	// start saving process
	$topic->post->type = $topic->type->type;
	$topic->post->promote=$topic->type->topic_options->promote?1:0;
	if ($topic->forum->require_approve=='yes' || !user_access('post paper without approval')) {
		$topic->post->status=_WAITING;
	} else if ($_POST['draft']) {
		$topic->post->status=_DRAFT;
	} else {
		$topic->post->status=_PUBLISH;
	}

	$topic->post->created = $topic->post->timestamp = date('Y-m-d H:i:s');
	$topic->post->ip = ip2long(GetEnv('REMOTE_ADDR'));
	if ($topic->post->ip=="") $topic->post->ip='func.NULL';
	$topic->post->uid = i()->uid;
	if ($topic->post->poster===i()->name) unset($topic->post->poster);
	$topic->post->comment=SG\getFirst($topic->post->comment,$topic->type->topic_options->comment,_COMMENT_READWRITE);

	// topic poperty
	$topic->post->property = (object) $topic->post->property;

	if (!isset($topic->post->property->show_photo)) {
		$topic->post->property->show_photo = cfg('topic.property.show_photo');
	}
	if (!user_access('administer contents') && $topic->post->property->input_format=='php') {
		unset($topic->post->property->input_format);
	}
	$topic->post->property = sg_json_encode($topic->post->property);
	$topic->post->revid = NULL;
	if (empty($topic->post->areacode)) $topic->post->areacode = NULL;

	$error=null;

	// check for clear sticky
	if ($_POST['clear_sticky'] && $topic->post->sticky && user_access('administer contents,administer papers')) {
		$sticky=cfg('sticky');
		$result->process[]='Clear sticky of '.$sticky[$topic->post->sticky];
		$sql_cmd='UPDATE %topic% SET sticky=0 WHERE sticky='.$topic->post->sticky;
		mydb::query($sql_cmd,$simulate);
		$result->process[]=mydb()->_query;
	}

	// save title into topic
	$sql_topic = mydb::create_insert_cmd('%topic%',$topic->post);
	mydb::query($sql_topic,$topic->post);



	//debugMsg(mydb()->_query);

	$result->process[]=mydb()->_query;

	if (mydb()->_error) $error['topic']='Error on create topic query command'.(user_access('access debugging program')?'<br />'.mydb()->_error:'');
	$tpid = $topic->tpid = $topic->post->tpid = mydb()->insert_id;
	$result->tpid = $tpid;

	if ( $simulate ) {
		$tpid = $topic->tpid = $topic->post->tpid = db_query_one_cell('SELECT MAX(tpid)+1 from %topic%');
	}

	// save detail into revision and update revision in topic
	$sql_detail = mydb::create_insert_cmd('%topic_revisions%',$topic->post);

	if (!isset($error['topic'])) {
		mydb::query($sql_detail,$topic->post);
		if (mydb()->_error) $error['detail']='Error on create detail query command'.(user_access('access debugging program')?'<br />'.mydb()->_error:'');
		$result->process[]=mydb()->_query;

		$revid=mydb()->insert_id;

		// update revision id into reference topic
		$sql_cmd = 'UPDATE %topic% SET revid='.$revid.' WHERE tpid='.$tpid.' LIMIT 1';
		mydb::query($sql_cmd);
		$result->process[]=mydb()->_query;
	}

	// add taxonomy into topic_tag table
	if ($topic->post->taxonomy) {
		$topic_tag = array();
		$taxonomy=$topic->post->taxonomy;
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
				$tag_db = mydb::select('SELECT tid FROM %tag% WHERE `vid` = :vid AND `name` = :name LIMIT 1', ':vid', $vid, ':name', $tag_name)->tid;
				$result->process[] = mydb()->_query;
				$tid =  $tag_db ? $tag_db : model::add_taxonomy($vid,$tag_name);
				$topic_tag[$tid] = $tpid .' , '.$vid.' , '. $tid;
			}
		}

		$result->process[] = print_o($topic_tag, '$topic_tag');
		if ($topic_tag) {
			mydb::query('INSERT INTO %tag_topic% ( `tpid` , `vid` , `tid` ) VALUES ( ' . implode(' ) , ( ',$topic_tag) .' ) ',$simulate);
			$result->process[]=mydb()->_query;

			$stmt = 'SELECT tid,name FROM %tag% WHERE tid IN ('.implode(',',array_keys($topic_tag)).')';
			$topic->tags = mydb::select($stmt)->items;
			$result->process[]=mydb()->_query;
		}

	}
	$result->process[]=print_o($taxonomy,'$taxonomy');

	// Save upload photo to folder and add to table
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
			$photo_result=$result->photo[] = R::Model('photo.save',$photo);
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
			$document->description = $topic->post->document_description;
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
			$video->title = $topic->post->document_title;
			$video->description = $topic->post->document_description;
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

	//		if ($topic->type->module!='paper') do_class_method($topic->type->module,'_post',$this,$topic);

	if ($error) {
		$result->error='Create topic error : <ul><li>'.implode('</li><li>',(array)$error).'</li></ul>';
	} else {
		$result->complete=true;
	}
	$result->process[]='paper_model::save_topic complete';
	return $result;
}
?>