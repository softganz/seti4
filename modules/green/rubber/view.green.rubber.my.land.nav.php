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

function view_green_rubber_my_land_nav($landInfo = NULL) {
	$ui = new Ui(NULL, 'ui-nav -main');

	$ui->add('<a href="'.url('green/rubber/my').'"><i class="icon -material">account_balance</i><span>หน้าหลัก</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/my/tree').'"><i class="icon -material">nature</i><span>ต้นไม้</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/my/tree/land').'" data-rel="box" data-width="320"><i class="icon -material">nature_people</i><span>แปลง</span></a>');
	$ui->add('<a class="sg-action -btn-hot" href="'.url('green/rubber/my/land/form',array('land' => $landInfo->landid)).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>เพิ่มแปลง</span></a>');

	return Array('main' => $ui);
}
?>