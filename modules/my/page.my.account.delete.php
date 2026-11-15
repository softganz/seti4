<?php
/**
 * My       :: Delete My Account
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2023-07-17
 * Modified :: 2026-11-15
 * Version  :: 2
 *
 * @uses my/account/delete
 */

declare(strict_types=1);

namespace My;

class MyAccountDelete extends \Page {
	/**
	 * Build page
	 *
	 * @return object
	 */
	#[\Override]
	function build(): object {
		return new \Scaffold(
			appBar: new \AppBar(
				title: 'ลบบัญชี',
				leading: new \Icon('person_cancel')
			), // AppBar
			body: new \Widget(
				children: [
					new \Header(
						title: 'Delete account',
						leading: new \Icon('person_cancel')
					),
					new \Form(
						class: 'sg-form',
						action: \Url::link('api/my/account.delete'),
						rel: 'notify',
						done: 'load:none:'
							. \Url::link('signout')
							. ' | close | reload:'
							. \Url::link('/'),
						children: [
							'คำเตือน จะดำเนินการลบบัญชีและข้อมูลอื่นๆ ทั้งหมด โดยจะไม่สามารถเรียกคืนได้อีก',
							'confirm' => [
								'type' => 'checkbox',
								'label' => 'ยืนยัน:',
								'require' => true,
								'options' => ['yes' => 'ยืนยันการลบบัญชี']
							],
							'submit' => [
								'type' => 'button',
								'class' => '-danger',
								'value' =>
									'<i class="icon -material">delete_forever</i><span>ยืนยันการลบบัญชี</span>',
								'container' => ['class' => '-sg-text-right']
							]
						] // children
					)
				] // children
			) // Widget
		);
	}
}
?>
