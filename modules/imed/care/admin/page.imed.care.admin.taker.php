<?php
/**
* iMed :: Care Admin
* Created 2021-07-30
* Modify  2021-07-30
*
* @return Widget
*
* @usage imed/care/admin/taker
*/

$debug = true;

import('package:imed/models/model.imed.user.php');

class iMedCareAdminTaker {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'iMedCare Admin :: ผู้รับบริการ',
				'leading' => new Nav([
					'children' => [
						'<a href="'.url('imed/care/admin').'"><i class="icon -material">admin_panel_settings</i></a>',
					],
				]),
			]),
			'body' => new Container([
				'children' => (function() {
					$widgetList = [];
					foreach (ImedUserModel::takers() as $rs) {
						$widgetList[] = new Card([
							'class' => 'sg-action',
							'href' => url('imed/care/admin/taker/'.$rs->uid),
							'children' => [
								new ListTile([
									'leading' => '<img class="profile-photo" src="'.model::user_photo($rs->username).'" width="64" />',
									'title' => $rs->name,
									'subtitle' => '@'.sg_date($rs->created, 'ว ดด ปปปป H:i'),
									'trailing' => '<i class="icon -material">navigate_next</i>',
								]),
								'<div>'.$rs->seq.'</div>',
							],
						]);
					}
					return $widgetList;
				})(), // children
			]),
		]); // Scaffold
	}
}
?>