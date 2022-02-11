<?php
function r_imed_person_zone_condition($zones) {
	$result='';
	$zoneCondition=$zoneTambon=$zoneAmpur=$zoneProvince=array();
	if ($zones) {
		foreach ($zones as $zone) {
			if (strlen($zone->zone)==6) {
				$zoneTambon[]=$zone->zone;
			} else if (strlen($zone->zone)==4) {
				$zoneAmpur[]=$zone->zone;
			} else if (strlen($zone->zone)==2) {
				$zoneProvince[]=$zone->zone;
			}
		}
		if ($zoneProvince) $zoneCondition[]='p.`changwat` IN ("'.implode('","', $zoneProvince).'")';
		if ($zoneAmpur) $zoneCondition[]='CONCAT(p.`changwat`,p.`ampur`) IN ("'.implode('","', $zoneAmpur).'")';
		if ($zoneTambon) $zoneCondition[]='CONCAT(p.`changwat`,p.`ampur`,p.`tambon`) IN ("'.implode('","', $zoneTambon).'")';
		$result='('.implode(' OR ',$zoneCondition).')';
	}
	return $result;
}
?>