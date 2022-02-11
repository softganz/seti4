<?php
/**
* Code :: Changwat
* Created 2019-05-15
* Modify  2021-10-30
*
* @return Widget
*
* @usage code/changwat
*/

$debug = true;

class CodeChangwat extends Page {
	function build() {
		$stmt = 'SELECT
			a.*
			, COUNT(DISTINCT v.`villid`) `totalVillage`
			FROM (SELECT
				p.*
				, COUNT(DISTINCT d.`distid`) `totalAmpur`
				, COUNT(DISTINCT sd.`subdistid`) `totalTambon`
				FROM %co_province% p
					LEFT JOIN %co_district% d ON LEFT(d.`distid`,2) = p.`provid` AND RIGHT(d.`distname`,1) != "*"
					LEFT JOIN %co_subdistrict% sd ON LEFT(sd.`subdistid`, 2) = p.`provid` AND RIGHT(sd.`subdistname`,1) != "*"
				GROUP BY p.`provid`
				ORDER BY `provid` ASC) a
					LEFT JOIN %co_village% v ON LEFT(v.`villid`, 2) = a.`provid` AND RIGHT(v.`villname`,1) != "*"
				GROUP BY `provid`
			';

		$dbs = mydb::select($stmt);


		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'รหัสจังหวัด',
			]),
			'body' => new Widget([
				'children' => [
					new Table([
						'thead' => [
							'code -center -nowrap' => 'รหัสจังหวัด',
							'name -fill' => 'ชื่อจังหวัด',
							'ampur -amt -nowrap' => 'จำนวนอำเภอ',
							'tambon -amt -nowrap' => 'จำนวนตำบล',
							'village -amt -nowrap' => 'จำนวนหมู่บ้าน'
						],
						'children' => array_map(function($item) {
							return [
								$item->provid,
								'<a class="sg-action" href="'.url('code/ampur/'.$item->provid).'" data-rel="box" data-width="640">'.$item->provname.'</a>',
								$item->totalAmpur,
								$item->totalTambon,
								$item->totalVillage,
							];
						},$dbs->items),
					]), // Table
				], // children
			]), // Widget
		]);
	}
}
?>