<?php
function project_test_json() {
	$result['ok']=true;
	$result['msg']='Hello world';
	die(json_encode($result));
}
?>