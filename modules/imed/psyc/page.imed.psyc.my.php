<?php
/**
* iMed :: My Menu
* Created 2021-05-26
* Modify  2021-05-31
*
* @return Widget
*
* @usage imed/psyc/my
*/

$debug = true;

class ImedPsycMy {
	function build() {
		$isAdmin = is_admin('imed');
		$isAccessDev = in_array(i()->username, array('softganz','momo'));
		$isLocalHost = _DOMAIN_SHORT == 'localhost';

		$ui = new Ui();

		//if (!i()->ok) return R::View('signform', '{time:-1, showTime: false}');

		$visitCount = mydb::select('SELECT COUNT(*) `total` FROM %imed_service% WHERE `uid` = :uid LIMIT 1', ':uid', i()->uid)->total;
		$careCount = mydb::select('SELECT COUNT(DISTINCT `pid`) `total` FROM %imed_service% WHERE `uid` = :uid LIMIT 1', ':uid', i()->uid)->total;

		$zoneCount = array();
		$zones = imed_model::get_user_zone(i()->uid);
		foreach ($zones as $zone) {
			$zoneCount[$zone->zone] = $zone->zone;
		}

		// if (i()->username == 'softganz') debugMsg(pageInfo());

		$ret = '<div class="imed-patient-photo-wrapper" style="margin:0 0 16px; padding: 16px 0; position:relative; background-color: #fff">';

		$ret .= '<div id="imed-patient-photo" style="width: 196px; height: 196px; margin: 0px auto 8px; display: block; border-radius: 50%; overflow: hidden; border: 2px #eee solid;"><img src="'.model::user_photo(i()->username).'" width="100%" height="100%" /></a></div>';

		$ret .= '<form class="sg-form -upload" method="post" enctype="multipart/form-data" action="'.url('my/api/photo.change').'" data-rel="notify" data-done="load:#main:'.url('imed/my').'" style="position: absolute; top: 16px; right: calc(50% - 112px); padding: 0; margin: 0; background-color: transparent;"><span class="fileinput-button"><i class="icon -material">add_a_photo</i><input type="file" name="photo" class="inline-upload" accept="image/*;capture=camcorder" x-capture="capture" onchange=\'$(this).closest(form).submit(); return false;\' /></span></form>';

		$ret .= '<div class="-sg-text-center"><b>'.i()->name.'</b></div>';
		$ret .= '<div class="-sg-flex" style="padding: 16px 16px 0;font-size: 0.9em;"><a class="btn -link">'.count($zoneCount).'<br />พื้นที่</a> <a class="btn -link">'.$visitCount.'<br />เยี่ยมบ้าน</a> <a class="btn -link">'.$careCount.'<br />ดูแล</a></div>';
		$ret .= '</div>';


		$mainUi = new Ui(NULL, NULL);
		$mainUi->addConfig('nav', '{class: "nav -app-menu"}');

		$mainUi->header('<h3>My Account</h3>');
		$mainUi->add('<a class="sg-action" href="'.url('imed/app/my/profile/info').'" data-rel="box" data-webview="Change Account Profile" data-width="480" data-max-height="80%"><i class="icon -material">account_circle</i><span>รายละเอียด</span></a>');
		if (i()->username != 'demo') {
			$mainUi->add('<a class="sg-action" href="'.url('my/change/password').'" data-rel="box" data-webview="Change Password" data-width="480" data-max-height="80%"><i class="icon -material">visibility</i><span>{tr:Change Password}</span></a>');
		}
		$mainUi->add('<a class="sg-action" href="'.url('signout').'" data-rel="none" data-title="ออกจากระบบสมาชิก" data-confirm="ต้องการออกจากระบบสมาชิก กรุณายืนยัน?" data-done="reload:'.url('imed/my').' | moveto:0,0"><i class="icon -material">lock_open</i><span>{tr:Sign out}</span></a>');


		$ret .= $mainUi->build();


		if (i()->ok) {
			// $mainUi = new Ui(NULL, NULL);
			// $mainUi->addConfig('nav', '{class: "nav -app-menu"}');
			// $mainUi->header('<h3>รายการของฉัน</h3>');
			// $ret .= $mainUi->build();


			// $mainUi = new Ui(NULL, NULL);
			// $mainUi->addConfig('nav', '{class: "nav -app-menu"}');
			// $mainUi->header('<h3>บริการของฉัน</h3>');
			// if ($isLocalHost) {
			// 	$mainUi->add('<a class="sg-action" href="'.url('imed/app/v2').'"><i class="icon -material">home</i><span>หน้าแรก</span></a>');
			// }
			// $mainUi->add('<a class="sg-action" href="'.url('imed/disabled').'" data-webview="คนพิการ"><i class="icon -material">wheelchair_pickup</i><span>คนพิการ</span></a>');
			// $mainUi->add('<a class="sg-action" href="'.url('imed/elderly').'" data-webview="ผู้สูงอายุ"><i class="icon -material">elderly</i><span>ผู้สูงอายุ</span></a>');
			// $mainUi->add('<a class="sg-action" href="'.url('imed/poorman').'" data-webview="คนยากลำบาก"><i class="icon -material">baby_changing_station</i><span>คนยากลำบาก</span></a>');

			// $ret .= $mainUi->build();
		}


		$mainUi = new Ui(NULL, NULL);
		$mainUi->addConfig('nav', '{class: "nav -app-menu"}');
		$mainUi->header('<h3>รายงาน</h3>');

		// $mainUi->add('<a class="sg-action" href="'.url('imed/report/disabledarea').'" data-webview="รายงานคนพิการแยกตามพื้นที่"><i class="icon -material">pie_chart</i><span>คนพิการแยกตามพื้นที่</span></a>');
		// $mainUi->add('<a class="sg-action" href="'.url('imed/report/elderarea').'" data-webview="รายงานผู้สูงอายุแยกตามพื้นที่"><i class="icon -material">pie_chart</i><span>ผู้สูงอายุแยกตามพื้นที่</span></a>');
		// $mainUi->add('<a class="sg-action" href="'.url('imed/report/poormanarea').'" data-webview="รายงานคนยากลำบากแยกตามพื้นที่"><i class="icon -material">pie_chart</i><span>คนยากลำบากแยกตามพื้นที่</span></a>');

		$ret .= $mainUi->build();


		// $mainUi = new Ui(NULL, NULL);
		// $mainUi->addConfig('nav', '{class: "nav -app-menu"}');
		// $mainUi->header('<h3>รายงานคนพิการ</h3>');

		// $mainUi->add('<a class="sg-action" href="'.url('imed/report/disabledarea').'" data-webview="รายงานคนพิการแยกตามพื้นที่"><i class="icon -material">pie_chart</i><span>คนพิการแยกตามพื้นที่</span></a>');
		// $mainUi->add('<a class="sg-action" href="'.url('imed/report/homevisit').'" data-webview="รายงานการเยี่ยมบ้าน"><i class="icon -material">pie_chart</i><span>จำนวนการเยี่ยมบ้าน</span></a>');
		// $mainUi->add('<a class="sg-action" href="'.url('imed/report/newdisability').'"data-webview="รายชื่อคนพิการรายใหม่"><i class="icon -material">view_list</i><span>รายชื่อคนพิการรายใหม่</span></a>');
		// $mainUi->add('<a class="sg-action" href="'.url('imed/report/prosthetic').'"data-webview="รายงานการได้รับกายอุปกรณ์"><i class="icon -material">pie_chart</i><span>การได้รับกายอุปกรณ์</span></a>');
		// $mainUi->add('<a class="sg-action" href="'.url('imed/report/regexpire').'"data-webview="รายงานวันที่บัตรหมดอายุ"><i class="icon -material">pie_chart</i><span>รายงานวันที่บัตรหมดอายุ</span></a>');

		// $ret .= $mainUi->build();

		$otherUi = new Ui();
		$otherUi->addConfig('nav', '{class: "nav -app-menu"}');
		$otherUi->header('<h3>อื่นๆ</h3>');

		$otherUi->add('<a class="sg-action" href="'.url('my/clear/cache').'" data-webview="ล้างแคช" data-options=\'{clearCache: true}\' data-rel="box" data-width="480"><i class="icon -material">clear</i><span>ล้างแคช</span></a>');

		$ret .= $otherUi->build();

		if ($isAdmin) {

			$adminUi = new Ui(NULL, NULL);
			$adminUi->addConfig('nav', '{class: "nav -app-menu"}');
			$adminUi->header('<h3>ผู้จัดการระบบ</h3>');

			$adminUi->add('<a class="sg-action" href="'.url('calendar').'" data-webview="ปฎิทินกิจกรรม"><i class="icon -material">date_range</i><span>ปฎิทิน</span></a>');

			$adminUi->add('<a class="sg-action" href="https://communeinfo.com" data-webview="ข้อมูลชุมชน.com" data-options=\'{history: true}\'><i class="icon"><img src="//communeinfo.com/themes/default/logo-homemed.png" width="24" height="24" /></i><span>CommuneInfo.com</span></a>');

			$adminUi->add('<a class="sg-action" href="'.url('imed/admin/member').'" data-webview="จัดการสิทธิ์"><i class="icon -material">add_task</i><span>จัดการสิทธิ์</span></a>');

			if (i()->username == 'softganz') {
				if (_DOMAIN_SHORT == 'localhost') {
					if (R()->appAgent) {
						$adminUi->add('<a href="'.url('imed/app/v2',array('setting:app' => '')).'"><i class="icon -material">web</i><span>www</span></a>');
					} else {
						$adminUi->add('<a href="'.url('imed/app/v2',array('setting:app' => '{OS:%22Android%22,ver:%220.20.00%22,type:%22App%22,dev:%22Softganz%22}')).'"><i class="icon -material">android</i><span>App</span></a>');
					}
				}

				//$adminUi->add('<a href="'.url('imed/app').'"><i class="icon"><img src="//communeinfo.com/themes/default/logo-homemed.png" width="24" height="24" /></i><span>iMed@Home</span></a>');


				if (R()->appAgent->OS == 'Android') {
					$host = preg_match('/^([a-z]+)/', _DOMAIN_SHORT, $out) ? $out[1] : _DOMAIN_SHORT;
					$isProduction = $host == 'communeinfo';
					$adminUi->add('<a class="sg-action" data-rel="none" data-webview="server" data-server="'.($isProduction ? 'DEV' : 'PRODUCTION').'" data-done="load:#main"><i class="icon -material">android</i><span>'.strtoupper($host).'</span></a>');
				}
			}

			$ret .= $adminUi->build();


			$stmt = 'SELECT `appsrc`,`appagent`,COUNT(*) `total`
				FROM %imed_service%
				WHERE `created` >= :created
				GROUP BY `appsrc`,`appagent`
				ORDER BY `appsrc`, `appagent`;
				-- {sum: "total"}';
			$appServiceCount = mydb::select($stmt, ':created', date('U') - 31*24*60*60);

			$tables = new Table();
			$tables->addClass('-imed-service-source');
			$tables->thead = array('App Agent', 'total -amt'=>'Amount', 'percent -amt' => '%');
			foreach ($appServiceCount->items as $rs) {
				$tables->rows[] = array($rs->appagent, number_format($rs->total), number_format(($rs->total/$appServiceCount->sum->total)*100,2).'%');
			}
			$tables->tfoot[] = array('รวม', number_format($appServiceCount->sum->total),'');
			$ret .= '<nav class="nav -app-menu"><header class="header"><h3>Service from source</h3><nav><ul><li><a class="btn -link">Last 31 Days</a></li></ul></nav></header>'.$tables->build().'</nav>';

		}

		$ret .= '<script type="text/javascript">
			if (typeof Android == "object") {
			Android.setTitle("MY ACCOUNT")
		}
		</script>';
		$ret .= '<style type="text/css">
		.item.-imed-service-source {font-size: 0.8em;}
		</style>';
		return new Scaffold([
			'child' => new Container([
				'children' => [
					$ret,
				],
			]),
		]);
	}
}
?>