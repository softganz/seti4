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

function view_green_rubber_my_tree_nav($landInfo = NULL) {
	$ui = new Ui(NULL, 'ui-nav -main');

	$ui->add('<a class="sg-action" href="'.url('green/my/shop/select', array('ret' => 'green/rubber/my/tree')).'" data-rel="box" data-width="480"><i class="icon -material">account_balance</i><span>กลุ่ม</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/rubber/my/tree/land').'" data-rel="box" data-width="320"><i class="icon -material">nature_people</i><span>แปลง</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/rubber/my/tree').'"><i class="icon -material">nature</i><span>ต้นไม้</span></a>');
	$ui->add('<a class="sg-action -add" href="'.url('green/rubber/my/tree/form',array('land' => $landInfo->landid)).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>ปลูกไม้</span></a>');

	$moreUi = new Ui(NULL, 'ui-nav');
	$moreUi->add('<a><i class="icon -material">info</i><span>More Menu for Drop Down</span></a>');

	return Array('main' => $ui, 'more' => $moreUi);
}
?>