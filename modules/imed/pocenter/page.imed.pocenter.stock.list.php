<?php
/**
* Module Method
*
* @param 
* @return String
*/

$debug = true;

function imed_pocenter_stock_list($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

	$centerInfo = is_object($orgId) ? $orgId : R::Model('imed.pocenter.get', $orgId, '{}');


	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $orgInfo->is->socialtype;

	$ret = '';
	// $ret .= i()->uid.print_o($centerInfo, '$centerInfo');

	$isAdmin = user_access('administer imeds')
					|| $centerInfo->RIGHT & _IS_ADMIN;
	$isEditable = $isAdmin || $centerInfo->is->officer
		|| in_array($centerInfo->officers[i()->uid], ['ADMIN','MODERATOR']);

	$ret .= '<section id="imed-pocenter-stock" class="" data-url="'.url('imed/pocenter/stock/list/'.$orgId).'">';
	$ret .= '<header class="header"><h3>รายชื่อกายอุปกรณ์</h3></header>';

	$stmt = 'SELECT tg.`stkid`, tg.`name`, s.`balanceamt`
					FROM %imed_stkcode% tg
						LEFT JOIN %po_stk% s ON s.`stkid` = tg.`stkid` AND s.`orgid` = :orgid
					WHERE `parent` IN ( "01", "03" )
					ORDER BY CONVERT(`name` USING tis620) ASC';
	$dbs = mydb::select($stmt, ':orgid', $orgId);

	$tables = new Table();
	$tables->thead = array('name -fill'=>'อุปกรณ์', 'balance -amt -nowrap'=>'จำนวนคงเหลือ','icons -nowrap'=>'');
	foreach ($dbs->items as $rs) {
		$ui = new Ui();
		$uiDrop = new Ui(NULL,'ui-menu');
		if ($isEditable) {
			$ui->add('<a class="sg-action" href="'.url('imed/pocenter/'.$orgId.'/stock.in/'.$rs->stkid).'" data-rel="box" title="รับเข้ากายอุปกรณ์" data-width="640" data-height="80%"><i class="icon -material">add_circle_outline</i></a>');
			$ui->add('<a class="sg-action" href="'.url('imed/pocenter/'.$orgId.'/stock.out/'.$rs->stkid).'" data-rel="box" title="จ่ายออกกายอุปกรณ์" data-width="640" data-height="80%"><i class="icon -material">remove_circle_outline</i></a>');
			$ui->add('<a class="sg-action" href="'.url('imed/pocenter/'.$orgId.'/stock.card/'.$rs->stkid).'" data-rel="box" title="รายการบันทึก" data-width="640" data-height="80%"><i class="icon -material">view_list</i></a>');

			$uiDrop->add('<a class="sg-action" href="'.url('imed/pocenter/'.$orgId.'/stock.in/'.$rs->stkid).'" data-rel="box" data-width="640" data-height="80%"><i class="icon -material">add_circle_outline</i><span>รับเข้ากายอุปกรณ์</span></a>');
			$uiDrop->add('<a class="sg-action" href="'.url('imed/pocenter/'.$orgId.'/stock.out/'.$rs->stkid).'" data-rel="box" data-width="640" data-height="80%"><i class="icon -material">remove_circle_outline</i><span>จ่ายออกกายอุปกรณ์</span></a>');

			$uiDrop->add('<a class="sg-action" href="'.url('imed/pocenter/'.$orgId.'/stock.torepair/'.$rs->stkid).'" data-rel="box" data-width="640" data-height="80%"><i class="icon -material">remove_circle_outline</i><span>ส่งซ่อม</span></a>');
			$uiDrop->add('<a class="sg-action" href="'.url('imed/pocenter/'.$orgId.'/stock.fromrepair/'.$rs->stkid).'" data-rel="box" data-width="640" data-height="80%"><i class="icon -material">remove_circle_outline</i><span>รับคืนจากซ่อม</span></a>');

			$uiDrop->add('<a class="sg-action" href="'.url('imed/pocenter/'.$orgId.'/stock.card/'.$rs->stkid).'" data-rel="box" data-width="640" data-height="80%"><i class="icon -material">view_list</i><span>รายการบันทึก</span></a>');
			$ui->add(sg_dropbox($uiDrop->build()));
		}
		$menu = $ui->count() ? '<nav class="nav -icons">'.$ui->build().'</nav>' : '';

		$tables->rows[] = array(
			$rs->name,
			$rs->balanceamt ? number_format($rs->balanceamt,2) : '',
			$menu,
		);
	}
	$ret .= $tables->build();

	$ret .= '</section>';
	return $ret;
}
?>