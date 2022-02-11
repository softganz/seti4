<?php
/**
* Model :: Description
* Created 2021-09-30
* Modify 	2021-09-30
*
* @param Array $args
* @return Object
*
* @usage new NodeModel([])
* @usage NodeModel::function($conditions, $options)
*/

$debug = true;

class NodeModel {

	public static function get($id, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) ['nodeId' => NULL, 'title' => '', 'info' => NULL];

		$result->info = mydb::select(
			'SELECT t.`tpid` `nodeId`, t.* FROM %topic% t WHERE t.`tpid` = :nodeId LIMIT 1;
			-- {fieldOnly: true}',
			[':nodeId' => $id]
		);
		// debugMsg(mydb()->_query);

		if (empty($result->info->nodeId)) return NULL;

		$result->nodeId = $result->info->nodeId;
		$result->title = $result->info->title;

		return $result;
	}

	public static function items($conditions, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = NULL;

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$conditions = (Object) ['id' => $conditions];
		}

		// TODO: Code for get items

		return $result;
	}

	/**
	* Node Delete
	*
	* @param Object $tpid
	* @return Object $options
	*/

	public static function delete($tpid, $options = '{}') {
		$defaults = '{debug: false, simulate: false}';
		$options = sg_json_decode($options, $defaults);
		$debug = $options->debug;

		$result = (Object) [
			'complete' => false,
			'error' => false,
			'process' => ['NodeModel:::delete() request'],
		];

		$simulate = $options->simulate;

		$stmt = 'SELECT * FROM %topic% WHERE `tpid` = :tpid LIMIT 1';
		$result->data = mydb::clearprop(mydb::select($stmt, ':tpid',$tpid));

		// delete topic
		$result->process[]='Delete paper topic and re-autoindex';
		mydb::query(
			'DELETE FROM %topic% WHERE `tpid` = :tpid LIMIT 1',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		// delete detail
		$result->process[] = 'Delete paper detail';
		mydb::query(
			'DELETE FROM %topic_revisions% WHERE `tpid` = :tpid LIMIT 1',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		// delete tag topic
		$result->process[] = 'Delete Tag Topic';
		mydb::query(
			'DELETE FROM %tag_topic% WHERE `tpid` = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		mydb::query(
			'DELETE FROM %topic_user% WHERE `tpid` = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		mydb::query(
			'DELETE FROM %topic_parent% WHERE `tpid` = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		mydb::query(
			'DELETE FROM %reaction% WHERE `action` LIKE "TOPIC.%" AND `refid` = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		// Delete topic property
		$result->process[] = 'Delete topic property';
		mydb::query(
			'DELETE FROM %property% WHERE `module` = "paper" AND `propid` = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		// delete photos
		$result->process[] = 'Start delete all photo';
		$photoDbs = mydb::select(
			'SELECT * FROM %topic_files% WHERE tpid = :tpid AND `type` = "photo"',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		mydb::query(
			'DELETE FROM %topic_files% WHERE tpid = :tpid AND `type` = "photo"',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		foreach ($photoDbs->items as $photo) {
			$filename = cfg('folder.abs').cfg('upload_folder').'pics/'.$photo->file;
			if (file_exists($filename) and is_file($filename)) {
				$is_photo_inused = mydb::count_rows('%topic_files%','`file` = "'.$photo->file.'" AND `fid` != '.$photo->fid);
				$result->process[] = mydb()->_query;

				if ($is_photo_inused) {
					$result->process[] = 'File <em>'.$photo->_file.'</em> was used by other item';
				} else {
					$result->process[] = '<em>Delete file '.$filename.'</em>';
					if (!$simulate) unlink($filename);
				}
			}
		}


		// delete documents
		$result->process[] = 'Delete document';
		$docDbs = mydb::select(
			'SELECT `file` FROM %topic_files% WHERE tpid = :tpid AND `type` = "doc"',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		mydb::query(
			'DELETE FROM %topic_files% WHERE tpid = :tpid AND `type` = "doc"',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

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
		$result->process[] = 'Delete comment';

		mydb::query(
			'DELETE FROM %topic_comments% WHERE tpid = :tpid',
			[':tpid' => $tpid]
		);
		$result->process[] = mydb()->_query;

		// save delete log
		model::watch_log('paper','Paper delete','paper/'.$tpid.' - '.$result->data->title.' was delete');

		// delete was complete
		$result->complete = true;
		$result->process[] = 'NodeModel::delete() complete';


		// Call node delete complete
		// TODO : Do it later
		//if (function_exists('module_exists') && module_exists($classname,'__delete_complete')) call_user_func(array($classname,'__delete_complete'),$this,$topic,$para,$result);

		return $result;
	}
}
?>