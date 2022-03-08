<?php
/**
* Node Delete
*
* @param Object $tpid
* @return Object $options
*/

$debug = true;

function r_node_delete($tpid, $options = '{}') {
	$defaults = '{debug: false, simulate: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$result->complete=false;
	$result->error=false;
	$result->process[]='__edit_delete request';

	$simulate = $options->simulate;

	$stmt = 'SELECT * FROM %topic% WHERE `tpid` = :tpid LIMIT 1';
	$result->data = mydb::clearprop(mydb::select($stmt, ':tpid',$tpid));

	// delete topic
	$result->process[]='Delete paper topic and re-autoindex';
	$stmt = 'DELETE FROM %topic% WHERE `tpid` = :tpid LIMIT 1';
	mydb::query($stmt, ':tpid', $tpid);
	$result->process[]=mydb()->_query;

	// delete detail
	$result->process[]='Delete paper detail';
	$stmt = 'DELETE FROM %topic_revisions% WHERE `tpid` = :tpid LIMIT 1';
	mydb::query($stmt, ':tpid', $tpid);
	$result->process[]=mydb()->_query;

	// delete tag topic
	$result->process[]='Delete Tag Topic';
	$stmt = 'DELETE FROM %tag_topic% WHERE `tpid` = :tpid';
	mydb::query($stmt, ':tpid', $tpid);
	$result->process[]=mydb()->_query;

	$stmt = 'DELETE FROM %topic_user% WHERE `tpid` = :tpid';
	mydb::query($stmt, ':tpid', $tpid);
	$result->process[]=mydb()->_query;

	$stmt = 'DELETE FROM %topic_parent% WHERE `tpid` = :tpid';
	mydb::query($stmt, ':tpid', $tpid);
	$result->process[]=mydb()->_query;

	// Delete topic property
	$result->process[]='Delete topic property';
	$stmt = 'DELETE FROM %property% WHERE `module` = "paper" AND `propid` = :tpid';
	mydb::query($stmt, ':tpid', $tpid);
	$result->process[]=mydb()->_query;





	// delete photos
	$result->process[] = 'Start delete all photo';
	$photoDbs = mydb::select('SELECT * FROM %topic_files% WHERE tpid = :tpid AND `type` = "photo"', ':tpid',$tpid);
	$result->process[]=mydb()->_query;

	$stmt = 'DELETE FROM %topic_files% WHERE tpid = :tpid AND `type` = "photo"';
	mydb::query($stmt, ':tpid', $tpid);
	$result->process[]=mydb()->_query;

	foreach ($photoDbs->items as $photo) {
		$filename = cfg('folder.abs').cfg('upload_folder').'pics/'.$photo->file;
		if (file_exists($filename) and is_file($filename)) {
			$is_photo_inused = mydb::count_rows('%topic_files%','`file` = "'.$photo->file.'" AND `fid` != '.$photo->fid);
			$result->process[]=mydb()->_query;

			if ($is_photo_inused) {
				$result->process[] = 'File <em>'.$photo->_file.'</em> was used by other item';
			} else {
				$result->process[] = '<em>Delete file '.$filename.'</em>';
				if (!$simulate) unlink($filename);
			}
		}
	}


	// delete documents
	$result->process[]='Delete document';
	$docDbs = mydb::select('SELECT `file` FROM %topic_files% WHERE tpid = :tpid AND `type` = "doc"',':tpid',$tpid);
	$result->process[]=mydb()->_query;

	$stmt = 'DELETE FROM %topic_files% WHERE tpid = :tpid AND `type` = "doc"';
	mydb::query($stmt, ':tpid', $tpid);
	$result->process[]=mydb()->_query;

	foreach ( $docDbs->items as $rs ) {
		$filename = cfg('folder.abs').cfg('upload_folder').'forum/'.$rs->file;
		$result->process[] = '<em>Delete document '.$filename.'</em>';
		if (!$simulate && file_exists($filename) && is_file($filename)) unlink($filename);
	}

	/*
	// delete video
	if ($topic->video->file) {
		$result->process[]='Delete video';
		$stmt = 'DELETE FROM %topic_files% WHERE `tpid` = :tpid AND `type`="movie"';
		mydb::query($stmt, ':tpid', $tpid);
		$result->process[]=mydb()->_query;

		if ($topic->video->_location && file_exists($topic->video->_location) && is_file($topic->video->_location)) {
			if (!$simulate) unlink($topic->video->_location);
			$result->process[]='<em>delete video file '.$topic->video->_location.'</em>';
		}
	}
	*/

	// delete comment post
	$result->process[]='Delete comment';

	$stmt = 'DELETE FROM %topic_comments% WHERE tpid = :tpid';
	mydb::query($stmt, ':tpid', $tpid);
	$result->process[]=mydb()->_query;

	// save delete log
	model::watch_log('paper','Paper delete','paper/'.$tpid.' - '.$result->data->title.' was delete');

	// delete was complete
	$result->complete = true;
	$result->process[] = 'r_node_delete complete';


	// Call node delete complete
	// TODO : Do it later
	//if (function_exists('module_exists') && module_exists($classname,'__delete_complete')) call_user_func(array($classname,'__delete_complete'),$this,$topic,$para,$result);

	return $result;
}
?>