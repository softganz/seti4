<?php
/**
* My      :: Clear Cache
* Created :: 2020-08-01
* Modify  :: 2025-06-25
* Version :: 2
*
* @param String $args
* @return Widget
*
* @usage module/{Id}/method
*/

class MyClearCache extends Page {
	var $args;

	function __construct($args = NULL) {
		parent::__construct([
			'args' => $args
		]);
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ล้างแคช',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					'<p style="padding: 32px 8px;">ในกรณีที่ข้อมูลแคชมีการเปลี่ยนแปลงแต่การแสดงผลยังคงใช้แคชเดิม จำทำการล้างแคชเพื่อดึงข้อมูลปัจจุบันมาใช้งาน</p>',
					new Form([
						'action' => url(q()),
						'class' => 'sg-form',
						'rel' => 'silent',
						'silent' => true,
						'done' => 'close',
						'children' => [
							'save' => [
								'type' => 'button',
								'value' => '<span>เรียบร้อย</span>',
								'container' => ['class' => '-sg-text-center'],
							],
						], // children
					]), // Form
				], // children
			]), // Widget
		]);
	}
}
?>