<?php
/**
* Project :: Help
* Created 2021-06-05
* Modify  2021-06-05
*
* @return Widget
*
* @usage project/help
*/

$debug = true;

class ProjectHelp extends Page {
	function build() {

		return new Container([
			'children' => [
				'<header class="header">'._HEADER_BACK.'<h3>HELP</h3></header>',
			],
		]);
	}
}
?>