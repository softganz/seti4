<?php
function admin_content_type_delete($self,$type_id) {
	$type=BasicModel::get_topic_type($type_id);

	if (\SG\confirm()) {
		if ($type->locked) return message('error','Content types was locked');
		$isUsed=mydb::select('SELECT `tpid` FROM %topic% WHERE `type`=:type LIMIT 1',':type',$type_id)->tpid;
		if ($isUsed) return message('error','Content types was inused.');
		mydb::query('DELETE FROM %topic_types% WHERE type=:type LIMIT 1',':type',$type_id);
		mydb::query('DELETE FROM %vocabulary_types% WHERE type=:type',':type',$type_id);
		cfg_db_delete('topic_options_'.$type_id);
		cfg_db_delete('comment_'.$type_id);
		$ret.='Delete content type <b>'.$type_id.'</b> completed.';
	}
	return $ret;
}
?>