<?php
/**
* LMS :: View Module Information
* Created 2020-07-01
* Modify  2020-07-01
*
* @param Object $self
* @param Int $moduleInfo
* @return String
*/

$debug = true;

function lms_info($self, $courseInfo, $action, $tranId = NULL) {
	if (!($courseId = $courseInfo->courseId)) return message('error', 'PROCESS ERROR');

	$isAdmin = user_access('administer lms');
	$isTeacher = user_access('teacher lms');
	$isEditStudent = $isAdmin || $isTeacher || user_access('edit lms student');

	$ret = '';

	switch ($action) {
		case 'survey.create':
			if (($surveyId = $tranId) && SG\confirm()) {
				//$surveyInfo = mydb::select('SELECT * FROM %lms_survey%');
				$data = new stdClass();
				$data->qtref = mydb::select('SELECT `qtref` FROM %qtmast% WHERE `lmssurid` = :surveyId AND `uid` = :uid LIMIT 1', ':surveyId', $surveyId, ':uid', i()->uid)->qtref;

				if (!$data->qtref) {
					$data->lmssurid = $surveyId;
					$data->uid = i()->uid;
					$data->qtdate = date('Y-m-d');
					$data->created = date('U');
					$data->qtform = mydb::select('SELECT `formid` FROM %lms_survey% WHERE `surid` = :surveyId LIMIT 1', ':surveyId', $surveyId)->formid;

					$stmt = 'INSERT INTO %qtmast%
						(`lmssurid`, `qtform`, `uid`, `qtdate`, `created`)
						VALUES
						(:lmssurid, :qtform, :uid, :qtdate, :created)';

					mydb::query($stmt, $data);

					$data->qtref = mydb()->insert_id;

					//$ret .= mydb()->_query;
					//$ret .= print_o($data,'$data');
				}
				location('lms/survey/'.$data->qtref);
			}
			break;

		case 'mod.survey.save':
			$post = (Object) post('data');
			$qtRef = $tranId;

			$surveyInfo = R::Model('lms.survey.get', $qtRef);
			$isEdit = i()->uid == $surveyInfo->uid || user_access('administer lms');

			if ($isEdit) {
				$ret .= 'Survey Save';

				//mydb::query('DELETE FROM %qttran% WHERE `qtref` = :qtref', ':qtref', $qtRef);
				foreach ($post as $key => $value) {
					list($part, $field) = explode(':', $key);
					$dataList[$part]->{$field} = $field == 'rate' ? $post->{$part.':rate'} : $post->{$part.':value'};
				}
				$qtTran = mydb::select('SELECT `qtid`,`part` FROM %qttran% WHERE `qtref` = :qtref ORDER BY `qtid`; -- {key: "part"}', ':qtref', $qtRef)->items;

				foreach ($dataList as $key => $data) {
					$data->qtid = SG\getFirst($qtTran[$key]->qtid);
					$data->qtref = $qtRef;
					$data->part = $key;
					$data->rate = SG\getFirst($data->rate);
					$data->value = SG\getFirst($data->value);
					$data->ucreated = $data->umodify = i()->uid;
					$data->dcreated = $data->dmodify = date('U');
					$stmt = 'INSERT INTO %qttran%
						(`qtid`, `qtref`, `part`, `rate`, `value`, `ucreated`, `dcreated`)
						VALUES
						(:qtid, :qtref, :part, :rate, :value, :dcreated, :dcreated)
						ON DUPLICATE KEY UPDATE
						`rate` = :rate
						, `value` = :value
						, `umodify` = :umodify
						, `dmodify` = :dmodify
						';
					mydb::query($stmt, $data);
					//$ret .= mydb()->_query.'<br />';
				}
			}

			//$ret .= print_o($qtTran,'$qtTran');
			//$ret .= print_o($dataList,'$dataList');
			break;

		case 'mod.checkin':
			$classId = $tranId;
			$classInfo = R::Model('lms.class.get', $classId);
			if ($classInfo->classId AND SG\confirm()) {
				$data = new stdClass();
				$data->classid = $classId;
				$data->courseid = $classInfo->courseId;
				$data->modid = $classInfo->moduleId;
				$data->timein = date('Y-m-d H:i:s');
				$data->phone = post('phone');
				if (post('sid') AND ($isAdmin || $isTeacher)) {
					$data->sid = post('sid');
					$data->uid = mydb::select('SELECT `uid` FROM %lms_student% WHERE `sid` = :sid AND `status` = "Active" LIMIT 1', $data)->uid;
				} else {
					$data->uid = i()->uid;
					$data->sid = mydb::select('SELECT `sid` FROM %lms_student% WHERE `uid` = :uid AND `status` = "Active" LIMIT 1', $data)->sid;
				}

				$stmt = 'INSERT INTO %lms_checkin%
					(`courseid`, `modid`, `classid`, `sid`, `uid`, `phone`, `timein`)
					VALUES
					(:courseid, :modid, :classid, :sid, :uid, :phone, :timein)';
				mydb::query($stmt, $data);
				// Set cookie when checkin with phone
				if ($data->phone) {
					setcookie('lms.checkin',$data->phone,time()+3*60*60,'/');
				}
				//$ret .= mydb()->_query;
			}
			break;

		case 'mod.checkout':
			if (($checkId = $tranId) && SG\confirm()) {
				$stmt = 'SELECT * FROM %lms_checkin% WHERE `chkid` = :chkid LIMIT 1';
				$checkInInfo = mydb::select($stmt, ':chkid', $checkId);
				if ($checkInInfo->phone) {
					setcookie('lms.checkin',NULL,time()-1000,'/');
				}

				$stmt = 'UPDATE %lms_checkin% SET `timeout` = :timeout WHERE `chkid` = :chkid LIMIT 1';
				mydb::query($stmt, ':chkid', $checkId, ':timeout', date('Y-m-d H:i:s'));
				//$ret .= mydb()->_query;
			}
			break;

		case 'mod.checkout.all':
			if (($moduleId = $tranId) && ($classId = post('cid')) && SG\confirm()) {
				$stmt = 'UPDATE %lms_checkin%
					SET `timeout` = :timeout
					WHERE  `courseid` = :courseId AND `modid` = :moduleId
						AND `classid` = :classId
						AND `timeout` IS NULL';

				mydb::query($stmt, ':courseId', $courseId, ':moduleId',$moduleId, ':classId', $classId, ':timeout', date('Y-m-d H:i:s'));
				//$ret .= mydb()->_query;
			}
			break;

		case 'mod.checkin.clear':
			if (($checkId = $tranId) && SG\confirm() && ($isAdmin || $isTeacher)) {
				$stmt = 'DELETE FROM %lms_checkin% WHERE `chkid` = :chkid LIMIT 1';
				mydb::query($stmt, ':chkid', $checkId);
				//$ret .= mydb()->_query;
			}
			break;
		
		case 'student.add':
			if (($uid = post('uid')) && $isAdmin) {
				$data = new stdClass();
				$data->courseid = $courseId;
				$data->uid = $uid;
				$data->prename = SG\getFirst($data->prename,' ');
				list($data->name, $data->lname) = sg::explode_name(' ',post('q'));
				$data->serno = $tranId;
				$data->status = 'Active';
				$data->created = date('U');
				$stmt = 'INSERT INTO %lms_student%
					(`courseid`, `uid`, `prename`, `name`, `lname`, `serno`, `status`, `created`)
					VALUES
					(:courseid, :uid, :prename, :name, :lname, :serno, :status, :created)
					ON DUPLICATE KEY UPDATE
					`uid` = :uid';

				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;
			}
			//$ret .= print_o(post(),'post()');
			break;

		case 'student.remove':
			if ($tranId && $isAdmin && SG\confirm()) {
				$studentInfo = R::Model('lms.course.student', $tranId);
				$stmt = 'DELETE FROM %lms_student% WHERE `courseid` = :courseId AND `sid` = :sid LIMIT 1';
				mydb::query($stmt, ':courseId', $courseId, ':sid', $tranId);

				$stmt = 'DELETE FROM %lms_checkin% WHERE `courseid` = :courseId AND `uid` = :uid';
				mydb::query($stmt, ':courseId', $courseId, ':uid', $studentInfo->uid);
			}
			break;

		case 'student.status':
			if ($tranId && ($status = post('status')) && ($isAdmin || $isTeacher)) {
				$stmt = 'UPDATE %lms_student% SET `status` = :status WHERE `sid` = :sid';
				mydb::query($stmt, ':sid', $tranId, ':status', $status);
				//$ret .= mydb()->_query;
			}
			break;

		case 'student.save':
			$studentInfo = R::Model('lms.student.get', $tranId);
			$studentId = $studentInfo->studentId;
			$data = (Object) post('data');
			if ($studentId && $data->name && ($isEditStudent || i()->uid == $studentInfo->uid)) {
				$data->studentId = $studentId;
				$data->coursetype = SG\getFirst($data->coursetype);
				$address = SG\explode_address($data->house, $data->areacode);
				$data->house = $address['house'];
				$ret .= 'SAVED';
				$stmt = 'UPDATE %lms_student% SET
					`prename` = :prename, `name` = :name, `lname` = :lname
					, `enprename` = :enprename, `enname` = :enname, `enlname` = :enlname
					, `scode` = :scode, `serno` = :serno
					, `coursetype` = :coursetype
					, `email` = :email, `phone` = :phone
					, `idcard` = :idcard
					, `house` = :house, `areacode` = :areacode
					, `zip` = :zip
					WHERE `sid` = :studentId
					LIMIT 1
					';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;

				$stmt = 'UPDATE %users% SET `name` = :name WHERE `uid` = :uid LIMIT 1';
				mydb::query($stmt, ':name', $data->name.' '.$data->lname, ':uid',$studentInfo->uid);
			} else {
				$ret .= 'Access Denied';
			}
			break;

		case 'student.coursetype.save':
			if ($isEditStudent) {
				$stmt = 'UPDATE %lms_student% SET `coursetype` = IF(`coursetype` = "ONLINE", NULL, "ONLINE") WHERE `sid` = :studentId LIMIT 1';
				mydb::query($stmt, ':studentId', $tranId);
				//$ret .= mydb()->_query;
			}
			break;

		case 'student.photo.change':
			$post = (Object) post();
			$studentInfo = R::Model('lms.student.get', $tranId);
			if ($post->id) {
				//$uploadResult = R::Model('photo.save', $_FILES['photo']);
				$photo = (object)$_FILES['photo'];
				$photo->name = $studentInfo->info->photo;
				$photo->overwrite = true;
				$uploadResult = R::Model('photo.save', $photo);
				if ($uploadResult->complete && $uploadResult->save->_file) {
					//$ret .= '<img src="'.imed_model::patient_photo($psnId).'" width="100%" height="100%" />';
				}
			} else {
				$data->tpid = NULL;
				$data->prename = 'lms'.($post->tagname ? '_'.$post->tagname : '').'_'.$tranId.'_';
				$data->tagname = 'lms'.($post->tagname ? ','.$post->tagname : '');
				$data->title = $post->title;
				$data->refid = $tranId;
				$data->cid = NULL;
				$data->uid = SG\getFirst($post->uid,i()->uid);
				//$data->deleteurl = $post->delete == 'none' ? NULL : 'project/'.$tpid.'/info/photo.delete/';
				//$data->link = $post->link;
				$uploadResult = R::Model('photo.upload', $_FILES['photo'], $data);

				if($uploadResult->error) {
					//$ret = implode(' ', $uploadResult->error);
				} else {
					//$ret = $uploadResult->link;
				}

				//$ret .= print_o($data,'$data');
			}
			//$ret .= print_o($uploadResult,'$uploadResult');
			break;

		default:
			$ret = 'NO ACTION';
			break;
	}

	return $ret;
}
?>