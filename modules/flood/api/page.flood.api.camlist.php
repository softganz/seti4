<?php
function flood_api_camlist($self) {
	$camList=array();

	$cams['hy']='1,2,3,4';
	$cams['utp']='20,21,22,23';
	$cams['mb']='8,9,10,11';

	$camList['hy']=explode(',', $cams['hy']);
	$camList['utp']=explode(',', $cams['utp']);
	$camList['mb']=explode(',', $cams['mb']);

	die(json_encode($camList));
}
?>