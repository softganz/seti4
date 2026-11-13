<?php
/**
 * Status   :: Widget
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2025-06-16
 * Modified :: 2026-11-13
 * Version  :: 4
 *
 * @param array $args
 *
 * @uses new StatusBarWidgetl([])
 */

class StatusbarWidget extends Widget {
	protected ?string $tagName = 'ul';
	public ?array $childContainer = ['tagName' => 'li', 'class' => '-item'];
	public ?string $class = 'widget-statusbar';

	function __construct(
		$args = [],
		?string $id = null,
		?string $class = null,
		?string $style = null,
		?array $children = []
	) {
		parent::__construct(
			array_replace(
				[
					'id' => $id,
					'class' => $class,
					'style' => $style,
					'children' => $children
				],
				$args
			)
		);
	}
}
?>
