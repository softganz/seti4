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

class ImedCareTeam {
	function __construct() {}

	function build() {
		$isAdmin = is_admin('imed care');

		return new Scaffold([
			'body' => new Container([
				'children' => [
					new HelloWidget(['title' => 'สวัสดี ทีม iMedCare', 'name' => i()->name, 'address' => '']),
					new Row([
						'class' => 'imed-care-menu -block',
						'children' => [
							'<a href="'.url('imed/care/team').'"><i class="icon -imed-care -team"></i><span>รายงานผู้รับบริการ</span></a>',
							'<a href="'.url('imed/care/team').'"><i class="icon -imed-care -team"></i><span>รายงานผู้ให้บริการ</span></a>',
							'<a href="'.url('imed/care/team').'"><i class="icon -imed-care -team"></i><span>รายงานการประเมินผล</span></a>',
							'<a href="'.url('imed/care/team').'"><i class="icon -imed-care -team"></i><span>การรับ-จ่ายค่าบริการ</span></a>',
							$isAdmin ? '<a class="sg-action" href="'.url('imed/care/admin').'" data-webview="iMedCare Admin"><i class="icon -imed-care -service"></i><span>iMedCare Admin</span></a>' : NULL,
						], // children
					]), // Container
					'รายละเอียด',
					'<div style="height: 8px;"></div>',
				], // children
			]),
		]); // Scaffold
	}
}
?>