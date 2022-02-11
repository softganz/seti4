<?php
/**
* My GoGreen Shop
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function green_rubber_my_org($self, $orgId = NULL) {
	if ($orgId) return R::Page('green.rubber.my.org.view', $self, $orgId);

	$getSearch = post('q');

	$isAdmin = is_admin('green');

	$toolbar = new Toolbar($self, 'กลุ่ม @สวนยางยั่งยืน');

	$listOptions = array(
		'href' => url('green/rubber/my/org/$id'),
		'data-webview' => true,
		'show' => post('show'),
	);

	if ($isAdmin) {
		if ($getSearch) $listOptions['search'] = $getSearch;

		$form = new Form(NULL, url('green/rubber/my/org'), NULL, 'sg-form');
		$form->addData('rel', '#main');
		$form->addField(
			'q',
			array(
				'type' => 'text',
				'class' => '-fill',
				'value' => $getSearch,
				'placeholder' => 'ค้นชื่อกลุ่ม',
				'posttext' => '<div class="input-append"><span><button class="btn" type="submit"><i class="icon -material">search</i></button></span></div>',
				'container' => '{class: "-group"}',
			)
		);

		$ret .= $form->build();
	}

	$ret .= R::View('green.my.select.org', $listOptions)->build();

	return $ret;
}
?>