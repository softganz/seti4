<?php
/**
 * My       :: Clear Cache
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2020-08-01
 * Modified :: 2026-11-12
 * Version  :: 3
 *
 * @uses my/clear/cache
 */

class MyClearCache extends Page {
	/**
	 * Right to build
	 *
	 * @return object|true
	 */
	function rightToBuild(): object|true {
		if (!i()->ok) {
			return new SignForm();
		}
		return true;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ล้างแคช'
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
								'container' => ['class' => '-sg-text-center']
							]
						] // children
					]) // Form
				] // children
			]) // Widget
		]);
	}
}
?>
