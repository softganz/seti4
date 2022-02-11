<?php
/**
* Green :: Change Land Owner
*
* @param Object $self
* @param Int $landId
* @return String
*
* @usage green/my/land/move/{$Id}
*/

$debug = true;

function green_my_land_change_owner($self, $landId = NULL) {
	$landInfo = R::Model('green.land.get', $landId, '{data: "orgInfo"}');
	$orgInfo = $landInfo->orgInfo;

	$isAdmin = is_admin('green') || $orgInfo->RIGHT & _IS_ADMIN;
	$isEdit = $isAdmin || $shopInfo->RIGHT & _IS_EDITABLE;

	if (!$isEdit) return message('error', 'Access Denied');

	$ret .= '<header class="header">'._HEADER_BACK.'<h3>เปลี่ยนเจ้าของ</h3></header>';

	$form = new Form(NULL, url('green/my/info/land.owner/'.$landId), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load');

	$form->addField('newuid', array('type' => 'hidden'));
	$form->addField(
		'ownerto',
		array(
			'type' => 'text',
			'label' => 'เจ้าของใหม่:',
			'class' => 'sg-autocomplete -fill',
			'require' => true,
			'attr' => array(
				'data-query' => url('api/user'),
				'data-altfld' => 'edit-newuid',
			),
			'placeholder' => 'ป้อนชื่อสมาชิก',
			//'<input type="text" name="orgname" class="sg-autocomplete form-text -fill" data-query="'.url('api/user').'" data-altfld="officer-uid" placeholder="ป้อนชื่อสมาชิก" data-select="label" />',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done</i><span>เปลี่ยนเจ้าของ</span>',
			'container' => '{class: "-sg-text-right"}'
		)
	);

	$ret .= $form->build();

	//$ret .= print_o($landInfo, '$landInfo');

	return $ret;
}
?>