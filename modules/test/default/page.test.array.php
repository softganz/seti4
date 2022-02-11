<?php
function test_array($self) {
	R::View('test.toolbar',$self,'Test Page Title Toolbar',NULL);

	$ret=array(
				'msg'=>'<font color="red">This is a test page view '.$id.'</font>',
				'id'=>10,
				'url'=>'{url:test/array}'
				);
	return $ret;
}
?>