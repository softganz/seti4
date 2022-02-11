<?php
/**
* Green :: My Animal Weight
* Created 2020-12-05
* Modify  2020-12-05
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage green/my/weight/form[/{id}]
*/

$debug = true;

function green_my_animal_weight_form($self, $weightId = NULL) {
	$stmt = 'SELECT `keyid` FROM %bigdata% WHERE `keyname` = "GREEN,ANIMAL" AND `fldname` = "weight" AND `bigid` = :bigid LIMIT 1';
	$animalId = mydb::select($stmt, ':bigid', $weightId)->keyid;

	if ($animalId) {
		$plantInfo = R::Model('green.plant.get', $animalId, '{data: "orgInfo,animal"}');
		$data = $plantInfo->animalWeight[$weightId];
	}

	if (empty($data)) return 'ERROR: ไม่มีข้อมูล';

	$ret = '';

	$ret .= $weightForm = R::View('green.my.animal.weight.form', $data)->build();

	return $ret;
}
?>