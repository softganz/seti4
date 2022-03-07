<?php
/**
* Model :: Description
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*
* @usage R::Model("module.method", $condition, $options)
*/

$debug = true;

function r_changwat_get($conditions = NULL, $options = '{}') {
	$defaults = '{debug: false, result: "record"}';
	$options = SG\json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_string($conditions) && preg_match('/^{/',$conditions)) {
		$conditions = SG\json_decode($conditions);
	} else if (is_object($conditions)) {
		//
	} else if (is_array($conditions)) {
		$conditions = (Object) $conditions;
	} else {
		$conditions = (Object) ['id' => $conditions];
	}

	$result = array();
	//$result[-1] = '--- เลือกจังหวัด ---';

	$stmt = 'SELECT `provid`, `provname`
		FROM %co_province%
		ORDER BY CONVERT(`provname` USING tis620) ASC';
	$changwatList = mydb::select($stmt)->items;
	debugMsg($dbs, '$dbs');

	if ($options->region == 'zone') {
		$result = $result + array(
			'TH' => '++ ทั้งประเทศ',
			'ระดับภาค' => array(
				1 => '++ ภาคกลาง',
				3 => '++ ภาคตะวันออกเฉียงเหนือ',
				5 => '++ ภาคเหนือ',
				8 => '++ ภาคใต้',
			)
		);
	}

	foreach ($changwatList as $rs) {
		if ($options->region == 'changwat') {
			$result[$rs->provid] = $rs->provname;
		} else {
			$result['ระดับจังหวัด'][$rs->provid] = $rs->provname;
		}
	}

	return $result;
}
?>