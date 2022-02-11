<?php
function set_admin($self) {
	$ret.='<h3>Administrator</h3>';
	$ret.='<nav class="nav -page">';
	$ui=new ui();
	$ui->add('<a class="sg-action" href="'.url('set/admin/user').'" data-rel="#set-info">Users</a>');
	$ui->add('<a class="sg-action" href="'.url('set/price').'" data-rel="#set-info">Realtime Price</a>');
	$ui->add('<a class="sg-action" href="'.url('set/closeprice').'" data-rel="#set-info">Update Close Price</a>');
	$ui->add('<a class="sg-action" href="'.url('set/admin/history').'" data-rel="#set-info">History</a>');
	$ui->add('<a href="http://www.settrade.com/C13_MarketSummary.jsp?detail=STOCK_TYPE&type=W" target="_blank">Worrent</a>'); // Direct link http://www.settrade.com/C13_MarketSummaryStockType.jsp?type=W cannot cal from outside settrade
	$ret.=$ui->build('ul');
	$ret.='</nav>';

	$ret.='<div id="set-info">';
	$ret.=R::Page('set.admin.user',$self);

	$ui=new ui();
	$ui->add('<a class="sg-action" href="'.url('set/admin/chartsrc').'" data-rel="#set-info">Set chart from</a>');
	$ret.=$ui->build('ul');
	$ret.='</div>';
	return $ret;
}
?>