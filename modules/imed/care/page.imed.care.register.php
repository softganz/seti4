<?php
/**
* iMed Care :: Register
* Created 2021-12-22
* Modify  2021-12-22
*
* @return Widget
*
* @usage imed/care/register
*/

$debug = true;

class ImedCareRegister extends Page {
	function build() {
		$userInfo = new UserModel();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สมัครใช้บริการ',
				'removeOnApp' => true,
			]),
			'body' => new Widget([
				'children' => [
					new Column([
						'class' => 'imed-menu',
						'children' => [
							'<a class="btn -fill" href="'.url('imed/care/regist/taker').'"><i class="icon -imed-care -patient -sg-32"></i><span>สมัครเป็นผู้รับบริการ</span></a>',
							'<a class="btn -fill" href="'.url('imed/care/regist/giver').'"><i class="icon -material -sg-32">baby_changing_station</i><span>สมัครเป็นผู้ให้บริการ</span></a>',
							'<a class="btn -fill -disabled" href="'.url('imed/care/regist/team').'"><i class="icon -material -sg-32">groups</i><span>สมัครเป็นทีมงาน</span></a>',
						], //children
					]),
					'<style type="text/css">
					.imed-menu {width: 300px; margin: 0 auto;}
					.imed-menu>.-item {margin: 16px 0;}
					.imed-menu .btn {padding: 16px 0; box-shadow: none; border-radius: 32px;}
					.imed-menu .btn>.icon {border-radius: 50%;}
					</style>',
				], // children
			]), // Widget
		]);
	}
}
?>