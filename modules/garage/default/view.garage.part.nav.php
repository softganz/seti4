<?php
function view_garage_part_nav($rs=NULL,$options='{}') {
	$searchTarget = url('garage/part');
	$selectTarget = url('garage/part');
	$searchForm = '<form class="search-box" method="get" action="'.$searchTarget.'" role="search"><input type="hidden" name="jobid" id="jobid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="40" value="'.post('q').'" placeholder="ป้อนทะเบียนรถหรือเลข job" data-query="'.url('garage/api/job').'" data-callback="'.$selectTarget.'" data-altfld="jobid"><button class="btn" type="submit"><i class="icon -material">search</i><span>ค้นหา</span></button></form>'._NL;

	$ui = new ui(NULL,'ui-nav');

	$ui->add('<a class="btn" href="'.url('garage/order').'" title="ใบสั่งของ"><i class="icon -list"></i><span>ใบสั่งของ</span></a>');
	$ui->add('<a class="btn" href="'.url('garage/aprcv').'" title="ใบรับของ"><i class="icon -list"></i><span>ใบรับของ</span></a>');
	$ui->add('<a class="btn" href="'.url('garage/req').'" title="ใบเบิกของ"><i class="icon -list"></i><span>ใบเบิกของ</span></a>');
	$ui->add('<a class="btn" href="'.url('garage/stock').'" title="สินค้าคงคลัง"><i class="icon -list"></i><span>สินค้าคงคลัง</span></a>');
	$ui->add('<a class="btn" href="javascript:window.print()" style="border-radius: 4px;"><i class="icon -print"></i><span class="-hidden">พิมพ์</span></a>');

	return Array('main' => $ui, 'preText' => $searchForm);
}
?>