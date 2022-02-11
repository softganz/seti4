<?php
/**
* LMS :: View Course Alumni
* Created 2020-07-01
* Modify  2020-07-01
*
* @param Object $self
* @param Object $courseInfo
* @return String
*/

$debug = true;

function lms_course_alumni($self, $courseInfo, $serno = NULL) {
	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$isAdmin = user_access('administer lms');

	$ret = '<header class="header"><h5>รายชื่อศิษย์เก่า '.($serno ? 'รุ่น '.$serno : 'ปัจจุบัน').'</h5></header>';

	if (!$serno) {
		$stmt = 'SELECT `serno`, COUNT(*) FROM %lms_student% GROUP BY `serno`';
		$dbs = mydb::select($stmt);

		$ui = new Ui(NULL, 'ui-album -justify-left');
		foreach ($dbs->items as $rs) {
			$ui->add('<a class="sg-action btn" href="'.url('lms/'.$courseId.'/course.alumni/'.$rs->serno).'" data-rel="replace:.ui-album">รุ่น '.$rs->serno.'</a>');
		}
		$ret .= $ui->build();
	} else {
		mydb::where('s.`courseid` = :courseId', ':courseId', $courseId);
		mydb::where('s.`status` = "Graduate"');
		mydb::where('s.`serno` = :serno', ':serno', $serno);

		$stmt = 'SELECT s.*, u.`username`
			FROM %lms_student% s
				LEFT JOIN %users% u USING(`uid`)
			%WHERE%
			ORDER BY `scode` ASC
			';

		$dbs = mydb::select($stmt);


		$cardUi = new Ui('div', 'ui-card -sg-flex lms-info-student');


		foreach ($dbs->items as $rs) {
			$cardStr = '<div class="detail">';
			$cardStr .= '<img class="profile-photo" src="'.model::user_photo($rs->username).'" />'
				. $rs->prename.' '.$rs->name.' '.$rs->lname.'<br />'
				. $rs->scode;

			$cardStr .= '</div><!-- detail -->';

			$cardUi->add($cardStr);
		}



		$ret .= $cardUi->build();
	}

		//$ret .= print_o($dbs,'$dbs');
		//$ret .= print_o($courseInfo, '$courseInfo');

	$ret .= '<style tyle="text/css">
	.lms-info-student>.ui-item {flex: 0 0 240px; text-align: center;}
	.lms-info-student .profile-photo {width: 120px; height: 120px; display: block; margin: 0 auto 16px;}
	.ui-album>.ui-item {width: 100px;}
	.ui-album>.ui-item>a {padding: 32px;}
	</style>';

	return $ret;
}
?>