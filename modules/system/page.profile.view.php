<?php
/**
 * Profile :: View User Information
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2021-01-01
 * Modify  :: 2026-06-13
 * Version :: 11
 *
 * @param Int $userInfo
 * @return Widget
 *
 * @usage profile/{userId}
 */

use Softganz\DB;

class ProfileView extends Page {
	var $userId;
	var $right;
	var $userInfo;

	function __construct($userInfo = null) {
		parent::__construct([
			'userId' => $userInfo->userId,
			'userInfo' => $userInfo,
			'right' => (Object) [
				'admin' => is_admin(),
				'adminUser' => user_access('administer users'),
				'canLogAs' => user_access('access administrator pages'),
				'access' => user_access('administer users,access user profiles', 'change own profile', $userInfo->uid)
			]
		]);
	}

	function rightToBuild() {
		if ($this->userInfo->uid == 1) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		if (empty($this->userId)) return error(_HTTP_ERROR_NOT_FOUND, 'User <em>' . $this->userId . '</em> not exists.');
		if (!$this->right->access) return error(_HTTP_ERROR_FORBIDDEN, 'Access denied');
		return true;
	}

	function build() {
		event_tricker('profile.view.init', $this, $this->userInfo);

		// Increase profile view
		DB::query([
			'UPDATE %users% SET `views` = `views` + 1 WHERE `uid` = :uid LIMIT 1',
			'var' => [':uid' => $this->userId]
		]);

		$this->userInfo->views++;

		event_tricker('profile.view.start', $this,$this->userInfo);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => '@' . i()->name,
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

	private function info() {
		return new Container([
			'class' => '-sg-view -sg-paddingnorm',
			'children' => [
				new Container([
					'class' => 'my-profile-wrapper',
					'children' => [
						new Container([
							'class' => '-photo',
							'child' => '<img src="' . BasicModel::user_photo($this->userInfo->username) . '" width="100%" height="100%" />',
						]), // Container
						new Column([
							'class' => '-sg-text-center',
							'children' => [
								'<b>' . $this->userInfo->name . '</b>',
								$this->right->adminUser ? 'Username : <strong><a href="' . Url::link('admin/user/edit/' . $this->userInfo->uid) . '">' . $this->userInfo->username . '</a></strong>' : null,
								$this->right->adminUser ? 'E-mail : ' . $this->userInfo->email : null,
							], // children
						]), // Column
					], // children
				]),// Container

				new Column([
					'children' => [
						$this->userInfo->real_name || $this->userInfo->mid_name || $this->userInfo->last_name ? 'ชื่อจริง : ' . ($this->userInfo->name_prefix ? $this->userInfo->name_prefix . ' ' : '') . $this->userInfo->real_name . ($this->userInfo->mid_name ? ' (' . $this->userInfo->mid_name . ')' : '') . ' ' . $this->userInfo->last_name : null,
						$this->userInfo->occupation ? 'อาชีพ : ' . $this->userInfo->occupation : null,
						$this->userInfo->position ? 'ตำแหน่ง : ' . $this->userInfo->position : null,
						$this->userInfo->organization ? 'องค์กร / บริษัท : ' . $this->userInfo->organization : null,
						($this->userInfo->address || $this->userInfo->amphur || $this->userInfo->province) && ($this->right->adminUser || i()->uid == $this->userInfo->uid) ? 'ที่อยู่ : ' . $this->userInfo->address . ' ' . $this->userInfo->amphur . ' ' . $this->userInfo->province . ' ' . $this->userInfo->zipcode . ' ' . $this->userInfo->country : null,
						$this->userInfo->phone && ($this->right->adminUser || i()->uid == $this->userInfo->uid) ? 'โทรศัพท์ : ' . $this->userInfo->phone : null,
						$this->userInfo->mobile && ($this->right->adminUser || i()->uid == $this->userInfo->uid) ? 'โทรศัพท์เคลื่อนที่ : ' . $this->userInfo->mobile : null,
						// $this->userInfo->fax ? 'แฟกซ์ : ' . $this->userInfo->fax :null,
						$this->userInfo->website ? 'เว็บไซท์ : <a href="' . $this->userInfo->website . '" target="_blank">' . $this->userInfo->website . '</a>' : null,
						$this->userInfo->about ? 'ประวัติย่อ : <br />' . nl2br($this->userInfo->about) : null,
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
				$this->right->adminUser ? new Nav([
					'direction' => 'vertical',
					'children' => [
						$this->right->canLogAs ? new Button([
							'type' => 'link',
							'href' => Url::link('admin/user/logas/name/' . $this->userInfo->username),
							'title' => 'ADMIN can LOG AS',
							'icon' => new Icon('how_to_reg'),
							'text' => '<b>LOG AS ' . $this->userInfo->username . '</b>',
						]) : null, // Button
						new Button([
							'type' => 'link',
							'class' => 'sg-action',
							'href' => Url::link('api/admin/user/' . $this->userInfo->uid . '/block'),
							'rel' => 'notify',
							'done' => 'load->replace:#profile-status:' . Url::link('profile/' . $this->userInfo->uid . '/view..status'),
							'attribute' => [
								'data-title' => strtoupper($this->userInfo->status) . ' USER!!!',
								'data-confirm' => 'ต้องการ ' . ($this->userInfo->status == 'block' ? 'Active' : 'Block') . ' สมาชิก กรุณายืนยัน?'
							],
							'icon' => new Icon($this->userInfo->status == 'enable' ? 'done' : 'block'),
							'text' => 'This user is <b>' . (strtoupper($this->userInfo->status)) . '</b>',
						]), // Button
						$this->right->admin ? new Button([
							'type' => 'link',
							'class' => 'sg-action',
							'href' => Url::link('api/admin/user/' . $this->userInfo->uid . '/blockanddelete'),
							'icon' => new Icon('delete'),
							'text' => '<b>BLOCK AND DELETE</b>',
							'rel' => 'notify',
							'done' => 'load->replace:#profile-status:' . Url::link('profile/' . $this->userInfo->uid . '/view..status'),
							'attribute' => [
								'data-title' => 'BLOCK USER & DELETE TOPICS!!!!',
								'data-confirm' => 'ต้องการ Block สมาชิก และ ลบหัวข้อทั้งหมดของสมาชิก กรุณายืนยัน?',
							]
						]) : null,
						new Button([
							'type' => 'link',
							'class' => 'sg-action',
							'href' => Url::link('paper/user/' . $this->userInfo->uid),
							'icon' => new Icon('view_list'),
							'text' => '<b>' . NodeModel::countNodeByUserId($this->userInfo->uid) . '</b> หัวข้อที่เขียน',
							'rel' => 'box',
							'boxWidth' => '480',
						]),
						new Button([
							'type' => 'link',
							'class' => 'sg-action',
							'href' => Url::link('paper/user/' . $this->userInfo->uid),
							'icon' => new Icon('hub'),
							'text' => '<b>' . NodeModel::countNodeJoinByUserId($this->userInfo->uid) . '</b> หัวข้อเข้าร่วม',
							'rel' => 'box',
							'boxWidth' => '480',
						]), // Button
						new Button([
							'type' => 'link',
							'class' => 'sg-action',
							'href' => Url::link('profile/' . $this->userInfo->uid . '/view..group'),
							'icon' => new Icon('groups'),
							'text' => '<b>' . UserModel::countGroupByUserId($this->userInfo->uid) . '</b> สมาชิกกลุ่ม',
							'rel' => 'box',
							'boxWidth' => '480',
						]), // Button
						new Button([
							'type' => 'link',
							'class' => 'sg-action',
							'href' => Url::link('paper/user/' . $this->userInfo->uid),
							'icon' => new Icon('comment'),
							'text' => '<b>' . NodeModel::countCommentByUserId($this->userInfo->uid) . '</b> ความคิดเห็น',
							'rel' => 'box',
							'boxWidth' => '480',
						]), // Button
						new Button([
							'type' => 'link',
							'class' => 'sg-action',
							'href' => Url::link('profile/' . $this->userInfo->uid . '/view..status'),
							'rel' => 'replace:#profile-status',
							'icon' => new Icon('refresh'),
							'text' => 'Refresh',
						]),
					]
				]) : null,

				$this->userId == i()->uid ? new Column([
					'children' => [
						new Button([
							'type' => 'link',
							'href' => Url::link('my'),
							'text' => 'แก้ไขรายละเอียด',
							'icon' => new Icon('person')
						]),
					]
				]) : null,

				// General information
				new Column([
					'children' => [
						'เริ่มเป็นสมาชิก ' . sg_date($this->userInfo->datein, 'ว ดด ปป H:i') . ' น.',
						$this->userInfo->login_time ? 'เข้าระบบล่าสุด ' . sg_date($this->userInfo->login_time, 'ว ดด ปป H:i') . ' น.' : null,
						'เข้าชมเว็บไซท์ ' . ($this->right->admin ? '<a class="sg-action" href="' . Url::link('stats/list', ['user' => $this->userId]) . '" data-rel="box" data-width="full"><b>' . number_format($this->userInfo->hits) . '</b></a>' : '<b>' . number_format($this->userInfo->hits)) . '</b> ครั้ง',
						'อ่าน <b>' . number_format($this->userInfo->views) . '</b> ครั้ง'
					], // children
				]), // Column
			], // children
		]);
	}

	function group() {
		$userInfo = UserModel::get($this->userId);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'User ' . $userInfo->name . ' in ' . UserModel::countGroupByUserId($this->userId) . ' groups.',
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]), // AppBar
			'body' => new Widget([
				'children' => array_map(
					function($user) {
						return new Card([
							'class' => '-sg-paddingnorm',
							'children' => [
								new ListTile([
									'title' => $user->orgName,
									'leading' => new Icon('groups')
								]),
							]
						]);
					},
					UserModel::getMemberOfGroup($this->userId)
				), // children
			]), // Widget
		]);
	}
}
?>
