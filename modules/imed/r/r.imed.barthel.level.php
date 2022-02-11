<?php
/**
* iMed :: Calculate Barthel Index Level
* Created 2020-12-10
* Modify  2020-12-10
*
* @param Object $adlValue
* @return Object
*
* @usage R::Model("imed.barthel.level", $adlValue)
*/

$debug = true;

function r_imed_barthel_level($adlValue) {
	$barthelList = array('home' => 'ติดบ้าน', 'social' => 'ติดสังคม', 'bed' => 'ติดเตียง', 'no' => 'ไม่ระบุ');

	$result = (Object) [
		'value' => $adlValue,
		'level' => NULL,
		'text' => NULL,
	];

	if (is_null($adlValue)) $result->level = 'no';
	else if ($adlValue >= 12) $result->level = 'social';
	else if ($adlValue >= 5) $result->level = 'home';
	else if ($adlValue === 0 || $adlValue > 0) $result->level = 'bed';

	$result->text = $barthelList[$result->level];

	return $result;
}
?>