<?php
/**
* Project owner
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/

define(_KAMSAIINDICATOR,'weight');
define(_INDICATORHEIGHT,'height');

function project_knet($self, $orgId = NULL, $action = NULL, $tranId = NULL) {
	if (empty($orgId) && empty($action)) return R::Page('project.knet.home',$self);
	if ($orgId && empty($action)) return R::Page('project.knet.info',$self, $orgId);

	$orgInfo = is_object($orgId) ? $orgId : R::Model('school.get',$orgId);
	$orgId = $orgInfo->orgid;


	if (!$orgId) return message('error', 'ไม่มีข้อมูลองค์กรที่ระบุ');

	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isEdit = ($isAdmin || in_array($orgInfo->officers[i()->uid],array('ADMIN','OFFICER'))) && post('mode') != 'view';

	//$ret .= 'Action = '.$action. ' Is edit = '.($isEdit ? 'YES' : 'NO').'<br />';
	//$ret .= print_o($orgInfo, '$orgInfo');

	switch ($action) {
		case 'school.save':
			if ($isEdit) {
				$data = (object) post('org');
				$data->orgid = $orgId;
				$data->uid = i()->uid;
				$data->networkType = 2;
				$data->created = date('U');

				$address = SG\explode_address($data->address, $data->areacode);
				$data->house = $address['house'];
				if ($address['zip'] && !$data->zip) $data->zip = $address['zip'];
				$data->classlevel = $data->classlevel ? implode(',',$data->classlevel) : NULL;

				$stmt = 'UPDATE %db_org%
						SET
						  `name` = :name
						, `phone` = :phone, `email` = :email
						, `areacode` = :areacode, `house` = :house, `zipcode` = :zip
						, `managername` = :managername, `contactname` = :contactname
						, `groupType` = :groupType
						WHERE `orgid` = :orgid LIMIT 1';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query.'<br />';

				mydb::query(
					'INSERT INTO %school%
					(`orgId`, `uid`, `networkType`, `studentAmt`, `classLevel`, `created`)
					VALUES
					(:orgid, :uid, :networkType, :studentamt, :classlevel, :created)
					ON DUPLICATE KEY UPDATE
					`studentAmt` = :studentamt
					, `classLevel` = :classlevel',
					$data
				);
				// debugMsg(mydb()->_query);
				// debugMsg($data,'$data');
			}
			break;

		case 'action.save':
			if ($isEdit) {
				$data = (object) post('action');
				$data->actionId = intval($tranId) > 0 ? intval($tranId) : NULL;
				$data->title = SG\getFirst($data->title);
				$data->activityId = SG\getFirst($data->activityId);
				$data->orgid = $orgId;
				$data->actionDate = sg_date($data->actionDate ? $data->actionDate :date('U'), 'Y-m-d');
				$data->to_date = $data->actionDate;
				$data->actionTime = SG\getFirst($data->actionTime);
				$data->actionReal = SG\getFirst($data->actionReal);
				$data->outputOutcomeReal = SG\getFirst($data->outputOutcomeReal);
				$data->uid = i()->uid;
				$data->created = date('U');

				$data->sorderAction = mydb::select(
					'SELECT MAX(`sorder`) `lastOrder` FROM %project_tr% WHERE `orgid` = :orgid AND `formid` = "activity" AND `part` = "owner" LIMIT 1',
					[':orgid' => $orgId]
				)->lastOrder + 1;
				$data->sorderActivity = mydb::select(
					'SELECT MAX(`sorder`) `lastOrder` FROM %project_tr% WHERE `orgid` = :orgid AND `formid` = "info" AND `part` = "activity" LIMIT 1',
					[':orgid' => $orgId]
				)->lastOrder + 1;

				// Create activity
				$stmt = 'INSERT INTO %project_tr%
					(
					  `trid`, `orgid`, `sorder`
					, `formid`, `part`
					, `date1`, `date2`
					, `detail1`
					, `uid`
					, `created`
					)
					VALUES
					(
					  :activityId, :orgid, :sorderActivity
					, "info", "activity"
					, :actionDate, :to_date
					, :title
					, :uid
					, :created
					)
					ON DUPLICATE KEY UPDATE
					  `detail1` = :title
					';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query.'<br />';

				if (empty($data->activityId)) $data->activityId = mydb()->insert_id;

				// Create action
				$stmt = 'INSERT INTO %project_tr%
					(
					  `trid`, `refid`, `orgid`, `sorder`
					, `formid`, `part`
					, `date1`
					, `detail1`
					, `text2`
					, `text4`
					, `uid`
					, `created`
					)
					VALUES
					(
					  :actionId, :activityId, :orgid, :sorderAction
					, "activity", "org"
					, :actionDate
					, :actionTime
					, :actionReal
					, :outputOutcomeReal
					, :uid
					, :created
					)
					ON DUPLICATE KEY UPDATE
					  `refid` = :activityId
					, `date1` = :actionDate
					, `detail1` = :actionTime
					, `text2` = :actionReal
					, `text4` = :outputOutcomeReal
					';

				mydb::query($stmt, $data);
				//$ret .= mydb()->_query.'<br />';

				if (empty($data->actionId)) $data->actionId = mydb()->insert_id;

				if (empty($data->tpid)) {
					mydb::query(
						'DELETE FROM %project_actguide%
						WHERE `tpid` = 0 AND `calid` = 0 AND `actionid` = :actionId'
						. ($data->guideId ? ' AND `guideid` NOT IN (:guideidset)' : ''),
						[
							':actionId' => $data->actionId,
							':guideidset' => 'SET:'.implode(',',$data->guideId)
						]
					);
					//$ret .= mydb()->_query.'<br />';

					foreach ($data->guideId as $key => $value) {
						mydb::query(
							'INSERT INTO %project_actguide%
								(`tpid`,`calid`,`actionid`,`guideid`)
								VALUES
								(0,0,:actionId,:guideId)
								ON DUPLICATE KEY UPDATE
								`guideid` = :guideId',
							[
								':actionId' => $data->actionId,
								':guideId' => intval($value)
							]
						);
						//$ret.=mydb()->_query.'<br />';
					}

					mydb::query(
						'DELETE FROM %project_standard%
						WHERE `actionId` = :actionId'
						. ($data->standardId ? ' AND `standardId` NOT IN (:standardId)' : ''),
						[
							':actionId' => $data->actionId,
							':standardId' => 'SET:'.implode(',',$data->standardId)
						]
					);
					// debugMsg(mydb()->_query);

					foreach ($data->standardId as $key => $value) {
						mydb::query(
							'INSERT INTO %project_standard%
								(`orgId`, `actionId`, `standardId`)
								VALUES
								(:orgId, :actionId, :standardId)
								ON DUPLICATE KEY UPDATE
								`standardId` = :standardId',
							[
								':orgId' => $orgId,
								':actionId' => $data->actionId,
								':standardId' => intval($value),
							]
						);
						// debugMsg(mydb()->_query);
					}
				}

				//$ret .= print_o($data,'$data');
			}
			break;

		case 'action.edit':
			$data = R::Model('project.action.get', ['actionId' => $tranId],'{debug: false}');
			$ret .= R::Page('project.knet.action.add', $self, $orgInfo, $data);
			// $ret .= print_o($data,'$data');
			break;

		case 'action.delete':
			if ($isEdit && $tranId && SG\confirm()) {
				$activityId = mydb::select(
					'SELECT `refid` FROM %project_tr% WHERE  `trid` = :actionId AND `orgid` = :orgid LIMIT 1',
					[':orgid' => $orgId, ':actionId' => $tranId]
				)->refid;
				//$ret .= mydb()->_query.'<br />';

				mydb::query(
					'DELETE FROM %project_tr% WHERE `trid` = :actionId AND `orgid` = :orgid LIMIT 1',
					[':orgid' => $orgId, ':actionId' => $tranId]
				);
				//$ret .= mydb()->_query.'<br />';

				mydb::query(
					'DELETE FROM %project_tr% WHERE `trid` = :activityId AND `orgid` = :orgid LIMIT 1',
					[':orgid' => $orgId, ':activityId' => $activityId]
				);
				//$ret .= mydb()->_query.'<br />';

				mydb::query(
					'DELETE FROM %project_actguide% WHERE `tpid` = 0 AND `calid` = 0 AND `actionid` = :actionId',
					[':actionId' => $tranId]
				);
				// $ret .= mydb()->_query.'<br />';

				mydb::query(
					'DELETE FROM %project_standard% WHERE `actionId` = :actionId',
					[':actionId' => $tranId]
				);
				// $ret .= mydb()->_query.'<br />';

				// Delete Photo
				$photoDbs = mydb::select(
					'SELECT
					f.`fid`, f.`refid`, f.`type`, f.`tagname`, f.`file`, f.`title`
					FROM %topic_files% f
					WHERE f.`orgid` = :orgid AND f.`refid` = :refid AND f.`tagname` = "project,knet,action"
					',
					[':orgid' => $orgId, ':refid' => $tranId]
				);

				foreach ($photoDbs->items as $rs) {
					$result = R::Model('photo.delete',$rs->fid);
					//$ret .= print_o($result,'$result');
				}

				mydb::query(
					'DELETE FROM %topic_files% WHERE `orgid` = :orgid AND `refid` = :refid AND `tagname` = "project,knet,action" ',
					[':orgid' => $orgId, ':refid' => $tranId]
				);
				//$ret .= mydb()->_query.'<br />';
			}
			break;

		case 'photo.upload':
			if ($isEdit) {
				$post = (Object) post();
				$data->prename = 'project_knet_'.$orgId.'_';
				$data->tagname = 'project,knet'.($post->tagname ? ','.$post->tagname : '');
				$data->title = $post->title;
				$data->orgid = $orgId;
				$data->refid = SG\getFirst($tranId);
				$data->deleteurl = 'project/knet/'.$orgId.'/photo.delete/';
				$uploadResult = R::Model('photo.upload', $_FILES['photo'], $data);

				if($uploadResult->error) {
					$ret = implode(' ', $uploadResult->error);
				} else {
					$ret = $uploadResult->link;
				}
			}
			break;

		case 'photo.delete':
			if ($isEdit && $tranId && SG\confirm()) {
				$result = R::Model('photo.delete',$tranId);
				$ret .= 'Photo Deleted!!!';
			}
			break;

		case 'weight.edit':
			$weightInfo = R::Model('project.knet.weight.get', $tranId);
			$ret .= R::Page('project.knet.weight.add', $self, $orgInfo, $weightInfo);
			break;

		case 'weight.save':
			$data = (object) post('title');
			if ($data) {
				// Check duplicate
				list($term, $period) = explode(':', $data->termperiod);
				if (!$tranId) {
					$isDuplicate = mydb::select(
						'SELECT * FROM %project_tr%
						WHERE `orgId` = :orgId AND `formid` = "weight" AND `detail1` = :year AND `detail2` = :term AND `period` = :period
						LIMIT 1',
						[
							':orgId' => $orgId,
							':year' => $data->year,
							':term' => $term,
							':period' => $period,
						]
					)->trid;
					// debugMsg($isDuplicate, '$isDuplicate');
					if ($isDuplicate) return new ErrorMessage(['code' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'ข้อมูลสถานการณภาวะโภชนาการ<br />ปีการศึกษา '.$data->year.' ภาคการศึกษา '.$term.'/'.$period.'<br />เคยการบันทึกไว้ก่อนแล้ว ไม่สามารถบันทึกซ้ำได้']);
				}

				$data->trid = $tranId;
				$data->orgid = $orgId;
				$data->weight = post('weight');
				$data->height = post('height');
				R::Model('project.weight.save', $data, '{debug: false}');

				// $ret.=print_o($post,'$post');
				// $ret.=print_o($qt,'$qt');
			}
			// debugMsg($data,'$data');
			break;

		default:
			/*
			// Bug on action/action/action
			$funcName = array();
			foreach (array_slice(func_get_args(),2) as $value) {
				if (is_numeric($value)) break;
				else if (is_string($value)) {
					$funcName[] = $value;
				}
			}
			$argIndex = count($funcName)+2; // Start argument
			*/

			$argIndex = 3; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
								'project.knet.'.$action,
								$self,
								$orgInfo,
								func_get_arg($argIndex),
								func_get_arg($argIndex+1),
								func_get_arg($argIndex+2),
								func_get_arg($argIndex+3),
								func_get_arg($argIndex+4)
							);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

			//$ret .= R::Page('project.'.$action, $self, $tpid);
			//$ret .= print_o($projectInfo,'$projectInfo');
			//$ret .= message('error', 'Action incorrect');
			break;
	}
	//$ret .= print_o($projectInfo, '$projectInfo');

	return $ret;
}
?>