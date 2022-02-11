<?php
/**
* iMed :: Care home page
* Created 2021-05-26
* Modify  2021-08-24
*
* @return Widget
*
* @usage imed/care
*/

$debug = true;

import('package:imed/models/model.imed.user.php');
import('package:imed/care/widgets/widget.hello.php');
import('package:imed/care/models/model.request.php');
import('package:imed/care/widgets/widget.request.list');

class ImedCareHome {
	function __construct() {
		$this->takerInfo = new ImedUserModel(['role' => 'IMED TAKER']);
		$this->giverInfo = new ImedUserModel(['role' => 'IMED GIVER']);
	}

	function build() {
		$isUpgradable = false;
		$lastVersion = '0.1.07';
		$updatePlayStoreUrl = "https://play.google.com/store/apps/details?id=com.softganz.imedcare";

		return new Scaffold([
			'body' => new Column([
				'children' => [
					'<span class="beta" style="position: absolute; right: 4px; z-index: 1; display: block; background-color: red; color: #fff; border-radius: 16px; margin: 4px; padding: 4px; opacity: 0.7; font-size: 0.6em;">BETA Version</span>',
					new HelloWidget(['name' => i()->name, 'address' => '']),

					// Motto
					new Card([
						'class' => '-sg-text-center',
						'style' => 'height: 100px; margin: 0;',
						'children' => [
							new Container([
								'class' => 'home-banner',
								'children' => [
									'<div><b>"บริการด้วยหัวใจ มอบความห่วงใยถึงบ้าน"<br />"We serve excellent care at your home"</b><br />เราคือใคร? เราทำอะไร?<br /></div>',
									'<a class="btn -link" href="'.url('imed/care/our/package').'" data-webview="บริการของเรา"><i class="icon -imed-care -team"></i><span>บริการของเรา</span></a>',
								],
							])
						],
					]),
					// เงื่อนไขว่า ถ้ายังไม่เป็นสมาชิก/หรือเป็นแล้วแต่ไม่ได้เป็นผู้ใช้บริการ ให้แสดงปุ่ม สมัคร ถ้าเป็นสมาชิกให้แสดง ปุ่มเมนู

					// Top menu
					$this->_homeMenu(),

					// date('H:i:s'),
					// new DebugMsg(R()->appAgent,'R()->appAgent'),
					$isUpgradable && R()->appAgent->OS == 'Android' && R()->appAgent->ver < $lastVersion ?
						'<div class="notify" style="padding: 24px; text-align: center;">'
							. '<p>เนื่องจากมีการอัพเดทแอพเป็นรุ่นใหม่ ขอให้ทุกท่านอัพเดทแอพเป็นรุ่นล่าสุดเพื่อให้สามารถใช้งานคุณสมบัติใหม่ๆ ได้</p>'
							. '<a class="sg-action btn -primary" href="'.$updatePlayStoreUrl.'" >ดำเนินการอัพเดทแอพ</a>'
							. '<p>New version is '.$lastVersion.' current verion '.R()->appAgent->ver.'</p>'
							. '</div>'
					: NULL,

					// Show Request Incomplete
					$this->_showWaitingRequest(),

					$this->_showBanner(),

					'<div style="height: 8px;"></div>',

					$this->_script(),
				], // children
			]),
		]); // Scaffold
	}

	function _homeMenu() {
		return new Row([
			'class' => 'imed-care-menu -imed-info',
			'children' => [
					!$this->takerInfo->isEnable() ? '<a class="-register" href="'.url('imed/care/register').'" data-webview="สมัครใช้บริการ"><i class="icon -imed-care -patient"></i><i class="icon -material" style="background-color: #1a81ff; color: #fff; width: 24px; height: 24px; font-size: 20px; line-height: 24px;">how_to_reg</i><span>สมัครใช้บริการ</span></a>' : NULL,
					$this->takerInfo->isEnable() ? '<a href="'.url('imed/care/taker/0/menu').'" data-webview="ขอใช้บริการ"><i class="icon -imed-care -patient"></i><i class="icon -material" style="background-color: red; color: #fff;">add</i><span>ขอใช้บริการ</span></a>' : NULL,
					$this->takerInfo->isEnable() ? '<a href="'.url('imed/care/taker').'" data-webview="บริการของฉัน"><i class="icon -imed-care -patient"></i><i class="icon -material" style="background-color: green; color: #fff;">done</i><span>บริการของฉัน</span></a>' : NULL,
					'<nav>',
					'<a href="'.url('imed/care/our/howto').'" data-webview="วิธีใช้บริการ"><i class="icon -imed-care -service"></i><span>วิธีใช้บริการ</span></a>',
					'<a href="'.url('imed/care/our/about').'" data-webview="เราคือใคร?"><i class="icon -imed-care -team"></i><span>เราคือใคร</span></a>',
					'<a href="'.url('imed/care/our/condition').'" data-webview="เงื่อนไขการใช้งาน"><i class="icon -imed-care -service"></i><span>เงื่อนไขการใช้งาน</span></a>',
				],
		]);
		// แบบที่ 1
		// if ($this->takerInfo->isEnable()) {
		// 	return new Row([
		// 		'class' => 'imed-care-menu -imed-info',
		// 		'children' => [
		// 				'<a href="'.url('imed/care/taker/0/menu').'" data-webview="ผู้รับบริการ"><i class="icon -imed-care -patient"></i><i class="icon -material" style="position: absolute; top: 8px; right: 8px; font-size: 16px; width: 16px; height: 16px; background-color: red; color: #fff;">add</i><span>ขอใช้บริการ</span></a>',
		// 				'<a href="'.url('imed/care/taker').'" data-webview="ผู้รับบริการ"><i class="icon -imed-care -patient"></i><i class="icon -material" style="position: absolute; top: 8px; right: 8px; font-size: 16px; width: 16px; height: 16px; background-color: green; color: #fff;">done</i><span>บริการของฉัน</span></a>',
		// 				'<nav>',
		// 				'<a href="'.url('imed/care/our/howto').'" data-webview="วิธีการใช้บริการ"><i class="icon -imed-care -service"></i><span>วิธีการใช้บริการ</span></a>',
		// 				'<a class="sg-action" href="'.url('imed/care/our/about').'" data-rel="box" data-webview="เราคือใคร?"><i class="icon -imed-care -team"></i><span>เราคือใคร</span></a>',
		// 				'<a class="sg-action" href="'.url('imed/care/our/condition').'" data-webview="เงื่อนไขการใช้"><i class="icon -imed-care -service"></i><span>เงื่อนไขการใช้</span></a>',
		// 			],
		// 	]);
		// } else {
		// 	return new Container([
		// 		'class' => '-sg-paddingmore',
		// 		'children' => [
		// 			'<a class="btn -primary -fill" href="'.url('imed/care/taker').'" data-webview="ผู้รับบริการ"><i class="icon -material">add</i><span>สมัครใช้บริการ</span></a>',
		// 		],
		// 	]);
		// }

		// แบบที่ 2

		// ย้ายเมนูไปรวมกับ เมนูผู้ใช้บริการ
		// new ScrollView([
		// 	'child' => new Row([
		// 		'class' => 'imed-care-menu -imed-info',
		// 		'children' => [
		// 			'<a href="'.url('imed/care/our/howto').'" data-webview="วิธีการใช้บริการ"><i class="icon -imed-care -service"></i><span>วิธีการใช้บริการ</span></a>',
		// 			'<a class="sg-action" href="'.url('imed/care/our/about').'" data-rel="box" data-webview="เราคือใคร?"><i class="icon -imed-care -team"></i><span>เราคือใคร</span></a>',
		// 			'<a class="sg-action" href="'.url('imed/care/our/condition').'" data-webview="เงื่อนไขการใช้"><i class="icon -imed-care -service"></i><span>เงื่อนไขการใช้</span></a>',
		// 		],
		// 	]),
		// ]),

		// '<h3>คุณคือใคร?</h3>',
		// new Row([
		// 	'children' => [
		// 		'รับ/รับบริการ',
		// 		'ให้/ให้บริการ',
		// 		'ทีม/ทีม iMedCare'
		// 	],
		// ]),
		// new Container([
		// 	'class' => 'imed-care-menu',
		// 	'children' => [
		// 		new Ui([
		// 			'children' => [
		// 				'<a href="'.url('imed/care/taker').'"><i class="icon -imed-care -patient"></i><span>ผู้รับบริการ</span></a>',
		// 				'<a href="'.url('imed/care/giver').'"><i class="icon -imed-care -giver"></i><span>ผู้ให้บริการ</span></a>',
		// 				'<a href="'.url('imed/care/service/menu').'"><i class="icon -imed-care -service"></i><span>บริการของเรา</span></a>',
		// 				'<a href="'.url('imed/care/team').'"><i class="icon -imed-care -team"></i><span>ทีม iMedCare</span></a>',
		// 			],
		// 		]),
		// 	], // children
		// ]), // Container
	}

	function _showWaitingRequest() {
		if (is_admin('imed care')) {
				return new RequestListWidget([
					'title' => 'รอรับบริการ',
					'leading' => '<i class="icon -material">hourglass_empty</i>',
					'children' => RequestModel::items(['waiting' => true]),
				]);
		} else {
			if ($this->takerInfo->isEnable()) {
				return new RequestListWidget([
					'takerId' => i()->uid,
					'title' => 'รอรับบริการ',
					'leading' => '<i class="icon -material">hourglass_empty</i>',
					'children' => RequestModel::items(['takerId' => i()->uid, 'waiting' => true]),
				]);
			}

			if ($this->giverInfo->isEnable()) {
				return new RequestListWidget([
					'giverId' => i()->uid,
					'title' => 'รอให้บริการ',
					'leading' => '<i class="icon -material">hourglass_empty</i>',
					'children' => RequestModel::items(['giverId' => i()->uid, 'waiting' => true]),
				]);
			}
		}
	}

	function _showBanner() {
		return new Container([
			'class' => 'imed-care-banner-wrapper',
			'children' => [
				'<img src="//communeinfo.com/upload/photo/imedcare/banner-03.jpg" style="width: 100%; margin: 32px 0;" />',
				new ScrollView([
					'child' => new Row([
						'children' => [
							'<a class="sg-action" href="https://communeinfo.com/upload/photo/imedcare/poster-01.jpg" data-webview="Poster"><img src="//communeinfo.com/upload/photo/imedcare/thumb-poster-01.jpg" /></a>',
							'<a class="sg-action" href="https://communeinfo.com/upload/photo/imedcare/poster-02.jpg" data-webview="Poster"><img src="//communeinfo.com/upload/photo/imedcare/thumb-poster-02.jpg" /></a>',
							'<a class="sg-action" href="https://communeinfo.com/upload/photo/imedcare/poster-03.jpg" data-webview="Poster"><img src="//communeinfo.com/upload/photo/imedcare/thumb-poster-03.jpg" /></a>',
							'<a class="sg-action" href="https://communeinfo.com/upload/photo/imedcare/poster-04.jpg" data-webview="Poster"><img src="//communeinfo.com/upload/photo/imedcare/thumb-poster-04.jpg" /></a>',
							'<a class="sg-action" href="https://communeinfo.com/upload/photo/imedcare/poster-05.jpg" data-webview="Poster"><img src="//communeinfo.com/upload/photo/imedcare/thumb-poster-05.jpg" /></a>',
							'<a class="sg-action" href="https://communeinfo.com/upload/photo/imedcare/poster-06.jpg" data-webview="Poster"><img src="//communeinfo.com/upload/photo/imedcare/thumb-poster-06.jpg" /></a>',
						], // children
					]), // Row
				]), // ScrollView
				'<img src="//communeinfo.com/upload/photo/imedcare/banner-04.jpg" style="width: 100%; margin: 32px 0;" />',
				'<img src="//communeinfo.com/upload/photo/imedcare/poster-01.jpg" style="width: 100%; margin: 32px 0;" />',
			], // children
		]);
	}
	function _script() {
		head('
		<style type="text/css">
		.imed-care-banner-wrapper .widget-row img {height: 200px; width: auto;}
		.imed-care-banner-wrapper .widget-row>.-item {padding: 0 8px;}
		</style>
		<script type="text/javascript">
		function onWebViewComplete() {
			var options = {title: "iMedCare", actionBar: true, clearCache: true, history: false, actionBarColor: "#1A83CD", menu: []}
			options.menu.push({id: "cancel", label: "Home", title: "iMed@home", load: "imed/app/select?app=home", options: {actionBar: true}})
			return options
		}
		</script>');

		return '<style type="text/css">
		</style>';
	}
}
?>