<?php
/**
* Project :: Information Controller/Model
*
* @param Object $self
* @param Int $projectId
* @param String $action
* @param Int $tranId
* @return String
*/
function project_info($self, $projectId = NULL, $action = NULL, $tranId = NULL) {
	$projectInfo = is_object($projectId) ? $projectId : R::Model('project.get',$projectId, '{initTemplate: true}');
	$projectId = $projectInfo->projectId;

	if (!$action) $ret = R::Page('project.view',$self, $projectInfo);

	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;
	$isOwner = $projectInfo->RIGHT & _IS_OWNER;
	$isMember = $isAdmin || $projectInfo->info->membershipType;
	$isAddAction = $isEdit || $projectInfo->info->membershipType;
	$isOfficer = $isAdmin
		|| in_array($projectInfo->info->membershipType, array('ADMIN','OFFICER'))
		|| in_array($projectInfo->orgMemberShipType, array('ADMIN','OFFICER'));
	$isEdit = $isMember;


	// EJECT If user not member of project
	if (!$projectId) return 'ERROR : NO PROJECT';
	else if (!$isMember) return 'ERROR: ACCESS DENIED';

	$tagname = 'info';


	$ret = '';
	//$ret .= 'Action = '.$action. ' Is edit = '.($isEdit ? 'YES' : 'NO').'<br />';
	// $ret .= print_o($projectInfo, '$projectInfo');

	switch ($action) {

		case 'status':
			if ($isAdmin && $tranId) {
				mydb::query('UPDATE %topic% SET `status` = :status WHERE `tpid` = :tpid LIMIT 1',':tpid',$projectId, ':status', _LOCK);

				if ($tranId == 'stop') {
					mydb::query('UPDATE %project% SET `project_status` = "ยุติโครงการ" WHERE `tpid` = :tpid LIMIT 1', ':tpid', $projectId);
					$ret .= 'ได้ดำเนินการ <b>ยุติโครงการ</b>เรียบร้อยแล้ว';
				} else if ($tranId == 'suspend') {
					mydb::query('UPDATE %project% SET `project_status` = "ระงับโครงการ" WHERE `tpid` = :tpid LIMIT 1', ':tpid', $projectId);
					$ret .= 'ได้ดำเนินการ <b>ระงับโครงการ</b>เรียบร้อยแล้ว';
				} else if ($tranId == 'close') {
					mydb::query('UPDATE %project% SET `project_status` = "ดำเนินการเสร็จสิ้น" WHERE `tpid` = :tpid LIMIT 1', ':tpid', $projectId);
					$ret .= 'ได้ดำเนินการ <b>ระงับโครงการ</b>เรียบร้อยแล้ว';
				} else if ($tranId == 'open') {
					mydb::query('UPDATE %project% SET `project_status` = "กำลังดำเนินโครงการ" WHERE `tpid` = :tpid LIMIT 1', ':tpid', $projectId);
					$ret .= 'ได้ดำเนินการ <b>กำลังดำเนินโครงการ</b>เรียบร้อยแล้ว';
				}
			}

			break;

		case 'period.add' :
			$lastPeriod = mydb::select('SELECT MAX(`period`) `lastPeriod` FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "info" AND `part` = "period" LIMIT 1',':tpid',$projectId)->lastPeriod;

			if ($lastPeriod < cfg('project.period.max')) {
				$stmt = 'INSERT INTO %project_tr%
					(`tpid`, `uid`, `formid`, `part`, `period`, `created`)
					VALUES
					(:tpid, :uid, :formid, :part, :period, :created)';

				mydb::query($stmt,':tpid',$projectId, ':uid', i()->uid, ':formid', 'info', ':part', 'period', ':period', $lastPeriod+1, ':created',date('U'));

				$ret .= 'Add completed';
			} else $ret .= 'จำนวนงวดสูงสุดแล้ว';
			break;

		case 'period.remove' :
			mydb::query('DELETE FROM %project_tr% WHERE `trid` = :trid LIMIT 1',':trid',$tranId);
			$ret .= 'Remove complete';
			break;

		case 'docs.upload':
			//$ret .= 'Upload Docs';
			$data = (object)post('document');
			$data->tpid = $projectId;
			if ($data->title == 'ไฟล์เอกสาร') $data->title = $_FILES['document']['name'];
			$docFiles = $_FILES['document'];
			$result = R::Model('doc.upload',$docFiles, $data);

			if ($result->error) header('HTTP/1.0 406 Not Acceptable');
			$ret .= $result->error ? implode(',', $result->error) : 'Upload Completed';

			//$ret .= print_o($result,'$result');
			//$ret .= print_o(post(),'post()');
			//$ret .= print_o($_FILES,'$_FILES');
			break;

		case 'docs.delete':
			if ($tranId && SG\confirm()) R::Model('doc.delete', $tranId);
			break;

		case 'problem.save' :
			$data=new stdClass();
			$data->tpid=$projectId;
			$data->uid=i()->uid;
			$data->formid='info';
			$data->part='problem';
			$data->created=date('U');
			$data->problemother=post('problemother');
			$data->problemdetail=post('problemdetail');
			$data->problemsize=post('problemsize');
			$data->problemref=post('problemref');

			// Save other problem
			if ($data->problemother) {
				$stmt='INSERT INTO %project_tr%
							(`tpid`, `uid`, `formid`, `part`, `detail1`, `text1`, `num1`, `created`)
							VALUES
							(:tpid, :uid, :formid, :part, :problemother, :problemdetail, :problemsize, :created)';
				mydb::query($stmt,$data);
				$data->problemId = mydb()->insert_id;
				//$ret.=mydb()->_query.'<br />';
			}

			// Get problem detail from list
			if ($data->problemref) {
				list($a,$b,$c,$refid) = explode(':', post('problemref'));
				$data->refid = $refid;
				$data->tagname = $a.':'.$b.':'.$c;
				$stmt = 'SELECT * FROM %tag% WHERE `taggroup` = :taggroup AND `catid` = :catid LIMIT 1';
				$problemRs = mydb::select($stmt,':taggroup',$data->tagname, ':catid',$data->refid);
				$problemDetail = json_decode($problemRs->description);
				$data->problemother = $problemRs->name;
				//$ret.=print_o($problemRs,'$problemRs');
			}

			// Save problem from list
			if ($data->refid) {
				$stmt = 'INSERT INTO %project_tr%
							(`tpid`, `uid`, `refid`, `tagname`, `formid`, `part`, `detail1`, `num1`, `created`)
							VALUES
							(:tpid, :uid, :refid, :tagname, :formid, :part, :problemother, :problemsize, :created)';
				mydb::query($stmt,$data);
				$data->problemId = mydb()->insert_id;
				//$ret.=mydb()->_query.'<br />';


				// Create project objective refer to problem
				$dataObjective=new stdClass();
				$dataObjective->tpid=$projectId;
				$dataObjective->refid = $data->refid;
				$dataObjective->tagname = $data->tagname;
				$dataObjective->uid=i()->uid;
				$dataObjective->formid='info';
				$dataObjective->part='objective';
				$dataObjective->created=date('U');
				$dataObjective->objective=$problemDetail->objective;
				$dataObjective->indicator=str_replace('<br />',"\n",$problemDetail->indicator);
				$dataObjective->targetsize=NULL;
				$dataObjective->problemId = $data->problemId;


				$stmt='INSERT INTO %project_tr%
							(`tpid`, `parent`, `uid`, `refid`, `refcode`, `tagname`, `formid`, `part`, `text1`, `text2`, `num2`, `created`)
							VALUES
							(:tpid, 1, :uid, :refid, :problemId, :tagname, :formid, :part, :objective, :indicator, :targetsize, :created)';
				mydb::query($stmt,$dataObjective);
				//$ret.=mydb()->_query.'<br />';
			}
			//$projectInfo = R::Model('project.get',$projectId);
			//$ret.=print_o($data,'$data');
			break;

		case 'problem.detail':
			//รายละเอียดปัญหา
			$rs=$projectInfo->problem[$tranId];
			$ret.=view::inlineedit(array('group'=>'tr:info:problem', 'fld'=>'text1', 'tr'=>$rs->trid, 'class'=>'-fill', 'ret'=>'html', 'placeholder'=>'ระบุรายละเอียดปัญหา'),$rs->detailproblem,$isEdit,'textarea');
			return $ret;
			break;

		case 'problem.remove' :
			if ($projectId && $tranId && SG\confirm()) {
				mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `trid`=:trid AND `formid`="info" AND `part`="problem" LIMIT 1',':tpid',$projectId, ':trid',$tranId);
			}
			break;

		case 'objective.save' :
			$data = new stdClass();
			$data->tpid = $projectId;
			$data->uid = i()->uid;
			$data->formid = 'info';
			$data->part = 'objective';
			$data->created = date('U');
			$data->objective = post('objective');
			$data->problemId = post('problemId');
			$data->indicator = post('indicator');
			$data->targetsize = post('targetsize');

			if ($data->objective) {
				$stmt='INSERT INTO %project_tr%
							(`tpid`, `parent`, `uid`, `formid`, `part`, `refcode`, `text1`, `text2`, `num2`, `created`)
							VALUES
							(:tpid, 1, :uid, :formid, :part, :problemId, :objective, :indicator, :targetsize, :created)';
				mydb::query($stmt,$data);
				//$ret.=mydb()->_query;
			}

			/*
			if (post('problemref')) {
				list($a,$b,$c,$refid)=explode(':', post('problemref'));
				$data->refid=$refid;
				$data->tagname=$a.':'.$b.':'.$c;
				$stmt='SELECT * FROM %tag% WHERE `taggroup`=:taggroup AND `catid`=:catid LIMIT 1';
				$problemRs=mydb::select($stmt,':taggroup',$data->tagname, ':catid',$data->refid);
				$detail=json_decode($problemRs->description);
				$data->objective=$detail->objective;
				$data->indicator=str_replace('<br />',"\n",$detail->indicator);
				//$ret.=print_o($problemRs);
			}

			if ($data->refid) {
				$stmt='INSERT INTO %project_tr%
							(`tpid`, `parent`, `uid`, `refid`, `tagname`, `formid`, `part`, `text1`, `text2`, `num2`, `created`)
							VALUES
							(:tpid, 1, :uid, :refid, :tagname, :formid, :part, :objective, :indicator, :targetsize, :created)';
				mydb::query($stmt,$data);
				//$ret.=mydb()->_query;
			}
			*/
			//$ret.=print_o($data,'$data');
			//$ret.=print_o(post(),'post()');
			//location('paper/'.$projectId);
			break;

		case 'objective.remove' :
			if ($projectId && $tranId && SG\confirm()) {
				mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `trid`=:trid AND `formid`="info" AND `part`="objective" LIMIT 1',':tpid',$projectId, ':trid',$tranId);
			}
			break;

		case 'area.delete':
			$ret .= 'DELETE';
			if ($isEdit && $tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %project_prov% WHERE `tpid` = :tpid AND `autoid` = :trid AND `tagname` = :tagname LIMIT 1';
				mydb::query($stmt, ':tpid', $projectId, ':trid', $tranId, ':tagname', _PROJECT_TAGNAME);
				$ret .= ' COMPLETED';
				//$ret .= mydb()->_query;
			} else {
				$ret .= ' ERROR';
			}
			break;

		case 'area.save':
			if ($isEdit && post('changwat') != '') {
				$data = (object) post();
				$data->tagname = _PROJECT_TAGNAME;
				$stmt = 'INSERT INTO %project_prov%
							(`tpid`,`tagname`,`changwat`,`ampur`,`tambon`,`areatype`)
							VALUES
							(:tpid,:tagname,:changwat,:ampur,:tambon,:areatype)';
				mydb::query($stmt,':tpid',$projectId,$data);
				//$ret .= mydb()->_query;
			}
			break;

		case 'tran.add':
			if ($tranId) {
				list($formid,$part) = explode(',',$tranId);
				$data = (Object) post('tran');
				$data->tpid = $projectId;
				$data->uid = i()->uid;
				$data->formid = SG\getFirst($formid, $data->formid);
				$data->part = SG\getFirst($part, $data->part);
				$data->created = date('U');
				$data->text1 = SG\getFirst($data->text1);
				$data->flag = SG\getFirst($data->flag);
				$data->rate1 = SG\getFirst($data->rate1);
				$data->parent = SG\getFirst($data->parent);

				$stmt = 'INSERT INTO %project_tr%
					(`tpid`, `uid`, `formid`, `part`, `parent`, `flag`, `rate1`, `text1`, `created`)
					VALUES
					(:tpid, :uid, :formid, :part, :parent, :flag, :rate1, :text1, :created)';

				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;
				//$ret .= print_o($data, '$data');
				//$ret .= print_o(post(), 'post()');
			}
			break;

		case 'tran.remove':
			if ($projectId && $tranId && SG\confirm()) {
				mydb::query('DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `trid` = :trid LIMIT 1',':tpid',$projectId, ':trid',$tranId);
				//$ret .= mydb()->_query;
			}
			break;

		case 'activity.remove' :
			$calendar = R::Model('project.calendar.get', array('activityId' => $tranId));
			if (($isEdit || $calendar->owner == i()->uid) && empty($calendar->actionId) && $calendar->calid && SG\confirm()) {
				$calid = $calendar->calid;

				$calendarTitle=mydb::select('SELECT `title` FROM %calendar% WHERE `id`=:id LIMIT 1',':id',$calid)->title;
				//$ret .= 'Remove calendar '.$calendarTitle;

				mydb::query('DELETE FROM %calendar% WHERE `id`=:calid LIMIT 1',':calid',$calid);
				//$ret .= mydb()->_query.'<br />';

				mydb::query('DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `calid` = :calid AND `formid` = "info" AND `part` = "activity" LIMIT 1', ':tpid',$projectId, ':calid', $calid);
				//$ret .= mydb()->_query.'<br />';

				mydb::query('DELETE FROM %project_activity% WHERE `calid`=:calid LIMIT 1',':calid',$calid);
				//$ret .= mydb()->_query.'<br />';

				if (mydb::table_exists('%project_actguide%')) {
					mydb::query('DELETE FROM %project_actguide% WHERE `calid`=:calid',':calid',$calid);
					//$ret .= mydb()->_query.'<br />';
				}
				// Add log
				model::watch_log('project','Calendar remove','ลบกิจกรรมย่อย '.$calid.' กิจกรรมหลัก '.$calid.' : ' .$calendarTitle,NULL,$projectId);
			}
			//$ret .= print_o($calendar, '$calendar');
			break;

		case 'action.post':
			$options = new stdClass;
			$options->moneyform = 'row';
			$options->ret = SG\getFirst(post('ret'));
			//,url('project/my/action/'.$projectId));

			if (post('calid')) {
				$calid = post('calid');
				$calendar = R::Model('project.calendar.get', $calid);
				// debugMsg('$calid = '.$calid);
				// debugMsg($calendar,'$calendar');

				if (empty($calendar)) {
					// No Activity
					$ret .= message('error','ไม่มีกิจกรรมตามที่ระบุ');
				} else if ($calendar->actionId) {
					// Already Action report
					$data = R::Model('project.action.get', ['projectId' => $projectId, 'actionId' => $calendar->actionId]);
					// debugMsg($data,'$data');
					$options->ret = SG\getFirst(post('ret'), url('project/'.$projectId.'/action.view/'.$data->actionId));
					$ret .= R::View('project.action.form',$projectInfo, $data->activityId, $data, $options);
				} else {
					// Activity no Action
					$data = (Object) [
						'calid' => $calendar->calid,
						'activityId' => $calendar->activityId,
						'part' => 'owner',
						'trid' => $calendar->actionId,
						'title' => $calendar->title,
						'actionDate' => $calendar->from_date,
						'budget' => $calendar->budget,
					];
					$ret .= R::View('project.action.form', $projectInfo, $data->activityId, $data, $options);
					// debugMsg($data,'$data');
				}
			} else if ($tranId) {
				$data = R::Model('project.action.get', ['projectId' => $projectId, 'actionId' => $tranId], '{debug: false}');
				if ($isEdit || $data->uid == i()->uid) {
					$ret .= R::View('project.action.form', $projectInfo, $tranId, $data, $options);
				} else {
					$ret .= message('error', 'access denied');
				}
				//$ret .= print_o($data,'$data');
			} else if (empty($tranId)) {
				$ret .= R::View('project.action.form',$projectInfo, NULL, NULL, $options);
			}
			//$ret.=print_o($calendar,'$calendar');
			break;

		case 'action.save':
			if (post('action')) {
				$data = (Object) post('action');

				$data->tpid = $projectId;

				// Create Activity on empty activityId
				$data->newcalendar = empty($data->calid);

				$result = R::Model('project.action.save', $projectInfo, $data, '{debug: false}');
				//$ret.=print_o($result,'$result');
				//$ret.=print_o($data,'$data');

				if (empty($result->actionId)) {
					$ret .= message('error','บันทึกข้อมูลผิดพลาด : '.$result->_error);
				} else {
					if ($tranId) {
						$ret .= 'บันทึกข้อมูลเรียบร้อย';
					} else {
						$ret .= R::PageWidget('project.action.view', [$projectInfo, $result->actionId])->build();
					}
				}
			}
			//$ret .= print_o(post(),'post()').print_o($result,'$result');
			break;

		case 'action.remove':
			if ($tranId && SG\confirm()) {
				$ret .= 'ลบบันทึกกิจกรรม';
				$options = new stdClass();
				if (post('removeActivity')) {
					$options->removeActivity = true;
				}
				$result = R::Model('project.action.remove',$tranId, $options);
				//$ret .= print_o($result, '$result');
			}
			break;

		case 'photo.upload':
			$post = (Object) post();
			$data->tpid = $projectId;
			$data->prename = 'project_'.$projectId.($post->tagname ? '_'.$post->tagname : '').'_';
			$data->tagname = 'project'.($post->tagname ? ','.$post->tagname : '');
			$data->title = $post->title;
			$data->orgid = $projectInfo->orgid;
			$data->refid = $tranId;
			$data->cid = SG\getFirst($post->cid);
			$data->deleteurl = $post->delete == 'none' ? NULL : 'project/'.$projectId.'/info/photo.delete/';
			$data->link = $post->link;
			$uploadResult = R::Model('photo.upload', $_FILES['photo'], $data);

			if($uploadResult->error) {
				$ret = implode(' ', $uploadResult->error);
			} else {
				$ret = $uploadResult->link;
				$firebaseCfg = cfg('firebase');
				$firebaseFolder = SG\getFirst($firebaseCfg['update'], 'update');
				$firebase = new Firebase('sg-project-man', $firebaseFolder);
				$firebaseData = array(
					'projectId' => $projectId,
					'actionId' => $tranId,
					'changed' => 'update',
					'time' => array('.sv' => 'timestamp'),
				);
				$firebase->set($tranId, $firebaseData);
			}

			//$ret .= print_o($data,'$data');
			//$ret .= print_o($uploadResult,'$uploadResult');
			break;

		case 'photo.delete':
			if ($tranId && SG\confirm()) {
				$result = R::Model('photo.delete',$tranId);
				$ret .= 'Photo Deleted!!!';
			}
			break;

		case 'vdo.save':
			$data = (Object) post('tran');
			if ($data->link) {
				$data->tpid = $projectId;
				$data->type = 'movie';
				$data->uid = i()->uid;
				$data->refid = SG\getFirst($tranId);
				$data->tag = SG\getFirst($data->tag);
				$data->file = $data->link;
				$data->title = SG\getFirst($data->title);
				$data->timestamp = date('Y-m-d H:i:s');
				$data->ip = ip2long(GetEnv('REMOTE_ADDR'));

				$stmt = 'INSERT INTO %topic_files% (`tpid`, `type`, `uid`, `refid`, `tagname`, `file`, `title`, `timestamp`, `ip`) VALUES (:tpid, :type, :uid, :refid, :tag, :file, :title, :timestamp, :ip)';
				mydb::query($stmt, $data);
				//$ret .= 'Upload '.mydb()->_query.print_o($data,'$data');
			}
			break;

		case 'vdo.delete':
			if (SG\confirm() && $tranId) {
				$stmt = 'DELETE FROM %topic_files% WHERE `tpid` = :tpid AND `fid` = :fid LIMIT 1';
				mydb::query($stmt, ':tpid', $projectId, ':fid', $tranId);
				//$ret .= mydb()->_query;
			}
			break;

		case 'target.delete':
			if (post('tid') && SG\confirm()) {
				$stmt = 'DELETE FROM %project_target% WHERE `tpid` = :tpid AND `tagname` = :tagname AND `tgtid` = :tranId LIMIT 1';
				mydb::query($stmt, ':tpid',$projectId, ':tagname',$tagname, ':tranId',post('tid'));
				//$ret .= mydb()->_query;
			}
			break;

		case 'target.add':
			if (post('targetname')) {
				$data = (object) post();
				$data->tpid = $projectId;
				$data->tagname = SG\getFirst($data->tagname, $tagname);
				$data->tgtid = SG\getFirst($data->tgtid, $data->targetname, '');
				$data->trid = SG\getFirst($trid, 0);
				$data->amount = SG\getFirst($data->amount, 0);
				$data->currentind1 = SG\getFirst($data->currentind1,NULL);
				$data->currentind2 = SG\getFirst($data->currentind2,NULL);
				$data->currentind3 = SG\getFirst($data->currentind3,NULL);
				$data->expectind1 = SG\getFirst($data->expectind1,NULL);
				$data->expectind2 = SG\getFirst($data->expectind2,NULL);
				$data->expectind3 = SG\getFirst($data->expectind3,NULL);
				$stmt = 'INSERT INTO %project_target%
					(
					  `tpid`, `trid`, `tagname`, `tgtid`
					, `amount`
					, `currentind1`, `currentind2`, `currentind3`
					, `expectind1`, `expectind2`, `expectind3`
					)
					VALUES
					(
					  :tpid, :trid, :tagname, :tgtid
					, :amount
					, :currentind1, :currentind2, :currentind3
					, :expectind1, :expectind2, :expectind3
					)
					ON DUPLICATE KEY UPDATE
					  `amount` = :amount
					, `currentind1` = :currentind1
					, `currentind2` = :currentind2
					, `currentind3` = :currentind3
					, `expectind1` = :expectind1
					, `expectind2` = :expectind2
					, `expectind3` = :expectind3
					';
				mydb::query($stmt, $data);
				//$ret .= print_o($data, '$data');
				//$ret.=mydb()->_query;

				/*
				$projectInfo = R::Model('project.get', $projectId);

				if ($data->tagname=='project:mainact') {
					$ret.=R::View('project.plan.target.view',$projectInfo,$trid);
				} else {
					$ret.=R::View('project.target.view',$projectInfo,$trid);
				}
				*/
			}
			break;

		case 'join.create':
			if ($isEdit && $tranId) {
				if (empty($projectInfo->orgid)) {
					return message('error', 'ไม่สามารถสร้างบันทึกผู้เข้าร่วมกิจกรรมได้ เนื่องจากโครงการนี้ไม่ได้สังกัดภายใต้องค์กรใด ๆ');
				}

				$calRs = mydb::select('SELECT * FROM %calendar% WHERE `id` = :calid LIMIT 1',':calid',$tranId);

				$doing->orgid = $projectInfo->orgid;
				$doing->tpid = $projectId;
				$doing->calid = $tranId;
				$doing->uid = i()->uid;
				$doing->doings = $calRs->title;
				$doing->place = $calRs->location;
				$doing->atdate = sg_date($calRs->from_date,'U');
				$doing->fromtime = $calRs->from_time;
				$stmt = 'INSERT INTO %org_doings% (`orgid`, `tpid`, `calid`, `uid`, `doings`, `place`, `atdate`, `fromtime`) VALUES (:orgid, :tpid, :calid, :uid, :doings, :place, :atdate, :fromtime)';
				mydb::query($stmt,$doing);
				//$ret .= mydb()->_query;
				$ret .= 'สร้างบันทึกผู้เข้าร่วมเรียบร้อย';
			} else {
				$ret .= 'มีข้อผิดพลาดในการสร้างบันทึกผู้เข้าร่วม';
			}
			break;

		case 'tag.save':
			$getTag = post('tagname', _TRIM);
			if ($getTag) {
				$data = new stdClass;
				$data->keyname = 'project.info';
				$data->bigid = post('tagid');
				$data->keyid = $projectId;
				$data->fldname = 'tag';
				$data->created = date('U');
				$data->ucreated = i()->uid;

				foreach (explode(',',$getTag) as $tagname) {
					$tagname = trim($tagname);
					if (empty($tagname)) continue;
					$data->flddata = $tagname;
					$stmt = 'INSERT INTO %bigdata% (`bigid`, `keyname`, `keyid`, `fldname`, `flddata`, `created`, `ucreated`) VALUES (:bigid, :keyname, :keyid, :fldname, :flddata, :created, :ucreated)';
					mydb::query($stmt, $data);
					//$ret .= mydb()->_query.'<br />';
				}
			}
			break;

		case 'tag.remove':
			if ($tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %bigdata% WHERE `bigid` = :bigid AND `keyid` = :keyid AND `keyname` = "project.info" AND `fldname` = "tag" LIMIT 1';
				mydb::query($stmt, ':bigid', $tranId, ':keyid', $projectId);
				//$ret .= mydb()->_query;
			}
			break;

		case 'expense.save':
			$post = (Object) post('data');
			if ($projectId && $tranId && $post->refid && $post->amt) {
				$post->trid = SG\getFirst($post->trid);
				$post->tpid = $projectId;
				$post->calid = $post->calid;
				$post->amt = sg_strip_money($post->amt);
				$post->tax = sg_strip_money($post->tax);
				$post->uid = i()->uid;
				$post->refdate = sg_date($post->refdate,'Y-m-d');
				$post->formid = 'expense';
				$post->part = 'exptr';
				$post->created = date('U');

				/*
				- calid = Calendar ID
				- num1 = จำนวนเงิน
				- num2 = ภาษีหัก ณ ที่จ่าย
				- refid = หมายเลขรหัสค่าใช้จ่าย
				- detail1 = รายละเอียดค่าใช้จ่าย
				- detail2 = เลขที่เอกสารอ้างอิง
				- date1 = วันที่เอกสารอ้างอิง
				- text1 = หมายเหตุ
				*/
				$stmt = 'INSERT INTO %project_tr%
					(`trid`, `tpid`, `calid`, `refid`, `formid`, `part`, `uid`
					, `num1`, `num2`
					, `detail1`, `detail2`, `date1`, `text1`
					, `created`)
					VALUES
					(:trid, :tpid, :calid, :refid, :formid, :part, :uid
					, :amt, :tax
					, :description, :refno, :refdate, :remark
					, :created)
					ON DUPLICATE KEY
					UPDATE
					  `refid` = :refid
					, `num1` = :amt
					, `num2` = :tax
					, `detail1` = :description
					, `detail2` = :refno
					, `date1` = :refdate
					, `text1` = :remark
					';

				mydb::query($stmt,$post);

				if (cfg('project')->follow->calculateExpense) {
					R::Model('project.expense.calculate', $projectId, $tranId);
				}

				//$ret.=mydb()->_query.'<br />';
				//$ret.=print_o($post,'$post');
			}
			break;

		case 'expense.remove':
			if ($projectId && $tranId && SG\confirm()) {
				$ret .= 'Expense Removed.';
				$stmt = 'SELECT * FROM %project_tr% e LEFT JOIN %project_tr% a ON a.`tpid` = :tpid AND a.`calid` = e.`calid` AND a.`formid` = "activity" AND a.`part` = "owner" WHERE e.`tpid` = :tpid AND e.`trid` = :tranId LIMIT 1';
				$action = mydb::select($stmt, ':tpid', $projectId, ':tranId', $tranId);

				$stmt = 'DELETE FROM %project_tr%
					WHERE `trid` = :trid AND `tpid` = :tpid AND `formid` = "expense" AND `part` = "exptr"
					LIMIT 1';
				mydb::query($stmt, ':tpid',$projectId,':trid',$tranId);

				//$ret .= mydb()->_query;

				if (cfg('project')->follow->calculateExpense && $action->trid) {
					R::Model('project.expense.calculate', $projectId, $action->trid, '{debug: false}');
				}
			}
			break;

		case 'expense.calculate':
			R::Model('project.expense.calculate', $projectId, $tranId);
			break;

		case 'expense.photo.upload':
			$data = (Object) [
				'tpid' => $projectId,
				'prename' => 'project_rcv_'.$projectId.'_',
				'tagname' => 'project,rcv',
				'title' => 'เอกสารการเงิน ',
				'orgid' => $projectInfo->orgid,
				'refid' => $tranId,
				'deleteurl' => 'project/'.$projectId.'/info/photo.delete/',
			];
			$uploadResult = R::Model('photo.upload', $_FILES['photo'], $data);

			$ret = $uploadResult->link;

			//$ret .= print_o($data,'$data');
			//$ret .= print_o($uploadResult,'$uploadResult');
			//$ret .= print_o($_FILES,'$FILES');
			//$ret .= print_o($paidDocs,'$paidDocs');
			break;

		case 'tor.create':
			if (empty($torInfo)) {
				$result = R::Model('project.tor.create', $projectId);
			}
			location('project/'.$projectId.'/info.tor');
			break;

		case 'tor.remove':
			if (SG\confirm()) {
				$result = R::Model('project.tor.remove', $projectId);
				$ret = 'ลบข้อตกลงเรียบร้อย';
				//$ret .= print_o($result, '$result');
			}
			break;

		case 'link.save':
			$getLinkUrl = post('link');
			$getProjectId = post('tpid');
			//$linkId = mydb::select('SELECT `bigid` FROM %bigdata% WHERE `keyname` = "project.info" AND `keyid` = :tpid AND `fldname` = "link" LIMIT 1', ':tpid', $projectId)->bigid;
			if ($getLinkUrl || $getProjectId) {
				$getProject = $getLinkUrl ? SG\getAPI($getLinkUrl) : Array();
				//$ret .= print_o($getProject, '$getProject');

				$data = new stdClass();
				$fldData = new stdClass();

				$data->keyid = $projectId;
				$data->fldref = NULL;

				$fldData->title = '';
				if ($getProjectId) {
					$data->fldref = $getProjectId;
					$fldData->url = url('project/'.$getProjectId);
					$fldData->title = mydb::select('SELECT `title` FROM %topic% WHERE `tpid` = :tpid LIMIT 1', ':tpid', $getProjectId)->title;
				} else {
					$fldData->url = $getLinkUrl;
					if (preg_match('/<title>(.*)<\/title>/siU',$getProject['result'], $out)) {
						$fldData->title = trim(strip_tags($out[1]));
					} else {
						$fldData->title = 'NO TITLE';
					}
				}

				$data->created = date('U');
				$data->uid = i()->uid;


				$data->flddata = SG\json_encode($fldData, JSON_PRETTY_PRINT);

				$stmt = 'INSERT INTO %bigdata%
					(`keyname`, `keyid`, `fldname`, `fldtype`, `fldref`, `flddata`, `created`, `ucreated`)
					VALUES
					("project.info", :keyid, "link", "json", :fldref, :flddata, :created, :uid)';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;
			}
			break;

		case 'link.remove':
			if ($tranId AND SG\confirm()) {
				// Remove external link
				$stmt = 'DELETE FROM %bigdata% WHERE `bigid` = :bigid AND `keyname` = "project.info" AND `keyid` = :tpid AND `fldname` = "link" LIMIT 1';
				mydb::query($stmt, ':bigid', $tranId, ':tpid', $projectId);
			}
			break;

		case 'hia.indicator.delete':
			if ($tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "eval-hia" AND `part` IN ("indicator", "tool", "stakeholder") AND `parent` = :tranId';
				mydb::query($stmt, ':tpid',$projectId, ':tranId', $tranId);

				$stmt = 'DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "eval-hia" AND `trid` = :tranId';
				mydb::query($stmt, ':tpid',$projectId, ':tranId', $tranId);
			}
			break;

		case 'qt.save':
			$ret .= 'บันทึกเรียบร้อย';
			$post = (Object) post();
			$data = (Object) post('data');

			$mastData->qtref = SG\getFirst($post->refid);
			$mastData->uid = i()->uid;
			$mastData->qtdate = SG\getFirst($post->qtdate, date('Y-m-d'));
			$mastData->qtgroup = $post->group;
			$mastData->qtform = $post->formid;
			$mastData->tpid = $projectId;
			$mastData->orgid = SG\getFirst($post->org);
			$mastData->seq = $seqId;
			//$mastData->value = $post->value;
			//$mastData->data = $data;
			$mastData->collectname = i()->name;
			$mastData->created = date('U');

			$mastResult = R::Model('qt.save', $mastData);

			//$ret .= print_o($mastResult, '$mastResult');

			foreach ($data->data as $key => $value) {
				$tranData = new stdClass();
				$tranData->qtref = $mastData->qtref;
				$tranData->part = $key;
				$tranData->value = $value;
				$tranResult = R::Model('qt.tran.save', $tranData);
				//$ret .= print_o($tranResult, '$tranResult');
			}


			//$ret .= print_o($mastData,'$mastData');
			//$ret .= print_o($tranData,'$tranData');
			//$ret .= print_o($post,'$post');
			//$ret .= print_o($data,'$data');
			break;

		case 'qt.remove':
			if (($qtRef = $tranId) && SG\confirm()) {
				$stmt = 'DELETE FROM %qtmast% WHERE `qtref` = :qtRef AND `tpid` = :projectId LIMIT 1';
				mydb::query($stmt, ':qtRef', $qtRef, ':projectId', $projectId);
				//$ret .= mydb()->_query;

				$stmt = 'DELETE FROM %qttran% WHERE `qtref` = :qtRef';
				mydb::query($stmt, ':qtRef', $qtRef);
				//$ret .= mydb()->_query;
			}
			break;

		case 'assign.save':
			$data = (Object) post('data');
			$data->trid = SG\getFirst($data->trid);
			$data->formid = 'info';
			$data->part = 'assign';
			$data->date1 = $data->assignMonth.'-01';
			$data->train = SG\json_encode((Object) $data->train);
			$data->uid = $data->modifyby = i()->uid;
			$data->created = $data->modified = date('U');
			$stmt = 'INSERT INTO %project_tr%
				(`trid`, `tpid`, `uid`, `formid`, `part`, `date1`, `text1`, `text2`, `text3`, `text4`, `text5`, `text6`, `text10`, `created`)
				VALUES
				(:trid, :projectId, :uid, :formid, :part, :date1, :dataAnalytics, :covid19, :digitalizing, :otop, :job, :other, :train, :created)
				ON DUPLICATE KEY UPDATE
				  `text1` = :dataAnalytics
				, `text2` = :covid19
				, `text3` = :digitalizing
				, `text4` = :otop
				, `text5` = :job
				, `text6` = :other
				, `text10` = :train
				, `modified` = :modified
				, `modifyby` = :modifyby
				';
			mydb::query($stmt, $data);
			//$ret .= print_o($data, '$data');
			//$ret .= mydb()->_query;
			break;

		case 'send.save':
			$ret .= 'บันทึกเรียบร้อย';
			$data = (Object) post('data');
			$data->tpid = $projectId;
			$data->trid = SG\getFirst($data->trid);
			$data->formid = 'info';
			$data->part = 'send';
			$data->uid = $data->modifyby = i()->uid;
			$data->created = $data->modified = date('U');

			/*
			$stmt = 'INSERT INTO %project_tr%
				(`trid`, `tpid`, `uid`, `formid`, `part`, `date1`, `date2`, `num1`, `num2`, `detail1`, `text1`, `text2`, `text3`, `created`)
				VALUES
				(:trid, :tpid, :uid, :formid, :part, :dateFrom, :dateEnd, :dateCount, :actionCount, :actionList, :train, :learn, :nextPlan, :created)
				ON DUPLICATE KEY UPDATE
				  `num1` = :dateCount
				, `num2` = :actionCount
				, `detail1` = :actionList
				, `text1` = :train
				, `text2` = :learn
				, `text3` = :nextPlan
				, `modified` = :modified
				, `modifyby` = :modifyby
				';
			*/
			$periodInfo = R::Model('project.period.get', $projectId, $tranId);
			$data->projectId = $projectId;
			$data->trid = $periodInfo->trid;
			$data->ownerTraining = SG\getFirst($data->ownerTraining);
			$data->ownerLearning = SG\getFirst($data->ownerLearning);
			$data->ownerNextPlan = SG\getFirst($data->ownerNextPlan);
			if ($periodInfo) {
				$stmt = 'UPDATE %project_tr% SET
					`text6` = :ownerTraining
					, `text7` = :ownerLearning
					, `text8` = :ownerNextPlan
					WHERE `tpid` = :projectId AND `trid` = :trid
					LIMIT 1
				';
				mydb::query($stmt, $data);
			}
			//$ret .= print_o($data, '$data');
			//$ret .= debugMsg($periodInfo, '$periodInfo');
			//$ret .= mydb()->_query;
			break;

		case 'send.checked':
			if (SG\confirm()) {
				$period = $tranId;
				$childId = post('child');
				$periodInfo = R::Model('project.period.get', $childId, $period);

				$data = new stdClass();
				$data->projectId = $childId;
				$data->period = $period;
				$data->flag = post('remove') ? _PROJECT_PERIOD_FLAG_SEND : _PROJECT_PERIOD_FLAG_MANAGER;
				$data->money = post('remove') ? 'func.`num2`' : sg_strip_money(post('money'));
				$dataJson = json_decode($periodInfo->data);
				$dataJson->checked[] = array('user' => i()->uid, 'date' => date('Y-m-d'));
				$data->json = json_encode($dataJson);

				$stmt = 'UPDATE %project_tr% SET `flag` = :flag, `num2` = :money, `data` = :json WHERE `tpid` = :projectId AND `formid` = "info" AND `part` = "period" AND `period` = :period LIMIT 1';
				mydb::query($stmt, $data);
				//$ret .= mydb()->_query;
				//$ret .= print_o($data,'$data');
			}
			//$ret .= print_o(post(),'post()');
			break;

		case 'send.approved':
			if (SG\confirm()) {
				//TODO: ดึง uid ที่เคย approved มารวมกับ uid ใหม่
				$period = $tranId;
				$childId = post('child');
				$periodInfo = R::Model('project.period.get', $childId, $period);
				$data = json_decode($periodInfo->data);
				$data->approved[] = array('user' => i()->uid, 'date' => date('Y-m-d'));
				$stmt = 'UPDATE %project_tr% SET
					`flag` = :flag
					, `data` = :data
					WHERE `tpid` = :childId AND `formid` = "info" AND `part` = "period" AND `period` = :period LIMIT 1';
				mydb::query($stmt, ':childId', $childId, ':period', $period, ':flag', _PROJECT_PERIOD_FLAG_GRANT, ':data', json_encode($data));
				//$ret .= mydb()->_query;
				//$ret .= print_o($periodInfo, '$periodInfo');
			}
			break;

		case 'send.delete':
			if (SG\confirm()) {
				$stmt = 'DELETE FROM %project_tr% WHERE `trid` = :tranId LIMIT 1';
				mydb::query($stmt, ':tranId', $tranId);
			}
			break;

		case 'train.save':
			$ret .= 'บันทึกเรียบร้อย';
			$data = (Object) post('data');
			$data->projectId = $projectId;
			$data->trainId = $tranId;
			$data->orgId = $projectInfo->orgid;
			$data->formid = 'info';
			$data->part = 'train';
			$data->trainingDate = sg_date($data->trainingDate, 'Y-m-d');
			$data->uid = $data->modifyby = i()->uid;
			$data->created = $data->modified = date('U');

			$stmt = 'INSERT INTO %project_tr%
				(`trid`, `tpid`, `orgid`, `uid`, `formid`, `part`, `parent`
				, `date1`, `refid`, `detail1`, `num1`, `text1`, `text2`
				, `created`)
				VALUES
				(:trainId, :projectId, :orgId, :uid, :formid, :part, :actionId
				, :trainingDate, :trainingType, :trainingLoc, :trainingHour, :trainingDetail, :learnDetail
				, :created)
				ON DUPLICATE KEY UPDATE
				  `date1` = :trainingDate
				, `refid` = :trainingType
				, `detail1` = :trainingLoc
				, `num1` = :trainingHour
				, `text1` = :trainingDetail
				, `text2` = :learnDetail
				, `modified` = :modified
				, `modifyby` = :modifyby
				';
			mydb::query($stmt, $data);

			//$ret .= '<br />'.mydb()->_query;
			//$ret .= print_o($data, '$data');
			break;

		case 'member.add':
			if (($isOwner || $isOfficer) && ($addUserId = post('uid'))) {
				$addMembership = SG\getFirst(post('membership'),'OWNER');
				$stmt = 'INSERT INTO %topic_user%
					(`tpid`, `uid`, `membership`)
					VALUES
					(:tpid, :uid, :membership)
					ON DUPLICATE KEY UPDATE
					`membership` = :membership;';

				mydb::query($stmt,':tpid',$projectId, ':uid', $addUserId, ':membership', strtoupper($addMembership));

				model::watch_log('project','add owner',$user->name.'('.$user->uid.') was added to be an owner of project '.$projectId.' by '.i()->name.'('.i()->uid.')');
			}
			break;

		case 'org.co.add':
			if ($orgId = post('orgId')) {
				mydb::query('INSERT INTO %project_orgco%
					(`tpid`, `orgId`, `uid`, `created`)
					VALUES
					(:projectId, :orgId, :uid, :created)
					ON DUPLICATE KEY UPDATE
					`orgId` = :orgId',
					[
						':projectId' => $projectId,
						':orgId' => $orgId,
						':uid' => i()->uid,
						':created' => date('U'),
					]
				);
			}
			break;

		case 'org.co.remove':
			if ($tranId && SG\confirm()) {
				mydb::query(
					'DELETE FROM %project_orgco% WHERE `tpid` = :projectId AND `orgId` = :orgId LIMIT 1',
					[':projectId' => $projectId, ':orgId' => $tranId]
				);
			}
			break;

		// FOR OFFICER ONLY

		case 'paiddoc.add':
			if ($isOfficer) {
				$data = (object) post('data');
				if ($data->amount > 0) {
					$trid = R::Model('project.paiddoc.create', $projectInfo, $data);
				}
				if (!$tranId) location('project/'.$projectId.'/info.paiddoc/'.$trid);
			}
			break;

		case 'paiddoc.remove' :
			if ($isOfficer && $tranId && SG\confirm()) {
				R::Model('project.paiddoc.remove', $projectId, $tranId);
				$fundInfo = R::Model('project.fund.get', $projectInfo->orgid);
				R::Model('project.nhso.obt.update', $fundInfo);
			}
			break;

		case 'paiddoc.newcode':
			if ($isOfficer && $tranId && SG\confirm()) {
				$newRefcode = R::Model('project.gl.getnextref','PAY',true);
				$stmt = 'UPDATE %project_paiddoc% SET `refcode` = :refcode WHERE `tpid` = :tpid AND `paidid` = :paidid';
				mydb::query($stmt, ':tpid', $projectId, ':paidid', $tranId, ':refcode', $newRefcode);
				//$ret .= mydb()->_query.'<br />';

				$stmt = 'UPDATE %project_gl% SET `refcode` = :refcode WHERE `tpid` = :tpid AND `actid` = :actid';
				mydb::query($stmt, ':tpid', $projectId, ':actid', $tranId, ':refcode', $newRefcode);
				//$ret .= mydb()->_query.'<br />';
			}
			break;

		case 'paiddoc.glcreate' :
			if ($isOfficer && SG\confirm()) {
				$paiddocInfo = R::Model('project.paiddoc.get',$projectId,$tranId);
				$options = options('project');

				$glExpenseCode = '50'.$projectInfo->info->supporttype.'00';
				$gldata->tpid = $projectId;
				$gldata->orgid = $projectInfo->orgid;
				$gldata->actid = $paiddocInfo->paidid;
				$gldata->uid = $paiddocInfo->uid;
				$gldata->refcode = R::Model('project.gl.getnextref','PAY',true);
				$gldata->refdate = $paiddocInfo->paiddate;
				$gldata->items = array(
					array(
						'pglid' => NULL,
						'glcode' => $glExpenseCode,
						'amount' => $paiddocInfo->amount
						),
					array(
						'pglid' => NULL,
						'glcode' => $options->bankcode,
						'amount' => -($paiddocInfo->amount)
					),
				);

				//$ret .= print_o($paiddocInfo,'$paiddocInfo').print_o($gldata,'$gldata');

				$glidResult = R::Model('project.gl.tran.add', $gldata, '');

				//$ret .= print_o($glidResult, '$glidResult');

				$stmt = 'UPDATE %project_paiddoc% SET `refcode` = :refcode WHERE `paidid` = :paidid LIMIT 1';
				mydb::query($stmt, ':paidid', $tranId, ':refcode', $gldata->refcode);

				//$ret .= mydb()->_query;
			}
			break;

		case 'paiddoc.upload':
			$paidDocs = R::Model('project.paiddoc.get', $projectId, NULL, NULL, '{getAllRecord: false, debug: false}');
			$data->tpid = $projectId;
			$data->prename = 'project_paiddoc_'.$projectId.'_';
			$data->tagname = 'project,paiddoc';
			$data->title = 'ใบเบิกเงิน '.$paidDocs->items[$trid]->refcode;
			$data->orgid = $projectInfo->orgid;
			$data->refid = $tranId;
			$data->deleteurl = 'project/'.$projectId.'/info/photo.delete/';
			$uploadResult = R::Model('photo.upload', $_FILES['photo'], $data);

			$ret = $uploadResult->link;

			//$ret .= print_o($data,'$data');
			//$ret .= print_o($uploadResult,'$uploadResult');
			//$ret .= print_o($paidDocs,'$paidDocs');
			break;

		case 'moneyback.save':
			$post = (Object) post('data');
			if ($isOfficer && $post->no && $post->rcvdate && $post->amount) {
				if (empty($tranId)) {
					$post->trid = NULL;
					$post->refcode = R::Model('project.gl.getnextref','RET',true);
				} else {
					$post->trid = $tranId;
					$moneybackInfo = R::Model('project.moneyback.get', $projectId, $tranId);
					$post->refcode = $moneybackInfo->refcode;
				}
				$actionMsg = $post->trid ? 'Edit' : 'Add';
				$post->tpid = $projectId;
				$post->amount = abs(sg_strip_money($post->amount));
				$post->uid = i()->uid;
				$post->rcvdate = sg_date($post->rcvdate,'Y-m-d');
				$post->formid = 'info';
				$post->part = 'moneyback';
				$post->created = date('U');

				$stmt = 'INSERT INTO %project_tr%
					(`trid`, `tpid`, `uid`, `formid`, `part`
					, `num1`, `date1`, `detail1`, `detail2`
					, `created`)
					VALUES
					(:trid, :tpid, :uid, :formid, :part
					, :amount, :rcvdate, :no, :refcode
					, :created)
					ON DUPLICATE KEY
					UPDATE
					  `num1` = :amount
					, `date1` = :rcvdate
					, `detail1` = :no';

				mydb::query($stmt, $post);
				//$ret .= mydb()->_query;

				if (empty($post->trid)) $post->trid = mydb()->insert_id;

				$options = options('project');

				$glTran = R::Model('project.gl.tran.get', $post->refcode);

				$glMoneyBackCode = '40500';

				$gldata = new stdClass();

				if (empty($data->pglid)) $gldata->pglid = NULL;
				$gldata->tpid = $projectId;
				$gldata->orgid = $projectInfo->orgid;
				$gldata->actid = $post->trid;
				$gldata->refcode = $post->refcode;
				$gldata->refdate = $post->rcvdate;

				$gldata->items = array(
					array(
						'pglid' => $glTran->items[0]->pglid,
						'glcode' => $options->bankcode,
						'amount' => $post->amount
					),
					array(
						'pglid' => $glTran->items[1]->pglid,
						'glcode' => $glMoneyBackCode,
						'amount' => -($post->amount)
					),
				);
				$glid = R::Model('project.gl.tran.add',$gldata);

				//$ret.=mydb()->_query.'<br />';
				//$ret.=print_o($glid,'$glid');
				//$ret.=print_o($glTran,'$glTran');

				$fundInfo = R::Model('project.fund.get', $projectInfo->orgid);
				R::Model('project.nhso.obt.update', $fundInfo);

				R::Model('watchdog.log','project','Money Back '.$actionMsg,'Project id '.$projectId.' - Tran '.$post->trid.'/'.$post->refcode.' Amount '.$post->amount.' by '.i()->name.'('.i()->uid.')', NULL, $projectId);

			}
			break;

		case 'moneyback.remove':
			if ($isOfficer && $tranId && SG\confirm()) {
				R::Model('project.moneyback.remove', $projectId, $tranId);
				$fundInfo = R::Model('project.fund.get', $projectInfo->orgid);
				R::Model('project.nhso.obt.update', $fundInfo);
			}
			break;

		case 'moneyback.glcreate' :
			if ($isOfficer && SG\confirm()) {
				$moneybackInfo = R::Model('project.moneyback.get', $projectId, $tranId);
				//$glTran = R::Model('project.gl.tran.get', $moneybackInfo->refcode);
				$options = options('project');

				$glMoneyBackCode = '40500';
				$gldata->tpid = $projectId;
				$gldata->orgid = $projectInfo->orgid;
				$gldata->actid = $moneybackInfo->trid;
				$gldata->uid = $moneybackInfo->uid;
				$gldata->refcode = R::Model('project.gl.getnextref','RET',true);
				$gldata->refdate = $moneybackInfo->rcvdate;
				$gldata->items = array(
					array(
						'pglid' => NULL,
						'glcode' => $options->bankcode,
						'amount' => $moneybackInfo->amount
						),
					array(
						'pglid' => NULL,
						'glcode' => $glMoneyBackCode,
						'amount' => -($moneybackInfo->amount)
					),
				);

				//$ret .= print_o($gldata,'$gldata');

				$glidResult = R::Model('project.gl.tran.add', $gldata, '');

				//$ret .= print_o($glidResult, '$glidResult');

				$stmt = 'UPDATE %project_tr% SET `detail2` = :refcode WHERE `trid` = :trid LIMIT 1';
				mydb::query($stmt, ':trid', $tranId, ':refcode', $gldata->refcode);

				//$ret .= mydb()->_query;
			}
			break;

		// For Admin Only
		case 'org.move':
			if ($isAdmin && ($orgId = post('org'))) {
				mydb::query(
					'UPDATE %topic% SET `orgid` = :orgId WHERE `tpid` = :projectId LIMIT 1',
					':projectId', $projectId,
					':orgId', $orgId
				);
			}
			break;

		default:
			$ret .= 'NO ACTION';
			break;
	}

	return $ret;
}
?>