<?php
/**
 * Code     :: Update Ampur Distance
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2019-05-15
 * Modified :: 2026-08-23
 * Version  :: 2
 *
 * @param Object $self
 * @param Int $var
 * @return String
 */

use Softganz\DB;

function code_ampur_distance_update($self, $ampurId = NULL) {
	$post = (object)post();
	$ret = array();
	$ret['msg'] = 'บันทึกเรียบร้อย';

	//$ret['msg'] .= print_o($post,'$post');

	if ($post->ret == 'numeric') $post->value = sg_strip_money($post->value);

	if (empty($post->value)) $post->value = NULL;

	DB::query([
		'INSERT INTO %distance%
		(`fromareacode`, `toareacode`, `$FIELD$`)
		VALUES
		(:from, :to, :value)
		ON DUPLICATE KEY UPDATE
		$FIELD$ = :value',
		'var' => [
			'$FIELD$' => $post->fld
		]
	]);

	$ret['value'] = is_null($post->value) ? '' : $post->value;
	//$ret['msg'] .= R('query');
	return $ret;
}
?>