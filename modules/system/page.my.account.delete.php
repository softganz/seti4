<?php
/**
* My      :: Delete My Account
* Created :: 2023-07-17
* Modify  :: 2023-07-17
* Version :: 1
*
* @return Widget
*
* @usage my/account/delete
*/

class MyAccountDelete extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ลบบัญชี',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Form([
						'class' => 'sg-form',
						'action' => url('api/my/account.delete'),
						'rel' => 'notify',
						'done' => 'load:none:'.url('signout').' | close | reload:'.url('/'),
						'children' => [
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
								'value' => '<i class="icon -material">delete_forever</i><span>ยืนยันการลบบัญชี</span>',
								'container' => ['class' => '-sg-text-right'],
							]
						], // children
					])
				], // children
			]), // Widget
		]);
	}
}
?>