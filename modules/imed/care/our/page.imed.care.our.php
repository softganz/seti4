<?php
/**
* iMed :: Care home page
* Created 2021-05-26
* Modify  2021-05-31
*
* @return Widget
*
* @usage imed/care/service/our
*/

$debug = true;

import('package:imed/care/widgets/widget.hello.php');

class ImedCareOur {
	function __construct() {}

	function build() {
		$isAdmin = is_admin('imed care');

		return new Scaffold([
			'body' => new Container([
				'children' => [
					new HelloWidget(['name' => i()->name, 'address' => '']),
					new Container([
						'class' => 'imed-care-menu',
						'children' => [
							'<h3>iMedCare</h3>',
							new Ui([
								'children' => [
									'<a href="'.url('imed/care/our/about').'"><i class="icon -imed-care -team"></i><span>เราคือใคร?</span></a>',
									'<a href="'.url('imed/care/our/team').'"><i class="icon -imed-care -team"></i><span>ทีมงาน</span></a>',
									'<a href="'.url('imed/care/our/condition').'"><i class="icon -imed-care -service"></i><span>เงื่อนไขการใช้</span></a>',
									'<a href="'.url('imed/care/our/howto').'"><i class="icon -imed-care -service"></i><span>วิธีการใช้บริการ</span></a>',
									'<a href="'.url('imed/care/our/package').'"><i class="icon -imed-care -service"></i><span>แพ็คเกจให้บริการ</span></a>',
									'<a href="'.url('imed/care/our/menu').'"><i class="icon -imed-care -service"></i><span>เมนูให้บริการ</span></a>',
								],
							]),
						], // children
					]), // Container
					'<div style="height: 8px;"></div>',
				], // children
			]),
		]); // Scaffold
	}
}
?>