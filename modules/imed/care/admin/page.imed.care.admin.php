<?php
/**
* iMed :: Care Admin
* Created 2021-07-30
* Modify  2021-07-30
*
* @return Widget
*
* @usage imed/care/admin
*/

$debug = true;

import('package:imed/care/widgets/widget.hello.php');

class iMedCareAdmin {
	function build() {
		return new Scaffold([
			'body' => new Container([
				'children' => [
					new HelloWidget(['name' => i()->name, 'address' => '']),
					new Row([
						'class' => 'imed-care-menu',
						'children' => [
							'<a href="'.url('imed/care/admin/taker').'" data-webview="ผู้รับบริการ"><i class="icon -imed-care -patient"></i><span>ผู้รับบริการ</span></a>',
							'<a href="'.url('imed/care/admin/giver').'" data-webview="ผู้ให้บริการ"><i class="icon -imed-care -giver"></i><span>ผู้ให้บริการ</span></a>',
							'<a href="'.url('imed/care/admin/req').'" data-webview="คำขอรับบริการ"><i class="icon -imed-care -service"></i><span>คำขอรับบริการ</span></a>',
						],
					]), // Row
					'<div style="height: 8px;"></div>',
				], // children
			]),
		]); // Scaffold
	}
}
?>