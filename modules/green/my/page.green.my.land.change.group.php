<?php
/**
* Green :: Move Land To Group
*
* @param Object $self
* @param Int $landId
* @return String
*
* @usage green/my/land/move/{$Id}
*/

$debug = true;

function green_my_land_change_group($self, $landId = NULL) {
	$landInfo = R::Model('green.land.get', $landId, '{data: "orgInfo"}');
	$orgInfo = $landInfo->orgInfo;

	$isAdmin = is_admin('green') || $orgInfo->RIGHT & _IS_ADMIN;
	$isEdit = $isAdmin || $shopInfo->RIGHT & _IS_EDITABLE;

	if (!$isEdit) return message('error', 'Access Denied');

	$ret .= '<header class="header">'._HEADER_BACK.'<h3>ย้ายกลุ่ม</h3></header>';

	$orgOptions = array('== เลือกกลุ่ม ==');
	foreach (R::Model('green.shop.get', NULL, '{debug: false, limit: "*"}') as $item) {
	 	$orgOptions[$item->shopid] = $item->name;
	 } 
	$form = new Form(NULL, url('green/my/info/land.move/'.$landId), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load:'.url('green/rubber/my/land'));

	$form->addField(
		'moveto',
		array(
			'type' => 'select',
			'label' => 'ย้ายไปกลุ่ม:',
			'class' => '-fill',
			'require' => true,
			'options' => $orgOptions,
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done</i><span>ย้ายกลุ่ม</span>',
			'container' => '{class: "-sg-text-right"}'
		)
	);

	$ret .= $form->build();

	//$ret .= print_o($landInfo, '$landInfo');

	return $ret;
}
?>