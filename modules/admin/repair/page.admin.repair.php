<?php
/**
 * Admin    :: Admin Repair
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2024-07-10
 * Modified :: 2026-08-22
 * Version  :: 4
 *
 * @return Widget
 *
 * @usEs admin/repair
 */

class AdminRepair extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'Admin Repair'
			]), // AdminAppBarWidget
			'sideBar' => new SideBar([
				'type' => 'wide',
				'children' => [
					new Button([
						'type' => 'link',
						'href' => Url::link('admin/site/upgrade/utf8mb4'),
						'text' => 'Convert Table to UTF8MB4',
						'icon' => new Icon('swap_horizontal_circle')
					]), // Button
					new Button([
						'type' => 'link',
						'href' => Url::link('admin/repair/like'),
						'text' => 'Repair Like Times',
						'icon' => new Icon('build_circle')
					]), // Button
					new Button([
						'type' => 'link',
						'href' => Url::link('admin/repair/file/rename'),
						'text' => 'Rename Upload File',
						'icon' => new Icon('build_circle')
					]), // Button
					new Button([
						'type' => 'link',
						'href' => Url::link('admin/repair/email'),
						'text' => 'Check Invalid Email',
						'icon' => new Icon('build_circle')
					]), // Button
				]
			]),
			'body' => new Nav([
				'direction' => 'vertical',
				'children' => [
				],
			]),
		]);
	}
}
?>