<?php
/**
* My :: My Home
* Created 2021-12-14
* Modify  2021-12-14
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

import('widget:profile.photo.php');

class My extends Page {
	function build() {
		$uid = i()->uid;
		$userInfo = UserModel::get($uid);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => '{tr:Hello} @'.i()->name,
				// 'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]), // AppBar
			'sideBar' => R::View('my.menu'),
			'body' => new Column([
				'children' => [
					new ProfilePhotoWidget([
						'photo' => BasicModel::user_photo($userInfo->username),
						'children' => [
							new Form([
								'class' => 'sg-form -upload -upload-profile-photo',
								'enctype' => 'multipart/form-data',
								'action' => url('my/api/photo.change'),
								'rel' => 'notify',
								'done' => 'load:#main:'.url('my'),
								'children' => [
									'<span class="fileinput-button"><i class="icon -material">add_a_photo</i><input type="file" name="photo" class="inline-upload" accept="image/*;capture=camcorder" onchange="$(this).closest(form).submit(); return false;"></span>'
									], // children
							]), // Form

							user_access('administer users') ? new Container([
								'class' => '-sg-text-center',
								'child' => 'ชื่อที่ใช้ในการเข้าระบบ'.' : <strong><a href="'.url('admin/user/edit/'.$userInfo->uid).'">'.$userInfo->username.'</a></strong> | <a href="'.url('profile/'.$uid).'" class="sg-action" data-rel="#main">Refresh</a>']
							) : NULL,

							user_access('administer users','change own profile',$userInfo->uid) ? new Row([
								'mainAxisAlignment' => 'center',
								'children' => [
									'<a class="btn" href="'.url('my/change/detail').'">'.tr('change your profile details').'</a>&nbsp;',
									'<a class="sg-action btn" href="'.url('my/change/password').'" data-rel="#main">'.tr('change password').'</a>&nbsp;',
									'<a class="btn" href="'.url('my/change/photo').'">'.tr('change photo').'</a>',
								],
							]) : NULL,
						], // children
					]),

					new Card([
						'children' => [
							'<p><a href="'.url('paper/user/'.$userInfo->uid).'">'.tr('หัวข้อที่เขียนโดย ').$userInfo->name.'</a></p>',

							$userInfo->real_name || $userInfo->mid_name || $userInfo->last_name ? 'ชื่อจริง : '.($userInfo->name_prefix?$userInfo->name_prefix.' ':'').$userInfo->real_name.($userInfo->mid_name?' ('.$userInfo->mid_name.')':'').' '.$userInfo->last_name.'<br /><br />' : '',

							($userInfo->occupation ? 'อาชีพ : '.$userInfo->occupation.'<br /><br />':'').'
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
								<p align="right">'.'เริ่มเป็นสมาชิกตั้งแต่ วัน'.sg_date($userInfo->datein,'ววว ว ดด ปป H:i').' น.'.'<br />'
								. ($userInfo->login_time ? 'เข้าระบบล่าสุดเมื่อ วัน'.sg_date($userInfo->login_time,'ววว ว ดด ปปปป H:i').' น.'.'<br />' : NULL)
								. 'เข้าชมเว็บไซท์ : '.number_format($userInfo->hits).' ครั้ง'.'<br />'
								. 'อ่าน : '.number_format($userInfo->views).' ครั้ง'.'</p>',
							], // children
						]), // Card

					'<script type="text/javascript">
					$(document).ready(function () {
						$(".NO-CACHE").attr("src",function () { return $(this).attr("src") + "?a=" + Math.random() });
					});
					</script>',
				], // children
			]), // Widget
		]);
	}
}
?>