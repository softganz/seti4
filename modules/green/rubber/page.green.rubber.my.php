<?php
/**
* Green Rubber : Main Page
* Created 2020-09-28
* Modify  2020-09-28
*
* @param Object $self
* @return String
*
* @usage green/rubber/{$Id}/method
*/

$debug = true;

function green_rubber_my($self, $orgId = NULL) {

	$ret = '<header class="header"><h3>จัดการข้อมูลสวนยางยั่งยืน</h3></header>';

	// ถ้ายังไม่เคยมีองค์กร ให้ถามแล้วสร้างองค์กร
	$myShopList = R::Model('green.shop.get', array('my' => '*'), '{debug: false, limit: "*"}');


	$mainUi = new Ui(NULL, NULL);
	$mainUi->addConfig('nav', '{class: "nav -app-menu"}');
	$mainUi->header('<h3>บันทึกข้อมูล</h3>');
	$mainUi->add('<a class="sg-action" href="'.url('green/rubber/my/org').'" data-webview="กลุ่ม"><i class="icon -material">account_balance</i><span>กลุ่ม</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('green/rubber/my/land').'" data-webview="แปลงสวนยาง"><i class="icon -material">nature_people</i><span>แปลงสวนยาง</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('green/rubber/my/rubber').'" data-webview="ต้นยาง"><i class="icon -material">nature</i><span>ต้นยาง</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('green/rubber/my/tree').'" data-webview="ธนาคารต้นไม้"><i class="icon -material">nature</i><span>ธนาคารต้นไม้</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('green/rubber/my/plant').'" data-webview="พืชผสมผสาน"><i class="icon -material">grass</i><span>พืชผสมผสาน</span></a>');
	$mainUi->add('<a class="sg-action" href="'.url('green/my/animal').'" data-webview="ปศุสัตว์"><i class="icon -material">emoji_nature</i><span>ปศุสัตว์</span></a>');
	$mainUi->add('<a class="sg-action -disabled" href="'.url('green/rubber/my/buy').'" data-webview="รับซื้อน้ำยาง"><i class="icon -material">money</i><span>รับซื้อน้ำยาง</span></a>');
	$mainUi->add('<a class="sg-action -disabled" href="'.url('green/rubber/my/gl').'" data-webview="บัญชีต้นทุน"><i class="icon -material">attach_money</i><span>บัญชีต้นทุน</span></a>');

	$ret .= $mainUi->build();

	return $ret;
}
?>