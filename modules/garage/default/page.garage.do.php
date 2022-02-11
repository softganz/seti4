<?php
function garage_do($self) {
	$shopInfo = R::Model('garage.get.shop');
	$shopId = $shopInfo->shopid;

	R::Model('garage.verify',$self, $shopInfo,'JOB');

	$stmt = 'SELECT
		j.*
		, s.`shortName` `shopShortName`
		, b.`brandname`
		, COUNT(tr.`jobtrid`) `totalCmd`
		FROM %garage_job% j
			LEFT JOIN %garage_shop% s USING (`shopid`)
			LEFT JOIN %garage_brand% b ON b.`shopid` = j.`shopid` AND b.`brandid` = j.`brandid`
			LEFT JOIN %garage_jobtr% tr USING(`tpid`)
		WHERE (j.`shopid` = :shopid OR (s.`shopparent` = :shopid AND s.`shopinclude` = "YES")) AND j.`iscarreturned` != "Yes"
		GROUP BY j.`tpid`
		ORDER BY j.`tpid` DESC
		';

	$dbs = mydb::select($stmt,':shopid',$shopInfo->shopid);

	new Toolbar($self,'สั่งงาน '.$dbs->count().' ใบ','do');

	$tables = new Table();

	$tables->addClass('-center');
	$tables->thead = array('เลขใบซ่อม','rcvdate -date'=>'วันรับรถ','ทะเบียน','รายละเอียดรถ','tran -amt -hover-parent'=>'รายการสั่งซ่อม');

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->jobno,
			sg_date($rs->rcvdate,'ว ดด ปป'),
			$rs->plate,
			$rs->brandname.' '.$rs->model.$rs->color,
			($rs->totalCmd ? $rs->totalCmd : '')
			. '<nav class="nav -icon -hover"><a href="'.url('garage/job/'.$rs->tpid.'/do').'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a></nav>',
			'config' => array('class'=>'-shop-'.$rs->shopShortName)
		);
	}
	$ret.=$tables->build();

	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>