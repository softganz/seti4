<?php
/**
* Forum   :: Help
* Created :: 2023-10-11
* Modify  :: 2023-10-11
* Version :: 1
*
* @return Widget
*
* @usage forum/help
*/

class ForumHelp extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Forum Help',
			]), // AppBar
			'body' => new Widget([
				'children' => [], // children
			]), // Widget
		]);
	}
}
?>