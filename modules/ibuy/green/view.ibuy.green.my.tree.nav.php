<?php
/**
* My Rubber Menu
* Created 2020-09-04
* Modify  2020-09-10
*
* @param Int $shopId
* @return String
*/

$debug = true;

function view_ibuy_green_my_tree_nav($landInfo = NULL) {
	$ui = new Ui();

	$ui->addConfig('nav', '{class: "nav -page -app-icon"}');

	$ui->add('<a href="'.url('ibuy/green/my/shop').'"><i class="icon -material">account_balance</i><span>องค์กร</span></a>');
	$ui->add('<a class="sg-action" href="'.url('ibuy/green/my/tree').'"><i class="icon -material">nature</i><span>ต้นไม้</span></a>');
	$ui->add('<a class="sg-action" href="'.url('ibuy/green/my/tree/land').'" data-rel="box" data-width="320"><i class="icon -material">nature_people</i><span>แปลง</span></a>');
	$ui->add('<a class="sg-action -btn-hot" href="'.url('ibuy/my/tree/form',array('land' => $landInfo->landid)).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>ปลูกไม้</span></a>');

	$ret = $ui->build()._NL;

	return $ret;
}
?>