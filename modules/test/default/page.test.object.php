<?php
function test_object($self) {
	R::View('test.toolbar',$self,'Test Page Title Toolbar',NULL);

	$ret=(object)array(
				'msg'=>'<font color="red">This is a test page view '.$id.'</font>',
				'id'=>10
				);
	return $ret;
}
?>