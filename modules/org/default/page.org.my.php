<?php
/**
* Org :: My Organization
* Created 2018-08-22
* Modify  2021-09-27
*
* @param Int $orgId
* @return Widget
*
* @usage org/my[/{id}]
*/

$debug = true;

class OrgMy extends Page {
	var $orgId;

	function __construct($orgId = NULL) {
		$this->orgId = $orgId;
	}

	function build() {
		if (!i()->ok) return R::View('signform');

		if (!$this->orgId) {
			if (!is_admin()) mydb::where('of.`uid` = :uid', ':uid', i()->uid);

			$dbs = mydb::select(
				'SELECT o.`orgId`, o.`name`, of.`uid`, of.`membership`
				FROM %org_officer% of
					RIGHT JOIN %db_org% o USING(`orgid`)
				%WHERE%
				GROUP BY `orgid`
				ORDER BY CONVERT(`name` USING tis620) ASC'
			);

			if ($dbs->_empty) {
				return message('error', 'ขออภัย ท่านยังไม่ได้เป็นเจ้าหน้าที่ขององค์กร');
			} else if ($dbs->_num_rows == 1) {
				// $this->orgId = $dbs->items[0]->orgId;
				location('org/'.$dbs->items[0]->orgId);
			} else {
				// Select orgid
				return new Scaffold([
					'appBar' => new AppBar([
						'title' => 'ระบบบริหารองค์กร',
					]),
					'body' => new Widget([
						'children' => [
							new Container([
								'children' => (function($items) {
									$widgets = [];
									foreach ($items as $item) {
										$widgets[] = new Card([
											'class' => 'sg-action -sg-paddingmore',
											'href' => url('org/'.$item->orgId),
											'children' => [
												new ListTile([
													'title' => $item->name,
													'leading' => '<i class="icon -material">home</i>',
												]),
											], // children
										]);
									}
									return $widgets;
								})($dbs->items),
							]),
						],
					]),
				]);

				// $ui = new Ui(NULL, 'ui-card');
				// foreach ($dbs->items as $rs) {
				// 	$ui->add('<a href="'.url('org/my/'.$rs->orgid).'"><i class="icon -local -home"></i><span>'.$rs->name.'</span></a>');
				// }
				// $ret .= '<nav class="nav -master">'.$ui->build().'</nav>';
				// return $ret;
			}
		}


		// Show Menu
		// $orgInfo = R::Model('org.get', $this->orgId);

		// if (!$orgInfo->orgId) return message('error', 'ขออภัย!!! ไม่มีข้อมูลองค์กรตามที่ระบุ');

		// return new Scaffold([
		// 	'appBar' => new AppBar([
		// 		'title' => $orgInfo->name,
		// 	]),
		// 	'body' => new Container([
		// 		'tagName' => 'nav',
		// 		'class' => 'nav -master',
		// 		'child' => new Ui([
		// 			'type' => 'card',
		// 			'children' => [
		// 				mydb::table_exists('%project%') ? '<a href="'.url('project/org/'.$this->orgId).'"><i class="icon -local -project"></i><span>บริหารโครงการ</span></a>' : NULL,
		// 				'<a href="'.url('org/'.$this->orgId.'/calendar').'"><i class="icon -local -calendar"></i><span>ปฏิทินองค์กร</span></a>',
		// 				'<a class="-disabled" href="'.url('org/'.$this->orgId.'/money').'"><i class="icon -local -money"></i><span>บริหารการเงิน</span></a>',
		// 				'<a href="'.url('org/'.$this->orgId.'/docs.o').'"><i class="icon -local -docs"></i><span>บริหารเอกสาร</span></a>',
		// 				'<a class="-disabled" href="'.url('org/'.$this->orgId.'/room').'"><i class="icon -local -meeting"></i><span>บริหารห้องประชุม</span></a>',
		// 				'<a class="-disabled" href="'.url('org/'.$this->orgId.'/car').'"><i class="icon -local -car"></i><span>บริหารรถ</span></a>',
		// 				'<a href="'.url('org/'.$this->orgId.'/news').'"><i class="icon -local -news"></i><span>ประชาสัมพันธ์</span></a>',
		// 				'<a href="'.url('org/'.$this->orgId.'/meeting').'"><i class="icon -local -action"></i><span>กิจกรรม</span></a>',
		// 				'<a href="'.url('org/'.$this->orgId.'/report').'"><i class="icon -local -report"></i><span>วิเคราะห์</span></a>',
		// 			], // children
		// 		]), // Ui
		// 	]), // Container
		// ]);
	}
}
?>