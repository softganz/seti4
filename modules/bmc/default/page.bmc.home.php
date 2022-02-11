<?php
/**
* Module :: Description
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage bmc
*/

$debug = true;

function bmc_home($self) {

	$headerUi = new Ui();
	$headerUi->addConfig('container', '{tag: "nav", class: "nav"}');
	$headerUi->add('<a class="btn -link" href="'.url('bmc/search').'"><i class="icon -material">search</i></a>');
	$headerUi->add('<a class="btn -link" href="'.url('bmc').'"><i class="icon -material">dashboard</i></a>');
	$headerUi->add('<a class="btn -link" href="'.url('bmc/new').'"><i class="icon -material">add_circle</i></a>');
	$ret = '<header class="header"><h3>Business Model Canvas (BMC)</h3>'.(R()->appAgent ? '' : $headerUi->build()).'</header>';


	mydb::where('`uid` = :uid', ':uid', i()->uid);

	$stmt = 'SELECT * FROM %bmc% %WHERE%';

	$dbs = mydb::select($stmt);

	$cardUi = new Ui('div', 'ui-card -sg-flex -justify-left');
	foreach ($dbs->items as $rs) {
		$cardStr = '<div class="header"><h3>'.$rs->title.'</h3></div>'
			. '<div class="detail"><img src="//img.softganz.com/img/bmc-template.png" width="100%" /></div>';
		$cardUi->add(
			$cardStr,
			array(
				'class' => 'sg-action',
				'href' => url('bmc/'.$rs->bmcid),
				'data-webview' => $rs->title,
			)
		);
	}

	$ret .= $cardUi->build();

	//$ret .= print_o($dbs,'$dbs');

	head('<script type="text/javascript">
		function onWebViewComplete() {
			console.log("CALL onWebViewComplete FROM WEBVIEW")
			var options = {title: "BMC", refreshResume: true}
			menu = []
			menu.push({id: "search", label: "ค้นหา", title: "ค้นหา", link: "bmc/search"})
			menu.push({id: "dashboard", label: "รายการ", load: "bmc"})
			menu.push({id: "add", label: "สร้าง BMC", title: "New Title", link: "bmc/new"})
			options.menu = menu
			return options
		}
		function onWebViewResume() {}
		function onWebViewMenuSelect(menuItem = {}) {}
	</script>'
	);

	$ret .= '<style type="text/css">
	.ui-card>.ui-item {margin: 4px; padding-bottom: 24px; flex: 0 0 calc(33.333% - 10px); border-radius: 8px; overflow: hidden;}
	.ui-card>.ui-item>.header {padding: 0; position: absolute; bottom: 0; width: 100%; height: 24px; overflow: hidden;}
	.ui-card>.ui-item>.header>h3 {width: 100%; padding: 4px 0; font-family: Arial; font-size: 1em; text-align: center;}
	</style>';
	return $ret;
}
?>