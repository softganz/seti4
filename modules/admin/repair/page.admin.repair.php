<?php
/**
* Admin   :: Admin Repair
* Created :: 2024-07-10
* Modify  :: 2024-08-19
* Version :: 2
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
					'<a href="'.url('admin/repair/like').'">Repair  Like Times</a>',
					'<a href="'.url('admin/repair/file/rename').'">Rename Upload File</a>',
				],
			]),
		]);
	}
}
?>