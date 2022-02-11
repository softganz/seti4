<?php
function map_gis_center($self) {
	$gisCenter=cfg('map.gis.center');
	if (empty($gisCenter)) $gisCenter='13.710035342476681,100.52490234375'; // Bangkok
	list($lat,$lng)=explode(',', $gisCenter);
	$lat=(float)$lat;
	$lng=(float)$lng;
	$center=array('lat'=>$lat,'lng'=>$lng);
	return $center;
}
?>