<?php
/**
* My Rubber Sam Tree Menu
* Created 2020-09-10
* Modify  2020-09-10
*
* @param Int $landInfo
* @return String
*/

$debug = true;

function view_ibuy_green_my_samtree_nav($landInfo = NULL) {
	$ui = new Ui();

	$ui->addConfig('nav', '{class: "nav -page -app-icon"}');

	$ui->add('<a href="'.url('ibuy/green/my/shop').'"><i class="icon -material">account_balance</i><span>องค์กร</span></a>');
	$ui->add('<a class="sg-action" href="'.url('ibuy/green/my/samtree').'"><i class="icon -material">nature</i><span>พืซแซม</span></a>');
	$ui->add('<a class="sg-action" href="'.url('ibuy/green/my/samtree/land').'" data-rel="box" data-width="320"><i class="icon -material">nature_people</i><span>แปลง</span></a>');
	$ui->add('<a class="sg-action -btn-hot" href="'.url('ibuy/my/samtree/form',array('land' => $landInfo->landid)).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>ปลูกพืช</span></a>');

	$ret = $ui->build()._NL;

	return $ret;
}
?>