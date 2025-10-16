<?php
/**
* Tambon Code
* Created 2020-01-29
* Modify  2020-01-29
*
* @param Object $self
* @param Int $ampurId
* @return String
*/

$debug = true;

function code_tambon($self, $ampurId = NULL) {
	$ret = '<header class="header">'._HEADER_BACK.'<h3>รหัสตำบล</h3></header>';

	if (empty($ampurId)) return message('error','กรุณาระบุรหัสตำบล');

	if ($ampurId) mydb::where('LEFT(`subdistid`,4) = :ampurId', ':ampurId', $ampurId);

	$stmt = 'SELECT
		d.*
		, (SELECT COUNT(*) FROM %co_village% WHERE LEFT(`villid`,6) = `subdistid`) `totalVillage`
		FROM %co_subdistrict% d
		%WHERE%
		ORDER BY `subdistid` ASC';

	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = array(
		'code -center -nowrap' => 'รหัสตำบล',
		'name -fill' => 'ชื่อตำบล',
		'village -amt -nowrap' => 'จำนวนหมู่บ้าน',
		''
	);

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->subdistid,
			'<a class="sg-action" href="'.url('code/village/'.$rs->subdistid).'" data-rel="box" data-box-resize="true">'.$rs->subdistname.'</a>',
			$rs->totalVillage,
			'<a href="'.url('code/ampur/distance/'.$rs->subdistid).'"><i class="icon -material">directions_car</i></a>',
		);
	}

	$ret .= $tables->build();

	//$ret .= print_o($dbs);
	return $ret;
}
?>