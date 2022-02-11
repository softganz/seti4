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

function garage_app($self) {
	$shopInfo = R::Model('garage.get.shop');
	$shopId = $shopInfo->shopid;

	cfg('navigator.garage', cfg('navigator.garage.verify'));

	$isEditable = in_array($shopInfo->iam, array('ADMIN','MANAGER','ACCOUNTING','FOREMAN'));

	//$ret = '@'.date('H:i:s');

	if (i()->ok && $shopId) {
		$jobUi = new Ui('div', 'ui-card');

		if (R::Model('garage.right', $shopInfo, 'CARIN')) {
			$jobUi->add(
				'<ul class="ui-action -user"><li class="ui-item"><i class="icon -material -white">add_circle</i></li></ul>'
				.'<i class="icon -i48 -carin"></i><span>รับรถ<br />&nbsp;</span>',
				array(
					'class' => 'sg-action -addjob',
					'href' => url('garage/in'),
					'data-webview' => 'รับรถ',
					'onclick' => '',
				)
			);
		}

		$stmt = 'SELECT
			d.`tpid`,j.`jobno`, j.`plate`, j.`brandid`
			, i.`insurername`
			, (SELECT GROUP_CONCAT(`username`) FROM %garage_do% ud LEFT JOIN %users% u USING(`uid`) WHERE `tpid` = d.`tpid`) `userList`
			FROM %garage_do% d
				LEFT JOIN %garage_job% j USING(`tpid`)
				LEFT JOIN %garage_insurer% i USING(`insurerid`)
			WHERE d.`uid` = :uid AND d.`status` = "OPEN" AND j.`iscarreturned` != "Yes"
			ORDER BY CONVERT(`plate` USING tis620) ASC';

		$dbs = mydb::select($stmt, ':uid', i()->uid);
		//$ret .= mydb()->_query;

		foreach ($dbs->items as $rs) {
			$userUi = new Ui(NULL,'-user -sg-flex -justify-right');
			foreach (explode(',', $rs->userList) as $username) {
				$userUi->add('<img src="'.model::user_photo($username).'" width="24" height="24" />');
			}
			$jobUi->add(
				$userUi->build()
				. '<i class="icon -i48 -job"></i>'
				. '<span>'.$rs->plate.'<br />'.$rs->brandid.'</span>',
				array(
					'class' => 'sg-action',
					'href' => url('garage/job/'.$rs->tpid.'/tech'),
					'data-webview' => htmlspecialchars($rs->plate.' ('.$rs->insurername.')'),
					'onclick' => '',
				)
			);
		}

		$jobUi->add(
			'<ul class="ui-action -user"><li class="ui-item"><i class="icon -material -white">add_circle</i></li></ul>'
			.'<i class="icon -i48 -addjob"></i><span>เพิ่มจ็อบ<br />&nbsp;</span>',
			array(
				'class' => 'sg-action -addjob',
				'href' => url('garage/job/addtech'),
				'data-rel' => '#main',
				'onclick' => '',
			)
		);

		if (R::Model('garage.right', $shopInfo, 'INVENTORY')) {
			$stmt = 'SELECT COUNT(*) `waitCount` FROM %garage_jobtr% tr LEFT JOIN %garage_job% j USING(`tpid`) WHERE `wait` > 0 AND (j.`shopid` = :shopid OR (j.`shopid` IN (SELECT `shopid` FROM %garage_shop% WHERE `shopparent` = :shopid))) LIMIT 1';
			$waitCount = mydb::select($stmt, ':shopid', $shopId)->waitCount;
			$userUi = new Ui(NULL,'-user -sg-flex -justify-right');
			$userUi->add('<span class="count">'.$waitCount.'</span>');

			$jobUi->add(
				$userUi->build()
				. '<i class="icon -i48 -part"></i><span>อะไหล่<br />&nbsp;</span>',
				array(
					'class' => 'sg-action -addjob',
					'href' => url('garage/part'),
					'data-webview' => 'อะไหล่',
					'onclick' => '',
				)
			);

		}

		$ret.='<nav class="nav -master -myjob">'.$jobUi->build().'</nav>'._NL;

	}

	if ($isEditable) {

		$stmt = 'SELECT
			do.`tpid`, j.`plate`, j.`brandid`
			, (SELECT GROUP_CONCAT(`username`) FROM %garage_do% ud LEFT JOIN %users% u USING(`uid`) WHERE `tpid` = do.`tpid`) `userList`
			FROM %garage_do% do
				LEFT JOIN %garage_job% j USING(`tpid`)
			WHERE do.`status` = "OPEN"
				AND (j.`shopid` = :shopid OR (j.`shopid` IN (SELECT `shopid` FROM %garage_shop% WHERE `shopparent` = :shopid)))
				AND j.`iscarreturned` = "No"
			GROUP BY `tpid`
			';

		$dbs = mydb::select($stmt, ':shopid', $shopId);

		$ret .= '<header class="header"><h3>ใบสั่งงานทั้งหมด '.$dbs->count().' ใบ</h3></header>';

		$jobUi = new Ui('div a', 'ui-card');
		foreach ($dbs->items as $rs) {
			$userUi = new Ui(NULL,'-user -sg-flex -justify-right');
			foreach (explode(',', $rs->userList) as $username) {
				$userUi->add('<img src="'.model::user_photo($username).'" width="24" height="24" />');
			}
			$jobUi->add(
				$userUi->build().'<i class="icon -i48 -job"></i><span>'.$rs->plate.'<br />'.$rs->brandid.'</span>',
				array(
					'class' => 'sg-action',
					'href' => url('garage/job/'.$rs->tpid.'/tech'),
					'data-webview' => true,
					'data-webview-title' => $rs->plate,
					'onclick' => '',
				)
			);
		}

		$ret.='<nav class="nav -master">'.$jobUi->build().'</nav>'._NL;

		//$ret .= print_o($dbs);
	}

	$ret .= '<div style="height: 80px;"></div>';

	//$ret .= print_o($shopInfo,'$shopInfo');

	return $ret;
}
?>