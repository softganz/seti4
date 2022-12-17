<?php
/**
* Delete comment
*
* @param Integer/Array $commentId
* @return Object $options
* @return Object
*/

$debug = true;


function r_paper_comment_delete($commentId, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = (Object) [
		'complete' => false,
		'error' => false,
		'process' => ['Paper comment delete request'],
	];

	$simulate = debug('simulate');

	if (is_string($commentId)) {
		$commentId = explode(',', $commentId);
	}
	//debugMsg($commentId, '$commentId');

	if (empty($commentId) || !is_array($commentId)) {
		$result->error = true;
		return $result;
	}


	$toDeleteDbs = mydb::select(
		'SELECT `cid`, `tpid` FROM %topic_comments% WHERE `cid` IN (:cid)',
		[':cid' => 'SET:'.implode(',', $commentId)]
	);

	if ($toDeleteDbs->_empty) $result->process[] = 'Nothing to delete';

	// Start delete each comment
	foreach ($toDeleteDbs->items as $delete) {
		// Delete photo
		$result->process[] = 'Process delete photo file of comment '.$delete->cid;
		$photoDbs = mydb::select('SELECT `fid`, `cid`, `file` FROM %topic_files% WHERE `tpid` = :tpid AND `cid` = :cid AND `type`="photo"', $delete);
		//debugMsg($photoDbs, '$photoDbs');
		$result->process[] = mydb()->_query;
		foreach ($photoDbs->items as $photo) {
			$result->process[] = 'Delete comment photo <em>'.cfg('paper.upload.photo.folder').$photo->file.'</em>';
			if (!$simulate) {
				unlink(cfg('paper.upload.photo.folder').sg_tis620_file($photo->file));
				$stmt = 'DELETE FROM %topic_files% WHERE `fid` = :fid LIMIT 1';
				mydb::query($stmt, ':fid', $photo->fid);
				$result->process[] = mydb()->_query;
			}
		}

		// Delete document
		$result->process[] = 'Process delete doc file of comment '.$delete->cid;
		$docDbs = mydb::select('SELECT `fid`, `cid`, `file` FROM %topic_files% WHERE `tpid` = :tpid AND `cid` = :cid AND `type`="doc"', $delete);
		$result->process[] = mydb()->_query;
		foreach ($docDbs->items as $doc) {
			$result->process[] = 'Delete comment document <em>'.cfg('paper.upload.document.folder').$doc->file.'</em>';
			if (!$simulate) {
				unlink(cfg('paper.upload.document.folder').$doc->file);
				$stmt = 'DELETE FROM %topic_files% WHERE `fid` = :fid LIMIT 1';
				mydb::query($stmt, ':fid', $doc->fid);
				$result->process[] = mydb()->_query;
			}
			// if (!$simulate) unlink(cfg('paper.upload.document.folder').sg_tis620_file($doc->file));
		}


		$result->process[] = 'Delete comment '.$delete->cid;
		$stmt = 'DELETE FROM %topic_comments% WHERE `cid` = :cid LIMIT 1';
		mydb::query($stmt, ':cid', $delete->cid);
		$result->process[] = mydb()->_query;

		// Update topic comments
		$stmt = 'UPDATE %topic% SET
							`reply` = (SELECT COUNT(*) FROM %topic_comments% WHERE `tpid` = :tpid)
							, `last_reply` = (SELECT MAX(`timestamp`) FROM %topic_comments% WHERE `tpid` = :tpid)
							WHERE `tpid` = :tpid LIMIT 1';
		mydb::query($stmt, ':tpid', $delete->tpid);
		$result->process[] = mydb()->_query;

		$result->process[] = '== DELETE COMMENT '.$delete->cid.' COMPLETED ==';




		CommonModel::watch_log('paper','Paper comment delete','Delete comment id '.$delete->id.' of <a href="'.url('paper/'.$delete->tpid).'">paper/'.$delete->tpid.'</a>');

		/*
		// get last post date
		$result->process[]='Calculate last reply of each topic';
		$sql_cmd = 'SELECT tpid AS tpid,MAX(timestamp) as last_post_date from %topic_comments% ';
		$sql_cmd .= '  WHERE tpid in ('.$delete->tpid.') ';
		$sql_cmd .= ' GROUP BY tpid';
		foreach (db_query_array($sql_cmd) as $rs) $last_post_dates[$rs['tpid']]=$rs['last_post_date'];
		$result->process[]=mydb()->_query;



		// update last reply date
		$result->process[]='Update last reply to each topic';
		foreach ( $delete->rs as $rs ) {
			$last_post_date = 'NULL';
			if (array_key_exists($rs->tpid,$last_post_dates)) $last_post_date='"'.$last_post_dates[$rs->tpid].'"';

			$up_rpl_cmd = 'UPDATE %topic% SET reply=reply-1,last_reply='.$last_post_date;
			$up_rpl_cmd .= '  WHERE tpid='.$rs->tpid.' LIMIT 1';
			mydb::query($up_rpl_cmd,$simulate);
			$result->process[]=mydb()->_query;
		}
		*/
	}

	// send alert email
	if (cfg('alert.email') && in_array('comment',explode(',',cfg('alert.module')))) {
		$mail = (Object) [
			'to' => cfg('alert.email'),
			'title' => '-- post : '.strip_tags($comment->title).' : '.$comment->content_type,
			'name' => i()->name,
			'from' => 'alert@'.cfg('domain.short'),
		];
		if (cfg('alert.cc')) $mail->cc=cfg('alert.cc');
		if (cfg('alert.bcc')) $mail->bcc=cfg('alert.bcc');

		$ip=GetEnv('REMOTE_ADDR');

		$mail->body='
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta content="text/html;charset='.cfg('client.characterset').'" http-equiv="Content-Type">
<title>'.$comment->title.'</title>
</head>
<body>
<a href="'.cfg('domain').url('paper/'.$comment->tpid).'" target=_blank><b>'.$comment->title.'</b></a> | <a href="'.cfg('domain').url('paper/'.$comment->tpid).'" target="_blank">view</a><br /><hr />
<strong>post message was delete by '.i()->name.' ('.i()->uid.') on '.date('Y-m-d H:i:s').'</strong><br />
<b>host</b> : '.gethostbyaddr($ip).' ('.$ip.')
<hr size=1>
Submit by <strong>'.($comment->name?$comment->name.($comment->uid?'('.$comment->uid.','.$comment->profile_name.')':''):$comment->profile_name.'('.$comment->uid.')').'</strong> on <b>'.$comment->timestamp.'</b> | paper id : <b>'.$comment->tpid.'</b><br />
<hr size=1>'.
sg_text2html($comment->comment).'
</body>
</html>
';
		CommonModel::sendmail($mail);
	}

	$result->complete = true;
	$result->process[] = 'comment_delete complate';
	return $result;
}
?>