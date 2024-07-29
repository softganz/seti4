<?php
/**
* Profile :: View User Information
* Created :: 2021-01-01
* Modify  :: 2024-07-23
* Version :: 3
*
* @param Int $userInfo
* @return Widget
*
* @usage profile/{userId}
*/

class ProfileView extends Page {
	var $userId;
	var $right;
	var $userInfo;

	function __construct($userInfo = NULL) {
		parent::__construct([
			'userId' => $userInfo->userId,
			'userInfo' => $userInfo,
			'right' => (Object) [
				'canLogAs' => user_access('access administrator pages'),
			]
		]);
	}

	function rightToBuild() {
		if ($this->userInfo->uid == 1) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		if (empty($this->userId)) return error(_HTTP_ERROR_NOT_FOUND, 'User <em>'.$this->userId.'</em> not exists.');
		if (!user_access('administer users,access user profiles','change own profile', $this->userInfo->uid)) return message('error','Access denied');
		return true;
	}

	function build() {
		event_tricker('profile.view.init',$this,$this->userInfo);

		if ($this->userId == i()->uid) location('my');

		// Increase profile view
		mydb::query('UPDATE %users% SET `views`=`views`+1 WHERE `uid` = :uid LIMIT 1',':uid',$this->userId);
		$this->userInfo->views++;

		event_tricker('profile.view.start',$this,$this->userInfo);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => '@'.i()->name,
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]), // AppBar
			'body' => new Card([
				'id' => 'profile',
				'class' => 'sg-view -co-2',
				'children' => [
					$this->info(),
					$this->status(),
					event_tricker('profile.view.complete', $this, $this->user),
				], // children
			]),
		]); // Scaffold
	}

	function info() {
		return new Container([
			'class' => '-sg-view -sg-paddingnorm',
			'children' => [
				new Container([
					'class' => 'my-profile-wrapper',
					'children' => [
						'<div class="-photo"><img src="'.BasicModel::user_photo($this->userInfo->username).'" width="100%" height="100%" /></a></div>',
						'<div class="-name">'.$this->userInfo->name.'</div>',
						user_access('administer users') ? '<div class="-sg-text-center">ชื่อที่ใช้ในการเข้าระบบ : <strong><a href="'.url('admin/user/edit/'.$this->userInfo->uid).'">'.$this->userInfo->username.'</a></strong></div>':NULL,
					], // children
				]),// Container

				new Column([
					'children' => [
						$this->userInfo->real_name || $this->userInfo->mid_name || $this->userInfo->last_name ? 'ชื่อจริง : '.($this->userInfo->name_prefix?$this->userInfo->name_prefix.' ':'').$this->userInfo->real_name.($this->userInfo->mid_name?' ('.$this->userInfo->mid_name.')':'').' '.$this->userInfo->last_name : NULL,
						$this->userInfo->occupation ? 'อาชีพ : '.$this->userInfo->occupation : NULL,
						$this->userInfo->position ? 'ตำแหน่ง : '.$this->userInfo->position : NULL,
						$this->userInfo->organization ? 'องค์กร / บริษัท : '.$this->userInfo->organization:NULL,
						($this->userInfo->address || $this->userInfo->amphur || $this->userInfo->province) && (user_access('administer users') || i()->uid == $this->userInfo->uid) ? 'ที่อยู่ : '.$this->userInfo->address.' '.$this->userInfo->amphur.' '.$this->userInfo->province.' '.$this->userInfo->zipcode.' '.$this->userInfo->country : NULL,
						// $this->userInfo->latitude ? 'ละติจูด : '.$this->userInfo->latitude : NULL,
						// $this->userInfo->longitude ? 'ลองกิจูด : '.$this->userInfo->longitude : NULL,
						$this->userInfo->phone && (user_access('administer users') || i()->uid == $this->userInfo->uid) ? 'โทรศัพท์ : '.$this->userInfo->phone : NULL,
						$this->userInfo->mobile && (user_access('administer users') || i()->uid==$this->userInfo->uid) ? 'โทรศัพท์เคลื่อนที่ : '.$this->userInfo->mobile : NULL,
						$this->userInfo->fax ? 'แฟกซ์ : '.$this->userInfo->fax :NULL,
						$this->userInfo->website ? 'เว็บไซท์ : '.'<a href="'.$this->userInfo->website.'" target="_blank">'.$this->userInfo->website.'</a>':NULL,
						$this->userInfo->about ? 'ประวัติย่อ'.' : <br />'.nl2br($this->userInfo->about) : NULL,
					], // children
				]), // Column
			], // children
		]);
	}

	function status() {
		return new Container([
			'id' => 'profile-status',
			'class' => '-sg-view -sg-paddingnorm',
			'children' => [
				// Admin menu
				user_access('access administrator pages,administer users') ? new Nav([
					'direction' => 'vertical',
					'children' => [
						$this->right->canLogAs ? new Button([
							'type' => 'link',
							'href' => url('admin/user/logas/name/'.$this->userInfo->username),
							'title' => 'ADMIN can LOG AS',
							'icon' => new Icon('how_to_reg'),
							'text' => '<b>LOG AS '.$this->userInfo->username.'</b>',
						]) : NULL, // Button
						new Button([
							'type' => 'link',
							'class' => 'sg-action',
							'href' => url('api/admin/user/'.$this->userInfo->uid.'/block'),
							'rel' => 'notify',
							'done' => 'load->replace:#profile-status:'.url('profile/'.$this->userInfo->uid.'/view..status'),
							'attribute' => [
								'data-title' => ($this->userInfo->status == 'block' ? 'ACTIVE' : 'BLOCK').' USER!!!',
								'data-confirm' => 'ต้องการ '.($this->userInfo->status == 'block' ? 'Active' : 'Block').' สมาชิก กรุณายืนยัน?'
							],
							'icon' => new Icon($this->userInfo->status == 'enable' ? 'done' : 'block'),
							'text' => 'This user is <b>'.($this->userInfo->status == 'block' ? 'Blocked' : 'Active').'</b>',
						]), // Button
						user_access('access administrator pages') ? new Button([
							'type' => 'link',
							'class' => 'sg-action',
							'href' => url('api/admin/user/'.$this->userInfo->uid.'/blockanddelete'),
							'icon' => new Icon('delete'),
							'text' => '<b>BLOCK AND DELETE TOPICS</b>',
							'rel' => 'notify',
							'done' => 'load->replace:#profile-status:'.url('profile/'.$this->userInfo->uid.'/view..status'),
							'attribute' => [
								'data-title' => 'BLOCK USER & DELETE TOPICS!!!!',
								'data-confirm' => 'ต้องการ Block สมาชิก และ ลบหัวข้อทั้งหมดของสมาชิก กรุณายืนยัน?',
							]
						]) : NULL,
						new Button([
							'type' => 'link',
							'href' => url('paper/user/'.$this->userInfo->uid),
							'target' => '_blank',
							'icon' => new Icon('view_list'),
							'text' => 'หัวข้อที่เขียน'
						]),
						new Button([
							'type' => 'link',
							'class' => 'sg-action',
							'href' => url('profile/'.$this->userInfo->uid.'/view..status'),
							'rel' => 'replace:#profile-status',
							'icon' => new Icon('refresh'),
							'text' => 'Refresh',
						]),
					]
				]) : NULL,

				// General information
				new Column([
					'children' => [
						'เริ่มเป็นสมาชิกตั้งแต่ '.sg_date($this->userInfo->datein,'ว ดด ปป H:i').' น.',
						$this->userInfo->login_time ? 'เข้าระบบล่าสุดเมื่อ '.sg_date($this->userInfo->login_time,'ว ดด ปปปป H:i').' น.' : NULL,
						'เข้าชมเว็บไซท์ : '.number_format($this->userInfo->hits).' ครั้ง',
						'อ่าน : '.number_format($this->userInfo->views).' ครั้ง'
					], // children
				]), // Column
			], // children
		]);
	}
}
?>