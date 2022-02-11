<?php
/**
* Profile :: View User Information
* Created 2021-01-01
* Modify  2021-06-07
*
* @param Int $userId
* @return Widget
*
* @usage profile/{id}
*/

$debug = true;

class ProfileView extends Page {
	var $userId;
	var $userInfo;

	function __construct($userId) {
		$this->userId = SG\getFirst($userId, post('uid'));
		$this->userInfo = R::Model('user.get',$this->userId);
	}

	function build() {
		$userId = $this->userId;
		$userInfo = $this->userInfo;
		event_tricker('profile.view.init',$this,$this->userInfo);

		if ($userInfo->_empty) return message('error','User <em>'.$userId.'</em> not exists.');

		if (!user_access('administer users,access user profiles','change own profile',$userInfo->uid)) return message('error','Access denied');

		if ($userId == i()->uid) location('my');

		// Increase profile view
		mydb::query('UPDATE %users% SET `views`=`views`+1 WHERE `uid` = :uid LIMIT 1',':uid',$userInfo->uid);
		$userInfo->views++;

		event_tricker('profile.view.start',$this,$userInfo);

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
					. '<div class="-photo"><img src="'.model::user_photo($userInfo->username).'" width="100%" height="100%" /></a></div>'
					. '<div class="-name">'.$userInfo->name.'</div>'
					. (user_access('administer users') ? '<div class="-sg-text-center">ชื่อที่ใช้ในการเข้าระบบ : <strong><a href="'.url('admin/user/edit/'.$userInfo->uid).'">'.$userInfo->username.'</a></strong></div>':'')
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
		$userInfo = $this->userInfo;
		$canLogAs = user_access('access administrator pages');

		// Set admin menu
		if ($userInfo->uid != 1 && user_access('access administrator pages,administer users')) {
			$ret .= '<p>'
				. ($canLogAs ? '<a class="btn -link -fill" href="'.url('admin/user/logas/name/'.$userInfo->username).'" title="ADMIN can LOG AS"><i class="icon -material">how_to_reg</i><span><b>LOG AS '.$userInfo->username.'</b></span></a>' : '')
				. '<a class="sg-action btn -link -fill" href="'.url('admin/user/block/'.$userInfo->uid).'"  data-rel="notify" data-done="load->clear:box:'.url('profile/'.$userInfo->uid).'" data-title="'.($userInfo->status == 'block' ? 'ACTIVE':'BLOCK').' USER!!!" data-confirm="ต้องการ '.($userInfo->status == 'block' ? 'Active':'Block').' สมาชิก กรุณายืนยัน?"><i class="icon -material">'.($userInfo->status == 'enable' ? 'done' : 'block').'</i><span>This user is <b>'.($userInfo->status=='block'?'Blocked':'Active').'</b></span></a>'
				. (user_access('access administrator pages') ? '<a class="btn -link" href="'.url('admin/user/blockanddelete/'.$userInfo->uid).'" class="sg-action" data-rel="#notify" data-done="load->clear:box:'.url('profile/'.$userInfo->uid).'"  data-title="BLOCK USER & DELETE TOPICS!!!!" data-confirm="ต้องการ Block สมาชิก และ ลบหัวข้อทั้งหมดของสมาชิก กรุณายืนยัน?"><i class="icon -material">delete</i><span><b>BLOCK AND DELETE TOPICS</b></span></a>' : '')
				. '<a class="btn -link -fill" href="'.url('paper/user/'.$userInfo->uid).'" target="_blank"><i class="icon -material">view_list</i><span>หัวข้อที่เขียน</span></a>'
				. '<a class="sg-action btn -link -fill" href="'.url('profile/'.$userInfo->uid).'" data-rel="replace:#profile"><i class="icon -material">refresh</i><span>Refresh</span></a>'
				. '</p>';
		}

		$ret .= '<p>'.'เริ่มเป็นสมาชิกตั้งแต่ '.sg_date($userInfo->datein,'ว ดด ปป H:i').' น.'.'<br />';
			if ($userInfo->login_time) $ret .= 'เข้าระบบล่าสุดเมื่อ '.sg_date($userInfo->login_time,'ว ดด ปปปป H:i').' น.'.'<br />';
			$ret .= 'เข้าชมเว็บไซท์ : '.number_format($userInfo->hits).' ครั้ง'.'<br />
		'.'อ่าน : '.number_format($userInfo->views).' ครั้ง'.'</p>';
		return $ret;
	}

	function _info() {
		$userInfo = $this->userInfo;

		$ret .= ($userInfo->real_name || $userInfo->mid_name || $userInfo->last_name ? 'ชื่อจริง : '.($userInfo->name_prefix?$userInfo->name_prefix.' ':'').$userInfo->real_name.($userInfo->mid_name?' ('.$userInfo->mid_name.')':'').' '.$userInfo->last_name.'<br /><br />' : '').'
		'.($userInfo->occupation ? 'อาชีพ : '.$userInfo->occupation.'<br /><br />':'').'
		'.($userInfo->position? 'ตำแหน่ง : '.$userInfo->position.'<br /><br />':'').'
		'.($userInfo->organization ? 'องค์กร / บริษัท : '.$userInfo->organization.'<br /><br />':'').'
		'.($userInfo->address || $userInfo->amphur || $userInfo->province ? 'ที่อยู่ : '.$userInfo->address.' '.$userInfo->amphur.' '.$userInfo->province.' '.$userInfo->zipcode.' '.$userInfo->country.'<br /><br />':'').'
		'.($userInfo->latitude ? 'ละติจูด : '.$userInfo->latitude.'<br /><br />':'').'
		'.($userInfo->longitude ? 'ลองกิจูด : '.$userInfo->longitude.'<br /><br />':'').'
		'.($userInfo->phone && (user_access('administer users') || i()->uid==$userInfo->uid) ? 'โทรศัพท์ : '.$userInfo->phone.'<br /><br />':'').'
		'.($userInfo->mobile && (user_access('administer users') || i()->uid==$userInfo->uid) ? 'โทรศัพท์เคลื่อนที่ : '.$userInfo->mobile.'<br /><br />':'').'
		'.($userInfo->fax ? 'แฟกซ์ : '.$userInfo->fax.'<br /><br />':'').'
		'.($userInfo->website ? 'เว็บไซท์ : '.'<a href="'.$userInfo->website.'" target="_blank">'.$userInfo->website.'</a><br /><br />':'').'
		'.'คำหลัก'.' : <br /><br />
		'.($userInfo->about?'ประวัติย่อ'.' : <br /><br />'.sg_text2html($userInfo->about):'').'<br /><br />
		';
		return $ret;
	}
}
?>