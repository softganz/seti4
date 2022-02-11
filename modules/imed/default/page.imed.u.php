<?php
/**
* iMed :: User Profile
* Created 2019-05-06
* Modify  2021-05-28
*
* @param Int $userId
* @return Widget
*
* @usage imed/u/{id}
*/

$debug = true;

class ImedU {
	var $userId;
	var $refApp;

	function __construct($userId) {
		$this->userId = $userId;
		$this->refApp = post('ref');
	}

	function build() {

		$defaults = '{debug:false, showEdit: true, page: "web"}';
		$options = SG\json_decode($options,$defaults);
		$debug = $options->debug;

		$isAdmin = user_access('administer imeds');

		$myZone = imed_model::get_user_zone(i()->uid,'imed');

		$isProvZone = false;
		foreach ($myZone as $key => $value) if (strlen($key) == 2) $isProvZone = true;
		$isRight = $isAdmin || $isProvZone || (i()->ok && $this->userId == i()->uid);


		$stmt = 'SELECT `uid`, `username`, `name`, `organization`, `mobile`, `datein` FROM %users% WHERE `uid`=:uid LIMIT 1';
		$userInfo = mydb::select($stmt,':uid',$this->userId);


		return new Container([
			'tagName' => 'section',
			'children' => [
				'<header class="header -box -hidden"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>'.$userInfo->name.'</h3></header>',
				'<div class="popup-profile -clearfix -sg-text-center"><img src="'.model::user_photo($userInfo->username).'" width="100" height="100" style="display: block; width: 200px; height: 200px; margin: 0 auto 16px; border-radius: 50%; float: none;" /><span class="name">'.$userInfo->name.'</span>'
				//. '<span class="">ข้อมูลที่เปิดเผยได้</span>'
				//. '<span class="">เพื่อน xx คน</span>'
				. '<span class="">'.$userInfo->organization.'</span>'
				. ($isRight && $userInfo->mobile ? 'โทร '.$userInfo->mobile : '')
				. '</div>',

				'<p>เยี่ยมบ้าน '.mydb::select('SELECT COUNT(*) `serviceCount` FROM %imed_service% WHERE `uid` = :uid LIMIT 1', ':uid', $this->userId)->serviceCount.' ครั้ง</p>',

				$isAdmin ? new Container([
					'children' => [
						'<p>เริ่มเป็นสมาชิกเมื่อ '.sg_date($userInfo->datein, _DATE_FORMAT).'</p>',
						'<h5>พื้นที่รับผิดชอบ</h5>',
						new Ui([
							'class' => 'ui-menu',
							'children' => (function(){
								foreach (imed_model::get_user_zone($this->userId) as $zone) {
									$result[] = '<a class="sg-action" href="'.url('imed/admin/user/'.$this->userId).'" data-rel="box" data-webview="Manage">'.SG\implode_address($zone,'short').' ('.$zone->right.' => '.$zone->module.':'.$zone->refid.')</a>';
								}
								if (empty($result)) $result[] = 'ไม่กำหนดพื้นที่';
								return $result;
							})(),
						]),
					], // children
				]) : NULL,  // Container

				$isRight ? new Container([
					'children' => [
						'<h5>กลุ่ม</h5>',
						new Ui([
							'class' => 'ui-menu',
							'children' => (function(){
								$result = [];
								$stmt = 'SELECT s.*, o.`name` FROM %imed_socialmember% s LEFT JOIN %db_org% o USING(`orgid`) WHERE s.`uid` = :uid';
								$dbs = mydb::select($stmt, ':uid', $this->userId);

								foreach ($dbs->items as $userInfo) {
									if ($this->refApp) {
										$result[] = '<a class="sg-action" href="'.url('imed/'.$this->refApp.'/group/'.$userInfo->orgid).'" data-webview="'.htmlspecialchars($userInfo->name).'">'.$userInfo->name.'</a>';
									} else {
										$result[] = '<a href="'.url('imed/group/'.$userInfo->orgid).'">'.$userInfo->name.'</a>';
									}
								}
								if (empty($result)) $result[] = '<a>ไม่มีกลุ่ม</a>';
								return $result;
							})(),
						]),
					],
				]) : NULL, // Container

				$isAdmin || (i()->ok && $this->userId == i()->uid) ? new Container([
					'children' => [
						'<h5>เยี่ยมบ้าน</h5>',
						new Ui([
							'class' => 'ui-menu',
							'children' => (function(){
								$stmt = 'SELECT s.`pid` `psnid`, CONCAT(p.`prename`, " ", p.`name`, " ", p.`lname`) `fullname`, COUNT(*) `serviceTotals` FROM %imed_service% s LEFT JOIN %db_person% p ON p.`psnid` = s.`pid` WHERE s.`uid` = :uid GROUP BY `psnid`';
								$dbs = mydb::select($stmt, ':uid', $this->userId);
								foreach ($dbs->items as $rs) {
									$patientUrl = $this->refApp ? '<a class="sg-action" href="'.url('imed/'.$this->refApp.'/'.$rs->psnid).'" data-webview="'.$rs->fullname.'">' : '<a class="sg-action" href="'.url('imed/patient/view/'.$rs->psnid).'" data-rel="box" data-width="640">';
									$result[] = $patientUrl.SG\getFirst($rs->fullname,'ไม่ระบุ').' ('.$rs->serviceTotals.' ครั้ง)</a>';
								}
								if (empty($result)) $result[] = 'ไม่เคยเยี่ยมบ้าน';
								return $result;
							})(),
						]),
					], // children
				]) : NULL,  // Container

			], // children
		]) // Container
		;
	}
}
?>