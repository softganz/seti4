<?php
/**
* Ampur Code
* Created 2019-05-15
* Modify  2019-05-15
*
* @param Object $self
* @param Int $changwatId
* @return String
*/

$debug = true;

function code_ampur($self, $changwatId = NULL) {
	$ret = '<header class="header">'._HEADER_BACK.'<h3>รหัสอำเภอ</h3></header>';

	if ($changwatId) mydb::where('LEFT(`distid`,2) = :changwatId', ':changwatId', $changwatId);

	$stmt = 'SELECT
		d.*
		, (SELECT COUNT(*) FROM %co_subdistrict% WHERE LEFT(`subdistid`,4) = `distid`) `totalTambon`
		, (SELECT COUNT(*) FROM %co_village% WHERE LEFT(`villid`,4) = `distid`) `totalVillage`
		FROM %co_district% d
		%WHERE%
		ORDER BY `distid` ASC';

	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = array(
		'code -center -nowrap' => 'รหัสอำเภอ',
		'name -fill' => 'ชื่ออำเภอ',
		'tambon -amt -nowrap' => 'จำนวนตำบล',
		'village -amt -nowrap' => 'จำนวนหมู่บ้าน',
		'',
	);

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->distid,
			'<a class="sg-action" href="'.url('code/tambon/'.$rs->distid).'" data-rel="box" data-box-resize="true">'.$rs->distname.'</a>',
			$rs->totalTambon,
			$rs->totalVillage,
			'<a href="'.url('code/ampur/distance/'.$rs->distid).'"><i class="icon -material">directions_car</i></a>',
		);
	}

	$ret .= $tables->build();

	//$ret .= print_o($dbs);
	return $ret;
}
?>