<?php
/** Delete topic
 *
 * @param Array $topic
 * @param Array $para
 * @return String
 */
function r_paper_delete($tpid) {
	$result->complete=false;
	$result->error=false;
	$result->process[]='r_paper_delete request';

	if (empty($tpid)) {
		$result->error='Empty topic';
		return $result;
	}

	// if set to true , simulate sql (not insert ) and show sql command
	// if set to true , simulate sql (not insert ) and show sql command
	$simulate = debug('simulate');

	// delete topic
	$result->process[]='Delete paper topic';
	$stmt = 'DELETE FROM %topic% WHERE `tpid`=:tpid LIMIT 1';
	mydb::query($stmt,':tpid',$tpid);
	$result->process[]=mydb()->_query;

	//$max_auto_id = db_query_one_cell('SELECT MAX(tpid) as max_auto_id FROM %topic%');
	//$result->process[]=mydb()->_query;
	//mydb::query('ALTER TABLE %topic% AUTO_INCREMENT='.$max_auto_id,$simulate);
	//$result->process[]=mydb()->_query;

	$result->process[]='Delete paper detail';
	$stmt = 'DELETE FROM %topic_revisions% WHERE `tpid`=:tpid LIMIT 1';
	mydb::query($stmt,':tpid',$tpid);
	$result->process[]=mydb()->_query;

	$result->process[]='Delete Tag Topic';
	mydb::query('DELETE FROM %tag_topic% WHERE `tpid`=:tpid',':tpid',$tpid);
	$result->process[]=mydb()->_query;

	$result->process[]='Delete Topic User';
	mydb::query('DELETE FROM %topic_user% WHERE `tpid`=:tpid',':tpid',$tpid);
	$result->process[]=mydb()->_query;

	$result->process[]='Delete Topic Parent';
	mydb::query('DELETE FROM %topic_parent% WHERE `tpid`=:tpid',':tpid',$tpid);
	$result->process[]=mydb()->_query;

	$result->process[]='Delete topic property';
	mydb::query('DELETE FROM %property% WHERE `module`="paper" AND `propid`=:propid',':propid',$tpid);
	$result->process[]=mydb()->_query;

	// Delete photos
	$delete_photos=mydb::select('SELECT * FROM %topic_files% WHERE `tpid`=:tpid AND `type`="photo"',':tpid',$tpid);

	if ($delete_photos->_num_rows) {
		$result->process[]='Start delete all photo';
		$stmt = 'DELETE FROM %topic_files% WHERE tpid=:tpid AND `type`="photo"';
		mydb::query($stmt,':tpid',$tpid);
		$result->process[]=mydb()->_query;

		foreach ($delete_photos->items as $photo) {
			$filename=cfg('folder.abs').cfg('upload_folder').'pics/'.$photo->file;
			if (file_exists($filename) and is_file($filename)) {
				$is_photo_inused=mydb::select('SELECT `fid` FROM %topic_files% WHERE `file`=:file AND `fid`!=:fid LIMIT 1',':file',$photo->file,':fid',$photo->fid)->fid;
				$result->process[]=mydb()->_query;
				if ($is_photo_inused) {
					$result->process[]='file <em>'.$photo->_file.'</em> was used by other item';
				} else {
					$result->process[]='<em>delete file '.$filename.'</em>';
					if (!$simulate) unlink($filename);
				}
			}
		}
	}

	// delete documents
	$result->process[]='Delete document';
	$doc_result = mydb::select('SELECT `file` FROM %topic_files% WHERE `tpid`=:tpid AND `type`="doc"',':tpid',$tpid)->items;
	$result->process[]=mydb()->_query;
	if ( $doc_result ) {
		$stmt = 'DELETE FROM %topic_files% WHERE tpid=:tpid AND `type`="doc"';
		mydb::query($stmt,':tpid',$tpid);
		$result->process[]=mydb()->_query;
		foreach ( $doc_result as $rs ) {
			$doc_filename=$rs->file;
			$filename=cfg('folder.abs').cfg('upload_folder').'forum/'.$doc_filename;
			$result->process[]= '<em>delete document '.$filename.'</em>';
			if (!$simulate && file_exists($filename) && is_file($filename)) unlink($filename);
		}
	}

	// delete video
	/*
	if ($topic->video->file) {
		$result->process[]='Delete video';
		$stmt = 'DELETE FROM %topic_files% WHERE tpid=:tpid AND `type`="movie"';
		mydb::query($stmt,':tpid',$tpid);
		$result->process[]=mydb()->_query;
		if ($topic->video->_location && file_exists($topic->video->_location) && is_file($topic->video->_location)) {
			if (!$simulate) unlink($topic->video->_location);
			$result->process[]='<em>delete video file '.$topic->video->_location.'</em>';
		}
	}
	*/

	// delete comment post
	$result->process[]='Delete comment';

	$stmt = 'DELETE FROM %topic_comments% WHERE tpid=:tpid';
	mydb::query($stmt,':tpid',$tpid);
	$result->process[]=mydb()->_query;

	// send email alert on delete
	/*
	if (cfg('alert.email') && in_array('paper',explode(',',cfg('alert.module')))) {
		$mail->to=cfg('alert.email');
		$mail->title='-- topic : '.strip_tags($topic->title).' : '.$topic->tags[0]->name;
		$mail->name=i()->name;
		$mail->from='alert@'.cfg('domain.short');
		if (cfg('alert.cc')) $mail->cc=cfg('alert.cc');
		if (cfg('alert.bcc')) $mail->bcc=cfg('alert.bcc');

		$mail->body='
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta content="text/html;charset='.cfg('client.characterset').'" http-equiv="Content-Type">
<title>'.$topic->title.'</title>
</head>
<body>
<strong>topic was delete by '.i()->name.' ('.i()->uid.') on '.date('Y-m-d H:i:s').'</strong>
<hr size=1>
Submit by <b>'.$topic->poster.'</b> on <b>'.$topic->created.'</b> | paper id : <b>'.$topic->tpid.'</b><br />
<hr size=1>'.
sg_text2html($topic->body).'
</body>
</html>
';
		CommonModel::sendmail($mail);
	}
	*/

	// save delete log
	CommonModel::watch_log('paper','Paper delete','Paper/'.$topic->tpid.' was delete');

	// delete was complete
	$result->complete=true;
	$result->process[]= 'r_paper_delete complete';
	return $result;
}
?>