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

function view_green_my_plant_nav($landInfo = NULL) {
	$ui = new Ui(NULL, 'ui-nav -main');

	//$ui->add('<a href="'.url('green/my/shop').'"><i class="icon -material">account_balance</i><span>กลุ่ม</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/my/plant').'"><i class="icon -material">nature</i><span>พืซแซม</span></a>');
	$ui->add('<a class="sg-action" href="'.url('green/my/plant/land').'" data-rel="box" data-width="320"><i class="icon -material">nature_people</i><span>แปลง</span></a>');
	$ui->add('<a class="sg-action -btn-hot" href="'.url('green/my/plant/form',array('land' => $landInfo->landid)).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>ปลูกพืช</span></a>');

	return Array('main' => $ui);
}
?>