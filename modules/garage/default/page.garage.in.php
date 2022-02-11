<?php
/**
* Garage Car In
* Created 2019-12-20
* Modify  2019-12-20
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_in($self, $jobId = NULL) {
	$getSearch = post('q');

	$shopInfo = R::Model('garage.get.shop');

	$shopId = $shopInfo->shopid;

	R::Model('garage.verify',$self, $shopInfo,'CARIN');

	$toolbar = new Toolbar($self,'รับรถ');

	mydb::where('j.`shopid` IN ( :shopid ) AND j.`templateid` IS NOT NULL');
	if ($jobId) mydb::where('j.`tpid` = :jobid', ':jobid', $jobId);
	else if ($getSearch) mydb::where('j.`plate` LIKE :getSearch', ':getSearch', '%'.$getSearch.'%');

	$stmt = 'SELECT
		  j.`tpid`
		, j.`jobno`
		, j.`plate`
		, j.`cartype`
		, j.`carindate`
		, j.`rcvby`
		, u.`username`
		, u.`name` `posterName`
		, (SELECT COUNT(*) FROM %garage_qt% WHERE `tpid` = j.`tpid`) `haveQT`
		FROM %garage_job% j
			LEFT JOIN %users% u ON u.`uid` = j.`rcvby`
		%WHERE%
		-- HAVING `haveQT` = 0
		ORDER BY j.`tpid` DESC
		';

	$dbs = mydb::select($stmt, ':shopid', 'SET:'.$shopInfo->branchId);
	//$ret .= mydb()->_query;

	$jobUi = new Ui('div', 'ui-card');
	$jobUi->addConfig('nav', '{class: "nav -master -myjob"}');

	$tables = new Table();
	$tables->thead = array('jobno -nowrap' => 'เลขใบซ่อม', 'rcvdate -date -nowrap' => 'วันรับรถ', 'plate -fill -hover-parent' => 'ทะเบียน');

	$jobUi->add(
		'<i class="icon -material -i48">add_circle</i><span>รับรถ<br />&nbsp;</span>',
		array(
			'class' => 'sg-action -addjob',
			'href' => url('garage/job/*/in'),
			'data-webview' => 'รับรถ',
			'onclick' => '',
		)
	);

	foreach ($dbs->items as $rs) {
		if ($rs->haveQT) {
			$itemUi = new Ui();
			$itemUi->addConfig('nav', '{class: "nav -hover"}');
			$itemUi->add('<a href="'.url('garage/job/'.$rs->tpid).'"><i class="icon -material">find_in_page</i></a>');
			$tables->rows[] = array(
				$rs->jobno,
				$rs->carindate ? sg_date($rs->carindate,'d/m/ปปปป') : '*',
				$rs->plate
				. $itemUi->build(),
			);
		} else {
			$userUi = new Ui(NULL,'-user -sg-flex -justify-right');
			$userUi->add('<img src="'.model::user_photo($rs->username).'" width="24" height="24" title="'.htmlspecialchars($rs->posterName).'" />');
			$jobUi->add(
				$userUi->build()
				. '<i class="icon -i48"></i>'
				. '<span>'.$rs->plate.'<br />'.$rs->brandid.'</span>',
				array(
					'class' => 'sg-action -car-type-'.$rs->cartype,
					'href' => url('garage/job/'.$rs->tpid.'/in'),
					'data-webview' => htmlspecialchars($rs->plate.' ('.$rs->insurername.')'),
					'onclick' => '',
				)
			);
		}
	}

	$ret .= $jobUi->build();

	$ret .= $tables->build();

	//$ret .= print_o($shopInfo, '$shopInfo');

	return $ret;
}
?>