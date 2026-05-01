<?php
/**
 * User    :: Waiting Page
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2026-05-01
 * Modify  :: 2026-05-01
 * Version :: 1
 *
 * @return Widget
 *
 * @usage user/waiting
 */

class UserWaiting extends Page {
	var $args;

	#[\Override]
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'User waiting for approval',
				'leading' => new Icon('schedule')
			]), // AppBar
			'body' => new Notify([
				'class' => '-center',
				'style' => 'padding: 6.4rem; width: 70%; max-width: 640px; margin: 3.2rem auto; border-radius: 2.4rem;',
				'child' => new Column([
					'children' => [
						new Icon('warning', ['class' => '-sg-64 -red']),
						'การสมัครสมาชิกยังสำเร็จสมบูรณ์',
						'กรุณาแจ้งผู้ดูแลระบบเพื่ออนุมัติการสมัครสมาชิก'
					]
				]), // Column
			]), // Widget
		]);
	}
}
?>