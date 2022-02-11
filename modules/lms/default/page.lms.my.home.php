<?php
/**
* LMS :: View Student Information
* Created 2020-07-11
* Modify  2020-07-11
*
* @param Object $self
* @param Object $studentInfo
* @return String
*/

$debug = true;

function lms_my_home($self, $studentInfo) {
	if (!($studentId = $studentInfo->studentId)) return message('error', 'PROCESS ERROR');

	R::View('toolbar', $self, 'นักศึกษา/'.$studentInfo->name, 'lms', $studentInfo, '{searchform: false}');

	$isAdmin = user_access('administer lms');
	$isTeacher = user_access('teacher lms');
	$isOwner = i()->ok && i()->uid == $studentInfo->uid;

	$isViewDetail = $isAdmin || $isTeacher || (i()->ok && i()->uid == $studentInfo->uid);
	$isEdit = $isAdmin || $isTeacher || $isOwner;

	$headerUi = new Ui();
	$headerUi->addConfig('nav', '{class: "nav"}');

	$dropUi = new Dropbox([
		'children' => $isAdmin ? [
			'<a class="sg-action" href="'.url('lms/student/'.$studentId.'/status').'" data-rel="box" data-width="480"><i class="icon -material">done</i><span>Set Status</span></a>',
			'<a class="sg-action" href="'.url('lms/'.$studentInfo->courseId.'/info/student.remove/'.$studentId).'" data-rel="notify" data-title="ลบชื่อนักศึกษา" data-confirm="ต้องการลบชื่อนักศึกษาออกจากหลักสูตร กรุณายืนยัน?" data-done="remove:parent .ui-card>.ui-item"><i class="icon -material">cancel</i><span>Remove</span></a>',
		] : NULL,
	]);

	$headerUi->add($dropUi->build());

	$ret = '<div id="lms-my-home" class="lms-my-home" data-url="'.url('lms/my').'">'
		. '<header class="header"><h3>'.$studentInfo->name.'</h3>'.$headerUi->build().'</header>';;


	$profilePhoto = model::get_photo_property($studentInfo->info->photo);
	if (empty($profilePhoto->_url)) $profilePhoto->_url = '/css/img/photography.png';

	//$ret .= '<div class="ui-card"><div class="ui-item">';
	$ret .= '<div class="detail">';

	$ret .= '<section><div class="-form-row">'
		. '<span style="position: relative;">'
		. '<img class="profile-photo -sg-128" src="'.$profilePhoto->_url.'?'.$profilePhoto->_filesize.'" width="128" height="128" />'
		. (
			i()->uid == $studentInfo->uid ?
			'<form class="sg-form -upload" method="post" enctype="multipart/form-data" action="'.url('lms/'.$studentInfo->courseId.'/info/student.photo.change/'.$studentInfo->studentId).'" data-rel="notify" data-done="load->replace:#lms-my-home" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; padding: 0; margin: 0; background-color: transparent;">'
			. '<input type="hidden" name="id" value="'.$studentInfo->info->photoId.'" />'
			. '<input type="hidden" name="tagname" value="profile" />'
			. '<input type="hidden" name="uid" value="'.$studentInfo->uid.'" />'
			. '<span class="fileinput-button" style="position: absolute; top: 0; left: 0; bottom: 0; right: 0;">'
			. '<input type="file" name="photo" class="inline-upload" accept="image/*;capture=camcorder" onchange=\'$(this).closest(form).submit(); return false;\' />'
			. '</span></form>'
			: ''
		)
		. ( i()->uid == $studentInfo->uid ?
			'<form class="sg-form -upload" method="post" enctype="multipart/form-data" action="'.url('lms/'.$studentInfo->courseId.'/info/student.photo.change/'.$studentInfo->studentId).'" data-rel="notify" data-done="load->replace:#lms-my-home"><span class="btn -link fileinput-button">'
		. '<input type="hidden" name="id" value="'.$studentInfo->info->photoId.'" />'
		. '<input type="hidden" name="tagname" value="profile" />'
		. '<input type="hidden" name="uid" value="'.$studentInfo->uid.'" />'
		. '<i class="icon -material">add_a_photo</i><span>Change Photo</span>'
		. '<input type="file" name="photo" class="inline-upload" accept="image/*;capture=camcorder" onchange=\'$(this).closest(form).submit(); return false;\' />'
		. '</span></form>'
		: '')
		. '</span>'

		. '<span class="profile">'
		. '<span class="poster-name">'
		. '<b>'.$studentInfo->name.'</b><br />'
		. ($studentInfo->info->enname ? ' ('.$studentInfo->info->enprename.$studentInfo->info->enname.' '.$studentInfo->info->enlname.')' : '')
		. '</span>'
		. '</span>'
		. '</div>'
		. '</section>';


	$ret .= '<section><h4>Student Details</h4>'
		. '<div class="-form-row">'
		. '<span>รหัสนักศึกษา </span><span><b>'.SG\getFirst($studentInfo->info->scode,'&nbsp').'</b></span>'
		. '</div>'
		. '<div class="-form-row">'
		. '<span>รุ่น </span><span><b>'.$studentInfo->info->serno.'</b></span>'
		. '</div>'
		. '<div class="-form-row">'
		. '<span>สถานภาพ </span>'
		. '<span>'.($isAdmin ? '<a class="sg-action btn -link" href="'.url('lms/student/'.$studentId.'/status').'" data-rel="box" data-width="480">'.$studentInfo->status.'</a>' : '<b>'.$studentInfo->status.'</b>').'</span>'
		. '</div>'
		. '</section>';	

	if ($isViewDetail) {
		$ret .= '<section><h4>Contact Details</h4>'
			. '<div class="-form-row">'
			. '<span><i class="icon -material">email</i></span>'
			. '<span>'.SG\getFirst($studentInfo->info->email, $studentInfo->info->userEmail).'</span>'
			. ($isEdit ? '<span class="-row-action"><a class="sg-action btn -link" href="'.url('lms/student/'.$studentId.'/edit', array('load' => 'main')).'" data-rel="box" data-width="640"><i class="icon -material">edit</i><span>Edit</span></a></span>' : '')
			. '</div>'

			. '<div class="-form-row">'
			. '<span><i class="icon -material">phone</i></span><span>'.SG\getFirst($studentInfo->info->phone, $studentInfo->info->userPhone).'</span>'
			. '</div>'

			. '<div class="-form-row">'
			. '<span><i class="icon -material">home</i></span><span>'.$studentInfo->info->address.'</span>'
			. '</div>'

			. '<div class="-form-row">'
			. '<span><i class="icon -material">person</i></span><span>'.$studentInfo->info->idcard.'</span>'
			. '</div>'

			. '</section>';
	}


	if ($isOwner) {

		$ret .= '<section>'
			. '<div class="-form-row">'
			. '<span class="-row-label">Sign-In Password</span><span id="lms-my-home-password" class="-row-info">******</span>'
			. '<span class="-row-action"><a class="sg-action btn -link" href="'.url('my/change/password').'" data-rel="box" data-width="480"><i class="icon -material">edit</i><span>Edit</span></a></span>'
			. '</div>'
			. '</section>';
	}

	$tables = new Table();
	$tables->thead = array('รายวิชา', 'เช็คอิน', 'เช็คเอ้าท์');
	foreach (mydb::select('SELECT c.*, m.`name` `moduleName`, m.`code` `moduleCode` FROM %lms_checkin% c LEFT JOIN %lms_mod% m USING(`modid`) WHERE c.`uid` = :uid', ':uid', $studentInfo->uid)->items as $rs) {
		$tables->rows[] = array($rs->moduleName.' ('.$rs->moduleCode.')', $rs->timein, $rs->timeout);
	}


	$ret .= '<section><h4>Checkins</h4>'
		. '<div>'.$tables->build().'<div>'
		. '</section>';

	$ret .= '<section>'
		. '<div><span>เข้าสู่ระบบ </span><span>'.sg_date($studentInfo->info->created, 'ว ดด ปปปป').'</span><div>'
		. '</section>';

	$ret .= '</div><!-- detail -->';

	//$ret .= '</div></div>';
	//$ret .= print_o($studentInfo, '$studentInfo');

	$ret .= '<style tyle="text/css">
	.-form-row {display: flex;}
	.-form-row>.-row-label {flex: 0 0 160px;}
	.-form-row>.-row-info {flex: 1;}
	.-form-row>.-row-action {}

	.-form-row>*:first-child {flex: 0 0 160px;}
	.-form-row>*:nth-child(2) {flex: 1}
	.-form-row>*:nth-child(n+2) {}

	.detail>section {padding: 16px; border-bottom: 1px #eee solid;}
	.detail>section:first-child {border-top: 1px #eee solid;}
	section {position: relative;}
	section .-form-row>.-row-action {}
	#lms-my-home-password .btn.-cancel,
	#lms-my-home-password .header, #lms-my-home-password .help {display: none;}
	</style>';

	$ret .= '</div><!-- lms-my-home -->';

	return $ret;
}
?>