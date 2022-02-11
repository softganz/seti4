<?php
/**
* Code Model :: Code Collection Model
* Created 2021-09-11
* Modify  2021-09-11
*
* @usage import('model:code')
*/

$debug = true;

class ChangwatModel {
	public static function items() {
		$stmt = 'SELECT `provid`, `provname` `changwatName` FROM %co_province% ORDER BY CONVERT(`changwatName` USING tis620) ASC; -- {key: "provid", value: "changwatName"}';
		$dbs = mydb::select($stmt);
		return $dbs->items;

	}
}

class AmpurModel {
	function inChangwat($changwat) {
		if (empty($changwat)) return [];

		return mydb::select(
			'SELECT `distid`, `distname`
			FROM  %co_district%
			WHERE LEFT(`distid`,2) = :changwat AND NOT INSTR(`distname`, "*");
			 -- {resultType: "array", key: "distid", value: "distname"}
			',
			[':changwat' => $changwat]
		);
	}
}
?>