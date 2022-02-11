<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_my_service($self) {
	$shopInfo = R::Model('garage.get.shop');
	$shopId = $shopInfo->shopid;

	$ret = '';

	if (i()->ok && $shopId) {
		$stmt = 'SELECT *, t.`jobno`, t.`plate` FROM %garage_do% d LEFT JOIN %garage_job% t USING(`tpid`) WHERE `uid` = :uid';
		$dbs = mydb::select($stmt, ':uid', i()->uid);

		$JobUi = new Ui('div a', 'ui-card');
		foreach ($dbs->items as $rs) {
			$JobUi->add(
				'<i class="icon -i48 -job"></i><span>'.$rs->plate.'</span>',
				array(
					'class' => 'sg-action',
					'href' => url('garage/job/'.$rs->tpid.'/tech'),
					'data-webview' => true,
					'data-webview-title' => $rs->plate,
					'onclick' => '',
				)
			);
		}

		$ret.='<nav class="nav -master">'.$JobUi->build().'</nav>'._NL;

	}

	/*
	$ui = new Ui();
	$ui->add('<a href="'.url('garage/job').'"><i class="icon -i48 -job"></i><span>สั่งซ่อม</span></a>');
	$ui->add('<a href="'.url('garage/do').'"><i class="icon -i48 -do"></i><span>สั่งงาน</span></a>');
	$ui->add('<a href="'.url('garage/part').'"><i class="icon -i48 -part"></i><span>อะไหล่</span></a>');
	$ui->add('<a href="'.url('garage/finance').'"><i class="icon -i48 -finance"></i><span>การเงิน</span></a>');
	$ui->add('<a href="'.url('garage/report').'"><i class="icon -i48 -report"></i><span>วิเคราะห์</span></a>');

	$ret.='<nav class="nav -master">'.$ui->build().'</nav>'._NL;
	*/

	//$ret .= print_o($shopInfo,'$shopInfo');
	return $ret;
}
?>