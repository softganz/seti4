<?php
/**
* Paper save post comment
* Created :: 2019-06-05
* Modify  :: 2023-10-04
* Version :: 2
*
* @param Object $self
* @param Int $tpid
* @param Array $_POST
* @return String
*/

$debug = true;

function paper_comment_post($self, $topicInfo) {
	if (!$topicInfo) return message('error', 'TOPIC NOT FOUND.');

	$tpid = $topicInfo->tpid;

	// if set to true , simulate sql (not insert ) and show sql command
	$simulate = debug('simulate');


	$comment = (object) post('comment',_TRIM+_STRIPTAG);
	// debugMsg($comment, '$comment');


	$field_missing = array();
	if ($topicInfo->info->property->allow_comment) {
		// do nothing mean alway allow to post comment
	} else if (!user_access('post comments')) {
		$error[]='ขออภัยค่ะ ท่านไม่สามารถเขียนข้อความแสดงความคิดเห็นได้:การแสดงความคิดเห็นต่าง ๆ ขอสงวนสิทธิ์ไว้สำหรับสมาชิกเท่านั้น หากท่านเป็นสมาชิก กรุณา Sign in ให้เรียบร้อยก่อน';
	}


	// Check error
	if (sg_invalid_poster_name($comment->name)) $error[]= 'Duplicate poster name';

	if (!i()->ok && empty($comment->name)) $field_missing[] = 'Your name - ชื่อของท่าน';
	if (!i()->ok && cfg('comment.require.mail') && (empty($comment->mail) || !sg_is_email($comment->mail))) $field_missing[] = 'E-mail - อีเมล์';
	if (!i()->ok && cfg('comment.require.homepage') && empty($comment->homepage)) $field_missing[] = 'Homepage - เว็บไซท์';
	if (!i()->ok && cfg('comment.require.subject') && empty($comment->subject)) $field_missing[] = 'Subject - สาระสำคัญ';
	if (empty($comment->comment)) $field_missing[] = 'Comment message - ข้อความ';
	if (!user_access('upload photo') && preg_match('/\[img\]|<img|\&lt\;img|\!\[.*?\]/i',$comment->comment)) $error[] = 'ขออภัย ท่านไม่มีสิทธิ์ในการส่งภาพ';
	if ($field_missing) $error[]='Input incomplete : กรุณาป้อนรายละเอียดต่อไปนี้ให้ครบถ้วน<ul><li>'.implode('</li><li>',$field_missing).'</li></ul>';

	if (!i()->ok && !sg_valid_daykey(5, post('daykey'))) $error[]='Invalid Anti-spam word';

	if (sg::is_spam_word($comment)) $error[]='มีข้อความที่ไม่เหมาะสมอยู่ในสิ่งที่ป้อนมา';



	// Upload file & picture
	if (empty($error) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
		$upload=new classFile($_FILES['photo'],cfg('paper.upload.photo.folder'),cfg('photo.file_type'));
		if (!$upload->valid_format()) $error[]='Invalid upload file format';
		if (!user_access('administer contents,administer papers') && !$upload->valid_size(cfg('photo.max_file_size')*1024)) $error[]='Invalid upload file size :maximun file size is '.cfg('photo.max_file_size').' KB.';
		if ($upload->duplicate()) $upload->generate_nextfile();
		$photo_upload=$upload->filename;
	}

	if (isset($error)) return message(['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => $error]);


	// Start save
	$comment->uid=i()->ok?i()->uid:0;
	if ($comment->name=== i()->name) unset($comment->name);

	$comment->tpid = $tpid;
	$comment->thread=$topicInfo->title;
	$comment->status=user_access('post comments without approval')?_PUBLISH:_WAITING;
	$comment->timestamp = date('Y-m-d H:i:s');
	$comment->ip=ip2long(GetEnv('REMOTE_ADDR'))?ip2long(GetEnv('REMOTE_ADDR')):0;

	// บันทึกข้อมูล
	$stmt = mydb::create_insert_cmd('%topic_comments%',$comment);

	// Save poster information into cookie
	$remember_time=time()+10*24*3600; // 10 days
	setcookie('sg[name]',$comment->name,$remember_time, cfg('cookie.path'),cfg('cookie.domain'));
	setcookie('sg[mail]',$comment->mail,$remember_time, cfg('cookie.path'),cfg('cookie.domain'));
	setcookie('sg[homepage]',$comment->homepage,$remember_time, cfg('cookie.path'),cfg('cookie.domain'));


	if ($simulate) {
		$ret .= '<p><strong>comment post sql :</strong> '.$stmt.'</p>';
		$post_id = db_query_one_cell('SELECT MAX(pid) FROM %topic_comments%')+1;
	} else {
		mydb::query($stmt,$comment);

		//debugMsg(mydb()->_query);

		$post_id = mydb()->insert_id;
		BasicModel::watch_log('paper','Paper comment post','<a href="'.url('paper/'.$tpid.'#comment-'.$post_id).'">paper/'.$tpid.'comment-#'.$post_id.'</a>:'.$topicInfo->title);
	}

	// Update topic comments
	$stmt = 'UPDATE %topic% SET
		`reply` = (SELECT COUNT(*) FROM %topic_comments% WHERE `tpid` = :tpid)
		, `last_reply` = :now
		WHERE `tpid` = :tpid LIMIT 1';
	if ($simulate) {
		$ret .= '<p><strong>increse reply sql :</strong> '.$stmt.'</p>';
	} else {
		mydb::query($stmt, ':tpid', $tpid, ':now',date('Y-m-d H:i:s'));
		//debugMsg(mydb()->_query);
	}

	if (isset($photo_upload) && is_object($upload)) {
		$pics_desc['type'] = 'photo';
		$pics_desc['tpid'] = $tpid;
		$pics_desc['cid'] = $post_id;
		$pics_desc['uid']=i()->ok?i()->uid:'func.NULL';
		$pics_desc['file']=$photo_upload;
		$pics_desc['timestamp'] = date('Y-m-d H:i:s');
		$pics_desc['ip'] = ip2long(GetEnv('REMOTE_ADDR'));

		$stmt = mydb::create_insert_cmd('%topic_files%',$pics_desc);
		if ($simulate) {
			$ret .= '<strong>save upload file </strong>'.$photo_upload.'<br />';
			$ret .= '<strong>add photo query :</strong>'.$stmt.'<br />';
		} else {
			if (!$upload->copy()) return $ret.message('error','Save file error (unknown error)');
			mydb::query($stmt,$pics_desc);
		}
	}

	$mail->to=cfg('alert.email');
	$mail->title='Re: ++'.strip_tags($topicInfo->title).' : '.$topicInfo->tags[0]->name;
	$mail->name = SG\getFirst(i()->name,$comment->name);
	$mail->from='alert@'.cfg('domain.short');
	if (cfg('alert.cc')) $mail->cc=cfg('alert.cc');
	if (cfg('alert.bcc')) $mail->bcc=cfg('alert.bcc');

	$mail->body='
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta content="text/html;charset='.cfg('client.characterset').'" http-equiv="Content-Type">
<title>'.$topicInfo->title.'</title>
</head>
<body>
<a href="'.cfg('domain').url('paper/'.$tpid).'" target=_blank><b>'.$topicInfo->title.'</b></a> | <a href="'.cfg('domain').url('paper/'.$tpid).'" target=_blank>view</a>
<hr size=1>
Submit by <b>'.($comment->name?$comment->name.(i()->name && i()->name!=$comment->name?'('.i()->name.')':''):i()->name).(i()->uid?'('.i()->uid.')':'').'</b> on <b>'.date('Y-m-d H:i:s').'</b> | paper id : <b>'.$tpid.'</b><br />
<b>poster host</b> : '.gethostbyaddr(long2ip($comment->ip)).' ('.long2ip($comment->ip).') | '.($photo? count($photo).' Photo(s).':'').'
<hr size=1>'.
sg_text2html($comment->comment).'
</body>
</html>
';
	//BasicModel::sendmail($mail);

	$ret .= message('status','Comment post complete');

	if ($simulate) {
		$ret.=print_o($topicInfo,'$topicInfo');
		$ret.=print_o($result,'$result');
		$ret.=print_o($mail,'$mail');
	} else {
		$ret .= R::View('paper.comment.render', $comment);
	}
	return $ret;
}
?>