<?php
/**
* Village Code
* Created 2020-01-29
* Modify  2020-01-29
*
* @param Object $self
* @param Int $tambonId
* @return String
*/

$debug = true;

function code_village($self, $tambonId = NULL) {
	$ret = '<header class="header">'._HEADER_BACK.'<h3>รหัสหมู่บ้าน</h3></header>';

	if (empty($tambonId)) return message('error','กรุณาระบุรหัสตำบล');

	mydb::where('LEFT(`villid`,6) = :tambonId', ':tambonId', $tambonId);

	$stmt = 'SELECT
		d.*
		FROM %co_village% d
		%WHERE%
		ORDER BY `villid` ASC';

	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = array('code -center -nowrap'=>'รหัสหมู่บ้าน', 'villageno -center -nowrap' => 'หมู่ที่', 'name -fill'=>'ชื่อหมู่บ้าน','');

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->villid,
			$rs->villno,
			$rs->villname,
			'<a href="'.url('code/ampur/distance/'.$rs->villid).'"><i class="icon -material">directions_car</i></a>',
		);
	}

	$ret .= $tables->build();

	//$ret .= print_o($dbs);
	return $ret;
}
?>