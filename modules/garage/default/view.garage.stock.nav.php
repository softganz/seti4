<?php
function view_garage_stock_nav($stkid=NULL,$options='{}') {
	$preText = '<form id="search" class="search-box" method="get" action="'.url('garage/stock').'" role="search"><input type="hidden" name="jid" id="jid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="รหัสสินค้า/อะไหล่" data-query="'.url('garage/api/repaircode').'" data-callback="'.url('garage/stock').'" data-altfld="jid"><button class="btn" type="submit"><i class="icon -material">search</i><span>ค้นหา</span></button></form>'._NL;

	$ui = new ui(NULL,'ui-nav');

	$ui->add('<a class="btn" href="'.url('garage/stock').'" title="สินค้าคงคลัง"><i class="icon -home"></i><span class="-hidden">สินค้าคงคลัง</span></a>');
	if ($stkid) {
		$ui->add('<a class="btn" href="'.url('garage/stock/'.$stkid.'/card').'" title="Stock Card"><i class="icon -list"></i><span>Stock Card</span></a>');
		$ui->add('<a class="btn" href="'.url('garage/stock/'.$stkid.'/sheet').'" title="Stock Sheet"><i class="icon -list"></i><span>Stock Sheet</span></a>');
	}

	$dboxUi = new Ui(NULL,'ui-nav');
	$dboxUi->add('<a class="" href="'.url('garage/stock/'.$stkid.'/repair').'" title=""><i class="icon -refresh"></i><span>คำนวณต้นทุนใหม่</span></a>');
	$dboxUi->add('<a class="sg-action" href="'.url('garage/stock/*/repairall').'" data-title="คำนวณต้นทุนใหม่ทั้งหมด" data-confirm="ต้องการคำนวณต้นทุนใหม่ทั้งหมด กรุณายืนยัน?"><i class="icon -refresh"></i><span>คำนวณต้นทุนใหม่ทั้งหมด</span></a>');

	//$ret.=sg_dropbox($dboxUi->build(),'{class:"leftside -atright"}');

	return Array('main' => $ui, 'more' => $dboxUi, 'preText' => $preText);
}
?>