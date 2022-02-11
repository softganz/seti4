<?php
/**
* Model Name
* Created 2019-01-01
* Modify  2019-01-01
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_paper_info_update($topicInfo, $data) {
	$simulate = debug('simulate');

	$result = (Object) [
		'title' => 'R::paper.info.update '.($simulate?'<strong>simulation</strong> ':'').'request',
		'process' => [],
		'query' => [],
		'data' => NULL,
	];

	// การแก้ไขหัวข้อ
	if ($data->detail) {
		$result->process[] = 'Update detail';

		if (is_array($data->detail)) $data->detail = (Object) $data->detail;
		$data->detail->uid = i()->uid;
		$data->detail->timestamp = 'func.NOW()';
		$topic_options = cfg('topic_options_'.$topicInfo->info->type);
		$cols = '`'.implode('`,`',array_keys(mydb::columns('topic_revisions'))).'`';
		$cols = str_replace('`revid`','NULL',$cols);

		if ($topic_options->revision) {
			mydb::query('INSERT INTO %topic_revisions% SELECT '.$cols.' FROM %topic_revisions% WHERE revid='.$topicInfo->info->revid.' LIMIT 1',$simulate);
			$result->query[] = mydb()->_query;

			$data->topic->revid = mydb()->insert_id;
			mydb::query(mydb::create_update_cmd('%topic_revisions%',$data->detail,'revid='.$data->topic->revid),$data->detail);
			$result->query[] = mydb()->_query;

		} else {
			$stmt = mydb::create_update_cmd('%topic_revisions%',$data->detail,'tpid='.$topicInfo->tpid.' and revid='.$topicInfo->info->revid.' LIMIT 1');
			//$stmt='UPDATE %topic_revisions% SET `body`=:body, `property`=:property, `timestamp`=:timestamp, `uid`=:uid WHERE `tpid`=:tpid AND `revid`=:revid';
			mydb::query($stmt,':tpid',$topicInfo->tpid,':revid',$topicInfo->info->revid,$data->detail);
			$result->query[] = mydb()->_query;
		}
	}

	if ($data->topic) {
		$result->process[] = 'Update topic';
		// check for clear sticky
		if ($data->clear_sticky && $data->topic['sticky'] && user_access('administer contents')) {
			$sticky = cfg('sticky');
			$result->process[] = 'Clear sticky of '.$sticky[$data->topic->sticky];
			$stmt = 'UPDATE %topic% SET sticky = 0 WHERE sticky = :sticky';
			mydb::query($stmt, ':sticky', $data->topic['sticky']);
			$result->query[] = mydb()->_query;
		}

		//unset($data->topic->uid);
		$data->topic['changed'] = date('Y-m-d H:i:d');
		$stmt = mydb::create_update_cmd('%topic%', $data->topic, 'tpid = '.$topicInfo->tpid.' LIMIT 1');
		mydb::query($stmt,$simulate);
		$result->query[] = mydb()->_query;
	}

	if ($data->photoinfo) {
		$result->process[] = 'Update photo information';
		$stmt = mydb::create_update_cmd('%topic_files%', $data->photoinfo, 'fid = :fid LIMIT 1');
		mydb::query($stmt, $data->photoinfo);
		$result->query[] = mydb()->_query;
	}

	$result->data = $data;

	if ($simulate) {
		return print_o($result,'$result');
	}

	return $result;
}
?>