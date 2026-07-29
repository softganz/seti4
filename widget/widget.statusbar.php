<?php
/**
 * Status   :: Widget
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2025-06-16
 * Modified :: 2026-07-29
 * Version  :: 2
 *
 * @param Array $args
 *
 * @uses new StatusBarWidgetl([])
 */

class StatusbarWidget extends Widget {
	var $tagName = 'ul';
	var $childContainer = ['tagName' => 'li', 'class' => '-item'];
	var $class = 'widget-statusbar';
}
?>