<?php
/**
* Module Method
* Created 2019-05-15
* Modify  2019-05-15
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function code_ampur_distance_update($self, $ampurId = NULL) {
	$post = (object)post();
	$ret = array();
	$ret['msg'] = 'บันทึกเรียบร้อย';

	//$ret['msg'] .= print_o($post,'$post');

	if ($post->ret == 'numeric') $post->value = sg_strip_money($post->value);

	mydb::value('$FIELD$', $post->fld);
	if (empty($post->value)) $post->value = NULL;

	$stmt = 'INSERT INTO %distance%
					(`fromareacode`, `toareacode`, `$FIELD$`)
					VALUES
					(:from, :to, :value)
					ON DUPLICATE KEY UPDATE
					$FIELD$ = :value
					';
	mydb::query($stmt, $post);

	$ret['value'] = is_null($post->value) ? '' : $post->value;
	//$ret['msg'] .= mydb()->_query;
	return $ret;
}
?>