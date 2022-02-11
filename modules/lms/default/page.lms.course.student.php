<?php
/**
* LMS :: Course Student List
* Created 2020-07-01
* Modify  2020-07-10
*
* @param Object $self
* @param Object $courseInfo
* @param Int $serNo
* @return String
*/

$debug = true;

function lms_course_student($self, $courseInfo, $serNo = NULL) {
	R::View('toolbar', $self, 'นักศึกษา/'.$courseInfo->name, 'lms', $courseInfo, '{searchform: false}');

	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$isAdmin = user_access('administer lms');

	$ret = '<div id="lms_course_student" data-url="'.url('lms/'.$courseId.'/course.student/'.$serNo).'">';

	$ui = new Ui();
	$ui->addConfig('nav', '{class: "nav -page"}');

	$dbs = mydb::select('SELECT `serno`, COUNT(*) `amt` FROM %lms_student% WHERE `courseid` = :courseId GROUP BY `serno`', ':courseId', $courseId);

	foreach ($dbs->items as $rs) {
		$ui->add('<a class="sg-action btn" href="'.url('lms/'.$courseId.'/course.student/'.$rs->serno).'" title="'.$rs->amt.' คน" data-rel="parent:div">รุ่น '.$rs->serno.'</a>');
	}

	$ret .= $ui->build();

	if ($isAdmin && $serNo) {
		$optionSerNo = '';
		for($i = 1; $i <= 10; $i++) {
			$optionSerNo .= '<option value="'.$i.'"'.($serNo == $i ? ' selected="selected"' : '').'>รุ่น '.$i.'</option>';

		}
		$ret .= '<form class="form sg-form" action="'.url('lms/'.$courseId.'/info/student.add/'.$serNo).'" data-rel="notify" data-done="load:parent div:'.url('lms/'.$courseId.'/course.student/'.$serNo).'" style="margin: 4px 0;">'
			. '<input type="hidden" name="uid" id="uid" />'
			. '<div class="form-item -group"><span class="form-group">'
			. '<input class="form-text -fill sg-autocomplete" type="text" name="q" value="'.post('q').'" placeholder="username or email" data-query="'.url('api/user').'" data-altfld="uid">'
			. '<div class="input-append">'
			. '<span><button class="btn" type="submit"><i class="icon -material">add_circle</i><span>เพิ่มนักศึกษารุ่น '.$serNo.'</span></button></span>'
			. '</div>'
			. '</span></div>'
			. '</form>';
	}

	$ret .= '<header class="header"><h5>รายชื่อนักศึกษา '.($serNo ? 'รุ่น '.$serNo : 'ปัจจุบัน').'</h5></header>';

	mydb::where('s.`courseid` = :courseId', ':courseId', $courseId);
	if ($serNo) {
		mydb::where('s.`serno` = :serno', ':serno', $serNo);
	} else {
		mydb::where('s.`status` = "Active"');
	}

	$stmt = 'SELECT s.*, u.`username`
		, f.`fid` `photoId`
		, f.`file` `photo`
		FROM %lms_student% s
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %topic_files% f ON f.`tagname` = "lms,profile" AND f.`refid` = s.`sid`
		%WHERE%
		ORDER BY `scode` ASC
		';

	$dbs = mydb::select($stmt);


	$cardUi = new Ui('div', 'ui-card -sg-flex lms-info-student');


	foreach ($dbs->items as $rs) {
		$dropUi = new Dropbox([
			'children' => $isAdmin ? [
				'<a class="sg-action" href="'.url('lms/student/'.$rs->sid).'" data-rel="box" data-width="640"><i class="icon -material">edit</i><span>Edit</span></a>',
				'<a class="sg-action" href="'.url('lms/'.$courseId.'/course.student.status/'.$rs->sid).'" data-rel="box" data-width="640"><i class="icon -material">done</i><span>Set Status</span></a>',
				'<a class="sg-action" href="'.url('lms/'.$courseId.'/info/student.remove/'.$rs->sid).'" data-rel="notify" data-title="ลบชื่อนักศึกษา" data-confirm="ต้องการลบชื่อนักศึกษาออกจากหลักสูตร กรุณายืนยัน?" data-done="remove:parent .ui-card>.ui-item"><i class="icon -material">cancel</i><span>Remove</span></a>',
			] : NULL,
		]);

		$cardStr = '<div class="header">&nbsp;'
			. '<nav class="nav -header">'
			. ($rs->coursetype == 'ONLINE' ? '<a class="btn -link"><i class="icon -material">public</i></a>' : '')
			. $dropUi->build().'</nav>'
			.'</div>';

		$profilePhoto = model::get_photo_property($rs->photo);
		if (empty($profilePhoto->_url)) $profilePhoto->_url = '/css/img/photography.png';
		//$cardStr .= print_o($profilePhoto,'$profilePhoto');

		$cardStr .= '<div class="detail">';
		$cardStr .= '<img class="profile-photo" src="'.$profilePhoto->_url.'?'.$profilePhoto->_filesize.'" />'
			. $rs->prename.' '.$rs->name.' '.$rs->lname.'<br />'
			. SG\getFirst($rs->scode,'&nbsp');

		$cardStr .= '</div><!-- detail -->';

		$cardUi->add($cardStr, '{class: "sg-action", "href": "'.url('lms/student/'.$rs->sid).'", "data-rel": "box", "data-width": 640}');
	}



	$ret .= $cardUi->build();

	//$ret .= print_o($dbs,'$dbs');
	//$ret .= print_o($courseInfo, '$courseInfo');

	$ret .= '<style tyle="text/css">
	.lms-info-student>.ui-item {flex: 0 0 240px; text-align: center;}
	.lms-info-student .profile-photo {width: 120px; height: 120px; display: block; margin: 0 auto 16px;}
	</style>';

	$ret .= '</div><!-- lms_course_student -->';
	return $ret;
}
?>