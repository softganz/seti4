<?php
/**
* Paper   :: Move Paper to Archive
 * Created :: 2019-06-02
 * Modify  :: 2025-07-18
 * Version :: 5
 *
 * @param Object $self
 * @param Object $topicInfo
 * @return String
 */

 use Softganz\DB;

$debug = true;

function paper_edit_archive($self, $topicInfo) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');
	else if (!$topicInfo->right->edit) return message('error', 'Access Denied');

	$tpid = $topicInfo->tpid;

	$ret = '<header class="header -box"><nav class="nav -back"><a class="" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material">arrow_back</i></a></nav><h3>MOVE TO ARCHIVE</h3></header>';

	$form = new Form([
		'variable' => 'topic',
		'action' => url('paper/'.$tpid.'/edit'),
		'id' => 'edit-topic',
		'children' => [
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			]
		], // children
	]);

	$ret .= $form->build();



	if (!user_access('administer papers')) return message('error','Access denied');

	if (!DB::tableExists('%archive_topic%')) return message('error', 'NO ARCHIVE TABLE');

	$error=false;
	if ($topic->_archive) {
		// Move archive to topic
		mydb::query('INSERT INTO %topic% (SELECT * FROM %archive_topic% WHERE `tpid`=:tpid)',':tpid',$topic->tpid);
		if (mydb()->_error) $error[]=mydb()->_query;
		mydb::query('INSERT INTO %topic_revisions% (SELECT * FROM %archive_topic_revisions% WHERE `tpid`=:tpid)',':tpid',$topic->tpid);
		if (mydb()->_error) $error[]=mydb()->_query;
		mydb::query('INSERT INTO %tag_topic% (SELECT * FROM %archive_tag_topic% WHERE `tpid`=:tpid)',':tpid',$topic->tpid);
		if (mydb()->_error) $error[]=mydb()->_query;
		mydb::query('INSERT INTO %topic_comments% (SELECT * FROM %archive_topic_comments% WHERE `tpid`=:tpid)',':tpid',$topic->tpid);
		if (mydb()->_error) $error[]=mydb()->_query;
		mydb::query('INSERT INTO %topic_files% (SELECT * FROM %archive_topic_files% WHERE `tpid`=:tpid)',':tpid',$topic->tpid);
		// Remove data from archive
		if (!$error) {
			mydb::query('DELETE FROM %archive_topic% WHERE `tpid`=:tpid',':tpid',$topic->tpid);
			mydb::query('DELETE FROM %archive_topic_revisions% WHERE `tpid`=:tpid',':tpid',$topic->tpid);
			mydb::query('DELETE FROM %archive_tag_topic% WHERE `tpid`=:tpid',':tpid',$topic->tpid);
			mydb::query('DELETE FROM %archive_topic_comments% WHERE `tpid`=:tpid',':tpid',$topic->tpid);
			mydb::query('DELETE FROM %archive_topic_files% WHERE `tpid`=:tpid',':tpid',$topic->tpid);
			$ret.=message('status','Move paper normal complete');
		}
	} else {
		// Move topic to archive
		mydb::query('INSERT INTO %archive_topic% (SELECT * FROM %topic% WHERE `tpid`=:tpid)',':tpid',$topic->tpid);
		if (mydb()->_error) $error[]=mydb()->_query;
		mydb::query('INSERT INTO %archive_topic_revisions% (SELECT * FROM %topic_revisions% WHERE `tpid`=:tpid)',':tpid',$topic->tpid);
		if (mydb()->_error) $error[]=mydb()->_query;
		mydb::query('INSERT INTO %archive_tag_topic% (SELECT * FROM %tag_topic% WHERE `tpid`=:tpid)',':tpid',$topic->tpid);
		if (mydb()->_error) $error[]=mydb()->_query;
		mydb::query('INSERT INTO %archive_topic_comments% (SELECT * FROM %topic_comments% WHERE `tpid`=:tpid)',':tpid',$topic->tpid);
		if (mydb()->_error) $error[]=mydb()->_query;
		mydb::query('INSERT INTO %archive_topic_files% (SELECT * FROM %topic_files% WHERE `tpid`=:tpid)',':tpid',$topic->tpid);
		if (mydb()->_error) $error[]=mydb()->_query;

		// Remove data from topic
		if (!$error) {
			mydb::query('DELETE FROM %topic% WHERE `tpid`=:tpid',':tpid',$topic->tpid);
			mydb::query('DELETE FROM %topic_revisions% WHERE `tpid`=:tpid',':tpid',$topic->tpid);
			mydb::query('DELETE FROM %tag_topic% WHERE `tpid`=:tpid',':tpid',$topic->tpid);
			mydb::query('DELETE FROM %topic_comments% WHERE `tpid`=:tpid',':tpid',$topic->tpid);
			mydb::query('DELETE FROM %topic_files% WHERE `tpid`=:tpid',':tpid',$topic->tpid);
			$ret.=message('status','Move paper archive complete');
		}
	}
	if ($error) {
		$ret.=message('error','Error on move topic');
		if (user_access('access debugging program')) $ret.=print_o($error,'$error');
	} else {
		location('paper/'.$topic->tpid);
	}

	return $ret;
}
?>