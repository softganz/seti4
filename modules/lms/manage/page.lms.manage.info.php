<?php
/**
* LMS : Model Manage Information
* Created 2020-08-01
* Modify  2020-08-01
*
* @param Object $self
* @param Object $courseId
* @return String
*
* @usage lms/{$courseId}/manage.info
*/

$debug = true;

function lms_manage_info($self, $courseInfo = NULL, $action = NULL, $tranId = NULL) {
	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$ret = '';

	$isAdmin = user_access('administer lms');
	$isOfficer = $isAdmin;
	
	if (!$isOfficer) return message('error', 'Access Denied');

	switch ($action) {
		case 'timetable.save':
			$data = (Object) post('data');
			$data->classid = SG\getFirst($tranId);
			$data->start = sg_date($data->start, 'Y-m-d').' '.$data->timestart;
			$data->end = sg_date($data->end, 'Y-m-d').' '.$data->timeend;
			$data->courseId = $courseId;
			if ($data->title) {
				$stmt = 'INSERT INTO %lms_timetable%
					(`classid`, `courseid`, `modid`, `serno`, `start`, `end`, `openbeforemin`, `openaftermin`, `title`, `speaker`, `detail`)
					VALUES
					(:classid, :courseId, :modid, :serno, :start, :end, :openbeforemin, :openaftermin, :title, :speaker, :detail)
					ON DUPLICATE KEY UPDATE
					`courseid` = :courseId
					, `modid` = :modid
					, `serno` = :serno
					, `start` = :start
					, `end` = :end
					, `openbeforemin` = :openbeforemin
					, `openaftermin` = :openaftermin
					, `title` = :title
					, `speaker` = :speaker
					, `detail` = :detail
					';

				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;
			}
			//$ret .= print_o($data, '$data');
			break;

		case 'timetable.delete':
			if (SG\confirm()) {
				$stmt = 'DELETE FROM %lms_timetable% WHERE `classid` = :classId AND `courseid` = :courseId LIMIT 1';
				mydb::query($stmt, ':classId', $tranId, ':courseId', $courseId);
				//$ret .= mydb()->_query;
			}
			break;

		case 'homepage.save':
			$data = new stdClass();
			$data->courseId = $courseId;
			$data->html = post('html');
			$result = R::Model('lms.course.homepage.save', $data, '{debug: false}');
			$ret .= 'HOMEPAGE SAVED';
			//$ret .= print_o($data, '$data');
			//$ret .= print_o($result, '$result');
			break;

		case 'navigator.save':
			$ret .= 'SAVED';
			$name = 'navigator.lms.'.$courseId;
			cfg_db($name, post('navigator'));
			break;

		default:
			$ret = 'ERROR : ACTION NOT FOUND';
			break;
	}

	return $ret;
}
?>