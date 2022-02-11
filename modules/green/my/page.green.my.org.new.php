<?php
/**
* Create New Org
* Created 2020-11-07
* Modify  2020-11-07
*
* @param Object $self
* @return String
*/

$debug = true;

function green_my_org_new($self) {
	$getRefUrl = post('ref');

	$ret = '<header class="header -hidden">'._HEADER_BACK.'<h3>สร้างกลุ่ม</h3></header>';

	if (!user_access('create own shop')) {
		return message('error','access denied:ขออภัยค่ะ ท่านยังไม่ได้รับสิทธิ์ในการสร้างกลุ่มใหม่');
	}

	if (post('name')) {
		$data = new stdClass();
		$data = (Object) array_slice(post(),1);
		$result = R::Model('green.shop.create', $data);
		//$ret .= print_o($result,'$result');
		//$ret .= print_o($data,'$data');
		if ($result->shopId) {
			$_SESSION['shopid'] = $result->shopId;
			location('green/organic/my/land');
		}
	}

	$form = new Form(NULL, url('green/organic/register'), 'green-my-org-new', 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'reload:'.url($getRefUrl));

	$form->addField(
		'name',
		array(
			'type' => 'text',
			'label' => 'ชื่อกลุ่ม',
			'class' => '-fill',
			'require' => true,
			'value' => htmlspecialchars($name),
			'placeholder' => 'ระบุชื่อกลุ่ม'
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done</i><span>สร้างกลุ่มใหม่</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	//$ret.=print_o($_POST,'$_POST');
	return $ret;
}
?>