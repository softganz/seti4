<?php
/**
* Admin   :: Admin Repair
* Created :: 2024-07-10
* Modify  :: 2025-05-22
* Version :: 3
*
* @return Widget
*
* @usage admin/repair
*/

class AdminRepair extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'Admin Repair'
			]), // AdminAppBarWidget
			'body' => new Nav([
				'direction' => 'vertical',
				'children' => [
					new Button([
						'href' => url('admin/repair/like'),
						'text' => 'Repair Like Times',
					]), // Button
					new Button([
						'href' => url('admin/repair/file/rename'),
						'text' => 'Rename Upload File',
					]), // Button
					new Button([
						'href' => url('admin/repair/email'),
						'text' => 'Check Invalid Email',
					]), // Button
				],
			]),
		]);
	}
}
?>