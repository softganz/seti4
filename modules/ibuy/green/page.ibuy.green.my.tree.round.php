<?php
/**
* Green Smile : My Tree Land
* Created 2020-09-04
* Modify  2020-09-09
*
*
* @param Object $self
* @return String
*/

$debug = true;

function ibuy_green_my_tree_round($self, $plantId) {
	$shopId = ($shopInfo = R::Model('ibuy.shop.get', 'my')) ? $shopInfo->shopId : location('ibuy/green/my/shop');

	$getLandId = post('land');

	R::View('toolbar',$self, 'ธนาคารต้นไม้ @'.$shopInfo->name,'ibuy.green.my.tree');

	$isAdmin = user_access('administer ibuys');
	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;
	//$isAddLand = $isEdit || in_array($shopInfo->is->membership,array('NETWORK'));

	$ret = '<section>';

	$ret .= '<header class="header">'._HEADER_BACK.'<h3>เส้นรอบวง</h3></header>';

	$form = new Form(NULL, url('ibuy/my/info/tree.round.save/'.$plantId), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load');

	$form->addField(
		'round',
		array(
			'type' => 'text',
			'label' => 'เส้นรอบวง (เมตร)',
			'class' => '-fill',
			'require' => true,
			'placeholder' => '0.00',
		)
	);

	$form->addField(
		'height',
		array(
			'type' => 'text',
			'label' => 'ความสูง (เมตร)',
			'class' => '-fill',
			'placeholder' => '0.00',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);


	$ret .= $form->build();

	$ret .= '</section>';

	return $ret;
}
?>