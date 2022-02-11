<?php
/**
* Garage Part
* Created 2017-07-18
* Modify  2019-12-14
*
* @param Object $self
* @param Int $jobId
* @return String
*/

$debug = true;

function garage_part($self, $jobId = NULL) {
	$getSearch = post('q');

	$shopInfo = R::Model('garage.get.shop');

	$shopId = $shopInfo->shopid;

	R::Model('garage.verify',$self, $shopInfo,'INVENTORY');

	//if (!R::Model('garage.right',$shopInfo, 'INVENTORY')) return message('error', 'Access Denied');

	new Toolbar($self,'อะไหล่','part');


	mydb::where('c.`repairtype` = 2');
	if ($jobId) mydb::where('tr.`tpid` = :jobid', ':jobid', $jobId);
	else if ($getSearch) mydb::where('j.`plate` LIKE :getSearch', ':getSearch', '%'.$getSearch.'%');
	else mydb::where('j.`shopid` = :shopid AND tr.`wait` > 0');

	$stmt = 'SELECT
		tr.`jobtrid`, tr.`tpid`
		, j.`plate`
		, tr.`qty`, tr.`wait`, tr.`done`
		, DATEDIFF(NOW(), FROM_UNIXTIME(`created`, "%Y-%m-%d")) `daywait`
		, c.`repairname`
		, tr.`description`
		FROM %garage_jobtr% tr
			LEFT JOIN %garage_job% j USING(`tpid`)
		LEFT JOIN %garage_repaircode% c USING(`repairid`)
		%WHERE%
		ORDER BY `daywait` ASC, `jobtrid` DESC
		';

	$dbs = mydb::select($stmt, ':shopid', $shopId);

	$tables = new Table();
	$tables->addClass('-garage-job-tran');
	$tables->thead = array('ทะเบียนรถ', 'รายการอะไหล่', 'amt -numeric' => 'จำนวน', 'days -amt' => 'วัน', 'wait -center' => 'รอ');
	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->plate,
			$rs->repairname.($rs->description ? ' ('.$rs->description.')' : ''),
			$rs->qty,
			$rs->wait && $rs->daywait > 0 ? $rs->daywait : '',
			'<a class="sg-action -wait" href="'.url('garage/job/'.$rs->tpid.'/info/part.wait/'.$rs->jobtrid).'" data-rel="notify" data-done="javascript:$this.closest(\'tr\').toggleClass(\'-wait\')"><i class="icon -material">watch_later</i></a>',
			'config' => array('class' => $rs->wait ? 'item-part -wait' : ''),
		);
	}

	$ret .= $tables->build();

	//$ret .= print_o($dbs, '$dbs');
	//$ret .= print_o($shopInfo);
	return $ret;
}
?>