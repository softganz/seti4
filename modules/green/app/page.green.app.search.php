<?php
/**
* Green : Search
* Created 2020-11-16
* Modify  2020-11-16
*
* @param Object $self
* @return String
*
* @usage green/app/search
*/

$debug = true;

function green_app_search($self) {
	$ret = '<header class="header"><h3>ค้นหา</h3></header>';


	head('<script type="text/javascript">
	function onWebViewComplete() {
		console.log("CALL onWebViewComplete FROM WEBVIEW")
		var options = {}
		menu = []
		//menu.push({id: "search", label: "ค้นหา", title: "ค้นหา", link: "green/app/search", options: {actionBar: false}})
		options.menu = menu
		return options
	}
	</script>');

	return $ret;
}
?>