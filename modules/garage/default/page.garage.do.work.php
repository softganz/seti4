<?php
/**
* Garage do in work
* Created 2019-12-04
* Modify  2019-12-04
*
* @param Object $self
* @return String
*/

$debug = true;

function garage_do_work($self) {
	$shopInfo = R::Model('garage.get.shop');
	$shopId = $shopInfo->shopid;

	$ret = '';

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

	new Toolbar($self,'สั่งงาน '.$dbs->count().' ใบ','do');


	$jobUi = new Ui('div a', 'ui-card');
	foreach ($dbs->items as $rs) {
		$userUi = new Ui(NULL,'-user -sg-flex -justify-left');
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
			)
		);
	}

	$ret.='<nav class="nav -master">'.$jobUi->build().'</nav>'._NL;

	return $ret;
}
?>