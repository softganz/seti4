<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function publicmon_assets_view($self) {
	R::View('publicmon.toolbar',$self,'Assets Information');


	$ret .= '<p>แสดงข้อมูลรายละเอียด</p>';

	$ret .= '<div id="map-canvas" style="width:100%;height:300px;background:#eee;">แผนที่</div>';

	return $ret;
}
?>