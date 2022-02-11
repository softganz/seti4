<?php
/**
* Show realtime symbol proce from set.or.th
*
* @return String
*/
function set_price($self) {
	$tables = new Table();
	$tables->rows=R::Model('set.price.realtime.get');
	$ret.='<h2>Realtime SET Price @'.date('d-m-Y H:i:s').' total = '.count($tables->rows).' items.</h2>';
//	$ret.='<p><strong>Count = '.count($tables->rows).' items.</strong></p>';
	$ret.=$tables->build();
	return $ret;
}
?>