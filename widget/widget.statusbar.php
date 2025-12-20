<?php
/**
* Status  :: Widget
* Created :: 2025-06-16
* Modify  :: 2025-06-16
* Version :: 1
*
* @param Array $args
* @return Object
*
* @usage import('widget:module.widgetlname.php')
* @usage new StatusBarWidgetl([])
*/

class StatusbarWidget extends Widget {
	var $childTagName = 'li';

	function __construct($args = []) {
		parent::__construct($args);
	}

	// function build() {
	// 	return new Widget([
	// 		'children' => $this->children
	// 	]);
	// }
}
?>