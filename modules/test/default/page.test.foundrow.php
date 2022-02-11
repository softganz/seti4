<?php
function test_foundrow($self) {
	R::View('test.toolbar',$self,'Test FOUND_ROWS',NULL);

	$foundrows = mysqli_query(mydb(),'SELECT FOUND_ROWS() `totals` LIMIT 1')->fetch_array(MYSQLI_ASSOC);

	//['totals'];

	$ret .= 'Found Rows = '.$foundrows['totals'];
	return $ret;
}
?>
