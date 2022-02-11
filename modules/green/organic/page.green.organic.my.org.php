<?php
/**
* Green :: My Organic Organization
*
* @param Object $self
* @param Int $orgId
* @return String
*/

$debug = true;

function green_organic_my_org($self, $orgId = NULL) {
	if ($orgId) return R::Page('green.organic.my.org.view', $self, $orgId);

	$getSearch = post('q');

	$isAdmin = is_admin('green');

	$myShopList = R::Model('green.shop.get', array('my' => '*'), '{debug: false, limit: "*"}');

	$toolbar = new Toolbar($self, 'กลุ่ม @Green Smile');

	$listOptions = array(
		'href' => url('green/organic/my/org/$id'),
		'data-webview' => true,
		'show' => post('show'),
	);

	if ($isAdmin) {
		if ($getSearch) $listOptions['search'] = $getSearch;

		$form = new Form(NULL, url('green/organic/my/org'), NULL, 'sg-form');
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

	head('<script type="text/javascript">
	function onWebViewComplete() {
		console.log("CALL onWebViewComplete FROM WEBVIEW")
		var options = {}
		menu = []
		//menu.push({id: "add", label: "สร้างกลุ่ม", title: "สร้างกลุ่ม", link: "green/my/org/new?ref=green/organic/my/land", options: {actionBar: true}})
		options.menu = menu
		return options
	}
	</script>');

	return $ret;
}
?>