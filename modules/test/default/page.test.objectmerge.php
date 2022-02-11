<?php
/**
* Module Method
* Created 2019-01-01
* Modify  2019-01-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function test_objectmerge($self) {
	$ret = '<h2>Object Merge Recursive</h2>';

	//$o0['title'] = 'Hello 0';

	$o1->title = 'o1 Hello o1';
	$o1->budget->show = 'o1 public';
	$o1->budget->check = 'o1 1';
	$o1->budget->status = (object) array('s1o1' => 'o1 active', 's2' => 'o1 active', 's4' => 'o1 test');
	$o1->budget->item['x'] = 'o1 XXX-O1';

	$o2->title = 'o2 Hello World o2';
	$o2->budget->show = 'o2 admin';
	$o2->budget->status->s1 = 'o2 close';
	$o2->budget->status->s2 = 'o2 active';
	$o2->budget->status->s3 = 'o2 inactive';
	$o2->budget->item[] = 'o2 AAA';
	$o2->budget->item['x'] = 'o2 XXX-O2';
	$o2->budget->item['y'] = 'o2 YYY';

	$o3->title = 'o3 Hello World o3';
	//$o3->budget->status->s1 = 'close';
	$o3->budget->item->x = 'o3 XXX-O3';

	$ret .= print_o($o1, '$o1');
	$ret .= print_o($o2, '$o2');
	$ret .= print_o($o3, '$o3');

	$result = object_merge_recursive($o0, $o1, $o2, $o3);

	$ret .= '<b>object_merge_recursive($o1, $o2, $o3)</b><br />'.print_o($result, '$object');

	$ret .= print_o($o1, '$o1 merge');
	$ret .= print_o($o2, '$o2 merge');
	$ret .= print_o($o3, '$o3 merge');

	$ret .= json_encode($o2).'<br />';

	$result = SG\json_decode($o3, $o2, $o1, '{debug: false}');

	$ret .= '<b>SG\json_decode($o3, $o2, $o1, \'{debug: false}\')</b><br />'.print_o($result, '$decode');

	$ret .= '$result = <pre>'.SG\json_encode($result).'</pre>';
	return $ret;
}
?>