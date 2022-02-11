<?php
/**
* iMed :: Care Admin
* Created 2021-07-30
* Modify  2021-07-30
*
* @return Widget
*
* @usage imed/care/admin/giver
*/

$debug = true;

import('package:imed/models/model.imed.user.php');

class iMedCareAdminGiver {
	function build() {
		$givers = ImedUserModel::givers();
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'iMedCare Admin :: '.count($givers).' ผู้ให้บริการ',
				'leading' => new Nav([
					'children' => [
						'<a href="'.url('imed/care/admin').'"><i class="icon -material">admin_panel_settings</i></a>',
					],
				]),
			]),
			'body' => new Container([
				'children' => (function() use($givers) {
					$widgetList = [];
					foreach ($givers as $rs) {
						$enable = $rs->status == 'ENABLE';
						$widgetList[] = new Card([
							'class' => 'sg-action',
							'href' => url('imed/care/giver/'.$rs->uid.'/profile'),
							'attribute' => ['data-webview' => $rs->name],
							'children' => [
								new ListTile([
									'leading' => '<img class="profile-photo" src="'.model::user_photo($rs->username).'" width="64" />',
									'title' => $rs->name,
									'subtitle' => '@'.sg_date($rs->created, 'ว ดด ปปปป H:i'),
									'trailing' => new Row([
										'children' => [
											'<a class="sg-action" href="'.url('imed/care/api/admin/'.$rs->uid.'/giver.'.($enable ? 'disable' : 'enable')).'" data-rel="notify" data-title="'.($enable ? 'DISABLE GIVER!!!' : 'ENABLE GIVER!!!').'" data-done="load" data-confirm="ต้องการ <b>'.($enable ? 'ระงับ' : 'อนุมัติ').'</b> การเป็นผู้ให้บริการ กรุณายืนยัน?"><i class="icon -material '.($enable ? '-green' : '-gray').'">verified</i></a>',
											'<a href="'.url('imed/care/giver/'.$rs->uid.'/profile').'"><i class="icon -material">navigate_next</i></a>',
										], // children
									]), // Row
								]), // ListTile
								// new DebugMsg($rs,'$rs'),
								'<div>'.$rs->seq.'</div>',
							], // children
						]);
					}
					return $widgetList;
				})(), // children
			]), // Container
		]); // Scaffold
	}
}
?>