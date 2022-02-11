<?php
/**
* Saveup :: Report Member Age Group
* Created 2017-04-08
* Modify  2020-12-10
*
* @param Object $self
* @return String
*
* @usage saveup/report/member/memage/bygroup
*/

$debug = true;

function saveup_report_member_memage_bygroup($self) {
	$getAge = post('age');

	$self->theme->title='รายงานช่วงอายุการเป็นสมาชิก';

	$stmt = 'SELECT
		a.*
		, CASE
			WHEN a.`memberAge` >= 30 THEN "30 ปีขึ้นไป"
			WHEN a.`memberAge` >= 25 THEN "25-29 ปี"
			WHEN a.`memberAge` >= 20 THEN "20-24 ปี"
			WHEN a.`memberAge` >= 15 THEN "15-19 ปี"
			WHEN a.`memberAge` >= 10 THEN "10-14 ปี"
			WHEN a.`memberAge` >= 6 THEN "6-9 ปี"
			WHEN a.`memberAge` = 5 THEN "5 ปี"
			WHEN a.`memberAge` = 4 THEN "4 ปี"
			WHEN a.`memberAge` = 3 THEN "3 ปี"
			WHEN a.`memberAge` = 2 THEN "2 ปี"
			WHEN a.`memberAge` = 1 THEN "1 ปี"
			WHEN a.`memberDay` < 365 THEN "< 1 ปี"
		END `ageRange`
		, COUNT(*) `amt`
		FROM (
			SELECT
			`mid`
			, `date_approve`
			, DATEDIFF(NOW(),`date_approve`) `memberDay`
			, YEAR(NOW())-YEAR(`date_approve`) `memberAge`
			FROM %saveup_member%
			WHERE status = "active" AND `date_approve` IS NOT NULL
			ORDER BY `memberAge` ASC
		) a
		GROUP BY `ageRange`
		ORDER BY `memberDay` ASC;
		-- {sum: "amt"}';

	$dbs = mydb::select($stmt);
	//debugMsg($dbs,'$dbs');

	$ret .= '<section class="saveup-report -memage">';
	$tables = new Table();
	$tables->addClass('saveup-report-main');
	$tables->caption=$self->theme->title;
	$tables->thead=array(
		'age -amt'=>'ช่วงอายุ(ปี)',
		'member -amt -hover-parent'=>'จำนวนสมาชิก(คน)'
	);
	$no=0;

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			//is_numeric($rs->groupage)?($rs->groupage*5).' - '.($rs->groupage*5+4):'ไม่ระบุ',
			$rs->ageRange,
			$rs->amt
			. '<nav class="nav -icons -hover"><a href="'.url('saveup/report/member/memage/bygroup','age='.$rs->ageRange).'"><i class="icon -material">find_in_page</i></a></nav>'
		);
	}
	$tables->tfoot[]=array('รวม',$dbs->sum->amt);

	$ret .= $tables->build();






	// Show Member List
	if ($getAge) {
		$stmt = 'SELECT
			a.*
			, CASE
				WHEN a.`memberAge` >= 30 THEN "30 ปีขึ้นไป"
				WHEN a.`memberAge` >= 25 THEN "25-29 ปี"
				WHEN a.`memberAge` >= 20 THEN "20-24 ปี"
				WHEN a.`memberAge` >= 15 THEN "15-19 ปี"
				WHEN a.`memberAge` >= 10 THEN "10-14 ปี"
				WHEN a.`memberAge` >= 6 THEN "6-9 ปี"
				WHEN a.`memberAge` = 5 THEN "5 ปี"
				WHEN a.`memberAge` = 4 THEN "4 ปี"
				WHEN a.`memberAge` = 3 THEN "3 ปี"
				WHEN a.`memberAge` = 2 THEN "2 ปี"
				WHEN a.`memberAge` = 1 THEN "1 ปี"
				WHEN a.`memberDay` < 365 THEN "< 1 ปี"
			END `ageRange`
			FROM (
				SELECT
				`mid`
				, CONCAT(`firstname`," ",`lastname`) name
				, `birth`
				, `date_regist`
				, `date_approve`
				, DATEDIFF(NOW(),`date_approve`) `memberDay`
				, YEAR(NOW())-YEAR(`date_approve`) `memberAge`
				FROM %saveup_member%
				WHERE status = "active" AND `date_approve` IS NOT NULL
			) a
			HAVING `ageRange` = :ageRange
			ORDER BY `memberAge` ASC
			';

		$dbs = mydb::select($stmt,':ageRange', $getAge);
		//debugMsg(mydb()->_query);

		$tables = new Table();
		$tables->addClass('saveup-report-detail');
		$tables->thead=array(
			'no'=>'ลำดับ',
			'id -nowrap' => 'เลขที่',
			'ชื่อ-นามสกุล',
			'date register'=>'วันที่เริ่มเป็นสมาชิก',
			'date birth'=>'วันเกิด',
			'amt age'=>'อายุสมาชิก(ปี)'
		);
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				++$no, $rs->mid,
				$rs->name,
				$rs->date_regist?sg_date($rs->date_regist,'ว ดด ปปปป'):'',
				$rs->birth?sg_date($rs->birth,'ว ดด ปปปป'):'',
				$rs->memberAge,
			);
		}

		$ret .= $tables->build();
	}

	$ret .= '<style type="text/css">
	.saveup-report {display: flex;}
	.saveup-report>first-child {flex: 1;}
	</style>';
	$ret .= '</section>';

	return $ret;
}
?>