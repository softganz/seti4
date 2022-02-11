<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_admin_member_zone($self) {
	$ret = '<h3>ผู้มีสิทธิ์เข้าถึงข้อมูลพื้นที่</h3>';

	$stmt = 'SELECT cop.`provid`, cop.`provname` `changwatName`, COUNT(*) `totals`
		FROM %db_userzone% z
			LEFT JOIN %co_province% `cop` ON cop.`provid` = LEFT(z.`zone`,2)
		GROUP BY `changwatName`
		ORDER BY CONVERT(`changwatName` USING tis620)';
	$dbs = mydb::select($stmt);

	$ui = new Ui();
	foreach ($dbs->items as $rs) {
		$ui->add('<a class="sg-action" href="'.url('imed/admin/member/zone',array('z'=>$rs->provid)).'" data-rel="#imed-app">'.$rs->changwatName.'</a>');
	}
	$ret .= '<nav class="nav -page">'.$ui->build().'</nav>';


	if ($zone = post('z')) mydb::where('z.`zone` LIKE :zone', ':zone', $zone.'%');
	$stmt = 'SELECT
		  z.*
		, u.`name`
		, u.`username`
		, u.`email`
		, u.`organization`
		, cop.`provname` `changwatName`
		, GROUP_CONCAT(CONCAT(`zone`,":",`module`,":",`refid`,":",`right`)) `right`
		, u.`admin_remark`
		FROM %db_userzone% z
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %co_province% `cop` ON cop.`provid` = LEFT(z.`zone`,2)
		%WHERE%
		GROUP BY `name`
		ORDER BY CONVERT(`changwatName` USING tis620),`name`,`module`,`refid`';

	$dbs = mydb::select($stmt);


	$tables = new Table();

	$tables->thead = array('no'=>'', 'Zone', 'Name', 'Right');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			++$no,
			$rs->changwatName,
			$rs->name.' ('.$rs->username.' '.$rs->email.')'
			.($rs->organization || $rs->admin_remark ? '<br /><i style="color:gray; font-size: 0.9em; font-style: normal;">'.$rs->organization.' '.$rs->admin_remark.'</i>' : ''),
			str_replace(',','<br />',$rs->right),
		);
	}
	$ret .= $tables->build();

	return $ret;
}
?>