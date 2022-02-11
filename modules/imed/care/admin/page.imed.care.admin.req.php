<?php
/**
* iMed :: Care Admin
* Created 2021-07-30
* Modify  2021-07-30
*
* @return Widget
*
* @usage imed/care/admin/req
*/

$debug = true;

import('package:imed/care/models/model.request');
import('package:imed/care/widgets/widget.request.list');

class iMedCareAdminReq {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'คำขอรับบริการ',
				'leading' => new Nav([
					'children' => [
						'<a href="'.url('imed/care/admin').'"><i class="icon -material">admin_panel_settings</i></a>',
					],
				]),
			]),
			'body' => new Container([
				'children' => [
					new RequestListWidget([
						'title' => 'รอรับบริการ',
						'leading' => '<i class="icon -material">hourglass_empty</i>',
						'children' => RequestModel::items(['waiting' => true]),
					]),
					new RequestListWidget([
						'title' => 'รับบริการเรียบร้อย',
						'leading' => '<i class="icon -material">done_all</i>',
						'children' => RequestModel::items(['closed' => true]),
					]),
				],
			]),
		]); // Scaffold
	}
}
?>