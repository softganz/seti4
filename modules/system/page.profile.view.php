<?php
/**
* Profile :: View User Information
* Created :: 2021-01-01
* Modify  :: 2023-10-06
* Version :: 2
*
* @param Int $userId
* @return Widget
*
* @usage profile/{userId}
*/

class ProfileView extends Page {
	var $userId;
	var $userInfo;

	function __construct($userId = NULL) {
		parent::__construct([
			'userId' => $userId = intval(SG\getFirst($userId, post('uid'))),
			'userInfo' => $userInfo = $userId ? R::Model('user.get',$userId) : NULL,
			'userId' => $userInfo->userId,
		]);
	}

	function build() {
		event_tricker('profile.view.init',$this,$this->userInfo);

		if (empty($this->userId)) return error(_HTTP_ERROR_NOT_FOUND, 'User <em>'.$this->userId.'</em> not exists.');

		if (!user_access('administer users,access user profiles','change own profile',$this->userInfo->uid)) return message('error','Access denied');

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
				'children' => [
					'<div class="sg-view -co-2">'
					. '<div class="-sg-view -sg-paddingnorm">'
					. '<div class="my-profile-wrapper">'
					. '<div class="-photo"><img src="'.BasicModel::user_photo($this->userInfo->username).'" width="100%" height="100%" /></a></div>'
					. '<div class="-name">'.$this->userInfo->name.'</div>'
					. (user_access('administer users') ? '<div class="-sg-text-center">ชื่อที่ใช้ในการเข้าระบบ : <strong><a href="'.url('admin/user/edit/'.$this->userInfo->uid).'">'.$this->userInfo->username.'</a></strong></div>':'')
					. '</div>'
					// . $this->_status()
					. $this->_info()
					. '</div>',

					'<div class="-sg-view -sg-paddingnorm">'.$this->_status().'</div>',

					event_tricker('profile.view.complete', $this, $this->user),
					'<script type="text/javascript">
						$(document).ready(function() {
							$("#profile-menu").html("<ul class=\"ui-menu\">"+$(".nav.-main .menu.-member").html()+"</ul>");
						});
					</script>'
				], // children
			]),
		]); // Scaffold
	}

	function _status() {
		$canLogAs = user_access('access administrator pages');

		// Set admin menu
		if ($this->userInfo->uid != 1 && user_access('access administrator pages,administer users')) {
			$ret .= '<p>'
				. ($canLogAs ? '<a class="btn -link -fill" href="'.url('admin/user/logas/name/'.$this->userInfo->username).'" title="ADMIN can LOG AS"><i class="icon -material">how_to_reg</i><span><b>LOG AS '.$this->userInfo->username.'</b></span></a>' : '')
				. '<a class="sg-action btn -link -fill" href="'.url('api/admin/user/'.$this->userInfo->uid.'/block').'"  data-rel="notify" data-done="load->clear:box:'.url('profile/'.$this->userInfo->uid).'" data-title="'.($this->userInfo->status == 'block' ? 'ACTIVE':'BLOCK').' USER!!!" data-confirm="ต้องการ '.($this->userInfo->status == 'block' ? 'Active':'Block').' สมาชิก กรุณายืนยัน?"><i class="icon -material">'.($this->userInfo->status == 'enable' ? 'done' : 'block').'</i><span>This user is <b>'.($this->userInfo->status=='block'?'Blocked':'Active').'</b></span></a>'
				. (user_access('access administrator pages') ? '<a class="sg-action btn -link" href="'.url('api/admin/user/'.$this->userInfo->uid.'/blockanddelete').'" class="sg-action" data-rel="notify" data-done="load->clear:box:'.url('profile/'.$this->userInfo->uid).'"  data-title="BLOCK USER & DELETE TOPICS!!!!" data-confirm="ต้องการ Block สมาชิก และ ลบหัวข้อทั้งหมดของสมาชิก กรุณายืนยัน?"><i class="icon -material">delete</i><span><b>BLOCK AND DELETE TOPICS</b></span></a>' : '')
				. '<a class="btn -link -fill" href="'.url('paper/user/'.$this->userInfo->uid).'" target="_blank"><i class="icon -material">view_list</i><span>หัวข้อที่เขียน</span></a>'
				. '<a class="sg-action btn -link -fill" href="'.url('profile/'.$this->userInfo->uid).'" data-rel="replace:#profile"><i class="icon -material">refresh</i><span>Refresh</span></a>'
				. '</p>';
		}

		$ret .= '<p>'.'เริ่มเป็นสมาชิกตั้งแต่ '.sg_date($this->userInfo->datein,'ว ดด ปป H:i').' น.'.'<br />';
			if ($this->userInfo->login_time) $ret .= 'เข้าระบบล่าสุดเมื่อ '.sg_date($this->userInfo->login_time,'ว ดด ปปปป H:i').' น.'.'<br />';
			$ret .= 'เข้าชมเว็บไซท์ : '.number_format($this->userInfo->hits).' ครั้ง'.'<br />
		'.'อ่าน : '.number_format($this->userInfo->views).' ครั้ง'.'</p>';
		return $ret;
	}

	function _info() {
		$ret .= ($this->userInfo->real_name || $this->userInfo->mid_name || $this->userInfo->last_name ? 'ชื่อจริง : '.($this->userInfo->name_prefix?$this->userInfo->name_prefix.' ':'').$this->userInfo->real_name.($this->userInfo->mid_name?' ('.$this->userInfo->mid_name.')':'').' '.$this->userInfo->last_name.'<br /><br />' : '').'
		'.($this->userInfo->occupation ? 'อาชีพ : '.$this->userInfo->occupation.'<br /><br />':'').'
		'.($this->userInfo->position? 'ตำแหน่ง : '.$this->userInfo->position.'<br /><br />':'').'
		'.($this->userInfo->organization ? 'องค์กร / บริษัท : '.$this->userInfo->organization.'<br /><br />':'').'
		'.($this->userInfo->address || $this->userInfo->amphur || $this->userInfo->province ? 'ที่อยู่ : '.$this->userInfo->address.' '.$this->userInfo->amphur.' '.$this->userInfo->province.' '.$this->userInfo->zipcode.' '.$this->userInfo->country.'<br /><br />':'').'
		'.($this->userInfo->latitude ? 'ละติจูด : '.$this->userInfo->latitude.'<br /><br />':'').'
		'.($this->userInfo->longitude ? 'ลองกิจูด : '.$this->userInfo->longitude.'<br /><br />':'').'
		'.($this->userInfo->phone && (user_access('administer users') || i()->uid==$this->userInfo->uid) ? 'โทรศัพท์ : '.$this->userInfo->phone.'<br /><br />':'').'
		'.($this->userInfo->mobile && (user_access('administer users') || i()->uid==$this->userInfo->uid) ? 'โทรศัพท์เคลื่อนที่ : '.$this->userInfo->mobile.'<br /><br />':'').'
		'.($this->userInfo->fax ? 'แฟกซ์ : '.$this->userInfo->fax.'<br /><br />':'').'
		'.($this->userInfo->website ? 'เว็บไซท์ : '.'<a href="'.$this->userInfo->website.'" target="_blank">'.$this->userInfo->website.'</a><br /><br />':'').'
		'.'คำหลัก'.' : <br /><br />
		'.($this->userInfo->about?'ประวัติย่อ'.' : <br /><br />'.sg_text2html($this->userInfo->about):'').'<br /><br />
		';
		return $ret;
	}
}
?>