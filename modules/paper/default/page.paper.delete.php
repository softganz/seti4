<?php
/**
* Delete Paper
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_delete($self, $topicInfo, $para = NULL) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $topicInfo->tpid;

	if (!$topicInfo) return message('error','Topic not found');

	$confirm=SG\getFirst($para->confirm,$_REQUEST['confirm']);
	// do external delete from module
	$classname = function_exists('module2classname') && module2classname($topicInfo->info->module);
	if ($confirm == 'no') {
		if (function_exists('module_exists') && module_exists($classname,'__delete_cancel')) call_user_func(array($classname,'__delete_cancel'),$self,$topicInfo,$para,$result);
		else location('paper/'.$tpid);
	} if ($confirm != 'yes') {

		$ret .= '<header class="header -box"><h3>ลบหัวข้อ และ ข้อมูลประกอบ</h3></header>';

		$form = new Form(NULL, url(q()), 'delete');

		$form->addField(
			'confirm',
			array(
				'type' => 'radio',
				'name' => 'confirm',
				'label' => 'คุณต้องการลบหัวข้อ <strong>"'.$topicInfo->title.'"</strong>  ใช่หรือไม่?',
				'options' => array('no' => 'ไม่ ฉันไม่ต้องการลบ', 'yes' => 'ใช่ ฉันต้องการลบทิ้ง'),
			)
		);

		$form->addField(
			'proceed',
			array(
				'type' => 'button',
				'class' => '-danger',
				'value' => '<i class="icon -material">delete</i><span>ดำเนินการลบหัวข้อ</span>',
				'container' => '{class: "-sg-text-right"}',
			)
		);

		$form->addText('<strong style="color:red; font-size: 1.2em;">คำเตือน : จะทำการลบข้อมูลหัวข้อนี้พร้อมทั้งภาพและเอกสารประกอบทั้งหมด และจะไม่สามารถเรียกคืนได้อีกแล้ว</strong>');


		$ret .= $form->build();
		return $ret;
	}


	// Start delete topic

	$result = (Object) [
		'complete' => false,
		'error' => false,
		'process' => ['__edit_delete request'],
	];

	//if ($para->edit!='delete' || $confirm!='yes') return message('error','Invalid parameter');


	if (!user_access('administer contents,administer papers,administer '.$topicInfo->info->module.' paper','edit own paper',$topicInfo->uid)) return message('error','Access denied');
	if ($topicInfo->info->status==_LOCK) return message('error','This topic was lock:You cannot delete a lock topic. Please unlock topic and go to delete again.');

	// if set to true , simulate sql (not insert ) and show sql command
	// if set to true , simulate sql (not insert ) and show sql command
	$simulate = debug('simulate');


	if (function_exists('module_exists') && module_exists($classname,'__delete')) call_user_func(array($classname,'__delete'),$self,$topicInfo,$para,$result);
	if ($result->error) return message('error',$result->error);

	// delete topic
	$result->process[]='Delete paper topic and re-autoindex';
	$sql_cmd = 'DELETE FROM %topic% WHERE tpid='.$tpid.' LIMIT 1';
	mydb::query($sql_cmd,$simulate);
	$result->process[]=mydb()->_query;
	$max_auto_id = mydb::select('SELECT MAX(`tpid`) `max_auto_id` FROM %topic% LIMIT 1')->max_auto_id;
	$result->process[] = mydb()->_query;

	mydb::query('ALTER TABLE %topic% AUTO_INCREMENT='.$max_auto_id,$simulate);
	$result->process[] = mydb()->_query;

	// delete detail
	$result->process[]='Delete paper detail';
	$sql_cmd = 'DELETE FROM %topic_revisions% WHERE tpid='.$tpid.' LIMIT 1';
	mydb::query($sql_cmd,$simulate);
	$result->process[]=mydb()->_query;

	// delete tag topic
	$result->process[]='Delete Tag Topic';
	$sql_cmd = 'DELETE FROM %tag_topic% WHERE tpid='.$tpid;
	mydb::query($sql_cmd,$simulate);
	$result->process[]=mydb()->_query;

	mydb::query('DELETE FROM %topic_user% WHERE `tpid`='.$tpid,$simulate);
	$result->process[]=mydb()->_query;

	mydb::query('DELETE FROM %topic_parent% WHERE `tpid`='.$tpid,$simulate);
	$result->process[]=mydb()->_query;

	// Delete topic property
	$result->process[]='Delete topic property';
	mydb::query('DELETE FROM %property% WHERE `module`="paper" AND `propid`=:propid',':propid',$tpid);
	$result->process[]=mydb()->_query;

	// delete photos
	$result->process[]='Start delete all photo';
	$delete_photos=mydb::select('SELECT * FROM %topic_files% WHERE tpid='.$tpid.' AND `type`="photo"');
	$sql_cmd = 'DELETE FROM %topic_files% WHERE tpid='.$tpid.' AND `type`="photo"';
	mydb::query($sql_cmd,$simulate);
	$result->process[]=mydb()->_query;

	if ($delete_photos->_num_rows) {
		foreach ($delete_photos->items as $photo) {
			$filename=cfg('folder.abs').cfg('upload_folder').'pics/'.$photo->file;
			if (file_exists($filename) and is_file($filename)) {
				$is_photo_inused = mydb::select('SELECT COUNT(*) `amt` FROM %topic_files% WHERE `file` = :file AND `fid` != :fid LIMIT 1', ':file', $photo->file, ':fid', $photo->fid)->amt;
				$result->process[] = mydb()->_query;
				if ($is_photo_inused) {
					$result->process[] = 'file <em>'.$photo->_file.'</em> was used by other item';
				} else {
					$result->process[] = '<em>delete file '.$filename.'</em>';
					if (!$simulate) unlink($filename);
				}
			}
		}
	}

	// delete documents
	$result->process[]='Delete document';
	$doc_result = mydb::select('SELECT `file` FROM %topic_files% WHERE `tpid` = :tpid AND `type` = "doc"', ':tpid', $tpid)->file;
	$result->process[]=mydb()->_query;
	if ( $doc_result ) {
		$stmt = 'DELETE FROM %topic_files% WHERE `tpid` = :tpid AND `type` = "doc"';
		mydb::query($stmt, ':tpid', $tpid);
		$result->process[] = mydb()->_query;
		foreach ( $doc_result as $docRs ) {
			$filename = cfg('folder.abs').cfg('upload_folder').'forum/'.$docRs->file;
			$result->process[]= '<em>delete document '.$filename.'</em>';
			if (!$simulate && file_exists($filename) && is_file($filename)) unlink($filename);
		}
	}

	// delete video
	if ($topicInfo->video->file) {
		$result->process[]='Delete video';
		$sql_cmd = 'DELETE FROM %topic_files% WHERE tpid='.$tpid.' AND `type`="movie"';
		mydb::query($sql_cmd,$simulate);
		$result->process[]=mydb()->_query;
		if ($topicInfo->video->_location && file_exists($topicInfo->video->_location) && is_file($topicInfo->video->_location)) {
			if (!$simulate) unlink($topic->video->_location);
			$result->process[]='<em>delete video file '.$topicInfo->video->_location.'</em>';
		}
	}

	// delete comment post
	$result->process[]='Delete comment';

	$sql_cmd = 'DELETE FROM %topic_comments% WHERE tpid='.$tpid;
	mydb::query($sql_cmd,$simulate);
	$result->process[]=mydb()->_query;

	// send email alert on delete
	if (cfg('alert.email') && in_array('paper',explode(',',cfg('alert.module')))) {
		$mail = (Object) [
			'to' => cfg('alert.email'),
			'title' => '-- topic : '.strip_tags($topicInfo->title).' : '.$topicInfo->tags[0]->name,
			'name' => i()->name,
			'from' => 'alert@'.cfg('domain.short'),
		];
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
<strong>topic was delete by '.i()->name.' ('.i()->uid.') on '.date('Y-m-d H:i:s').'</strong>
<hr size=1>
Submit by <b>'.$topicInfo->info->poster.'</b> on <b>'.$topicInfo->info->created.'</b> | paper id : <b>'.$tpid.'</b><br />
<hr size=1>'.
sg_text2html($topicInfo->info->body).'
</body>
</html>
';
		BasicModel::sendmail($mail);
	}

	// save delete log
	BasicModel::watch_log('paper','Paper delete','Paper/'.$tpid.' was delete');

	// delete was complete
	$result->complete=true;
	$result->process[]= '__edit_delete complete';
	if ($simulate) $ret.=print_o($result,'$result');
	else if (function_exists('module_exists') && module_exists($classname,'__delete_complete')) call_user_func(array($classname,'__delete_complete'),$self,$topicInfo,$para,$result);
	else if ($para->deleteonly) {
		// do nothing
	} else location('tags/'.$topicInfo->tags[0]->tid,$ret);

	return $ret;
}
?>