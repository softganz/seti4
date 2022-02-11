<?php
/**
* Project Nxt :: University List
* Created 2021-11-01
* Modify  2021-11-01
*
* @return Widget
*
* @usage project/nxt/university
*/

$debug = true;

import('model:org.php');

class ProjectNxtUniversity extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'มหาวิทยาลัย',
				'navigator' => [
					new Form([
						'class' => 'sg-form form-report',
						'action' => url('project/nxt/university'),
						'rel' => '#main',
						'children' => [
							'q' => [
								'type' => 'text',
								'value' => post('q'),
								'placeholder' => 'ค้นชื่อ',
							],
							'go' => [
								'type' => 'button',
								'value' => '<i class="icon -material">search</i>',
							]
						], // children
					]), // Form
				],
			]),
			'body' => new Widget([
				'children' => [
					new Table([
						'thead' => ['มหาวิทยาลัย', 'ที่อยู่'],
						'children' => array_map(
							function($item) {
								return [
									'<a href="'.url('org/'.$item->orgId).'">'.$item->name.'</a>',
									$item->house,
								];
							},
							OrgModel::items(
								['sector' => 10, 'q' => post('q')],
								['debug' => false]
							)
						), // children
					]), // Table
				], // children
			]), // Widget
		]);
	}
}
?>