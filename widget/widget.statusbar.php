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
	var $tagName = 'ul';
	var $childContainer = ['tagName' => 'li', 'class' => '-item'];
	var $class = 'widget-statusbar';

	// function __construct($args = []) {
	// 	parent::__construct($args);
	// }
}
?>