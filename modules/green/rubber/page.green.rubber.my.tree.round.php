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

function green_rubber_my_tree_round($self, $plantId) {
	$shopId = ($shopInfo = R::Model('green.shop.get', 'my')) ? $shopInfo->shopId : location('green/my/shop');

	$getLandId = post('land');

	new Toolbar($self, 'ธนาคารต้นไม้ @'.$shopInfo->name,'my.tree');

	$isAdmin = user_access('administer ibuys');
	$isEdit = $shopInfo->RIGHT & _IS_EDITABLE;
	//$isAddLand = $isEdit || in_array($shopInfo->is->membership,array('NETWORK'));

	$ret = '<section>';

	$ret .= '<header class="header">'._HEADER_BACK.'<h3>เส้นรอบวง</h3></header>';

	$form = new Form(NULL, url('green/my/info/tree.round.save/'.$plantId), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'back | load: .box-page | load');

	$form->addField(
		'round',
		array(
			'type' => 'text',
			'label' => 'เส้นรอบวงของต้นไม้ (เซ็นติเมตร)',
			'class' => '-fill',
			'require' => true,
			'placeholder' => '0.00',
			'description' => 'วิธีวัดเส้นรอบวงของต้นไม้ ให้วัดเส้นรอบวงของต้นไม้ที่ความสูงจากพื้นดิน 130 เซ็นติเมตร โดยวัดเส้นรอบวงเป็นหน่วยเซ็นติเมตร',
		)
	);

	$form->addField(
		'height',
		array(
			'type' => 'text',
			'label' => 'ความสูงของต้นไม้ (เซ็นติเมตร)',
			'class' => '-fill',
			'placeholder' => '0.00',
			'description' => 'วิธีวัดความสูงของต้นไม้ ให้วัดความสูงของต้นไม้เป็นหน่วยเซ็นติเมตร หากวัดเป็นหน่วยเมตรให้คูณด้วย 100 ก่อน',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" data-rel="back"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);


	$ret .= $form->build();

	$ret .= '</section>';

	return $ret;
}
?>