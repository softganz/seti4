<?php
/**
 * Assessor main page
 *
 * @param Integer $userId
 * @param String $action
 * @param Integer $tranId
 * @return String
 */
function project_assessor($self, $userId = NULL, $action = NULL, $tranId = NULL) {
	R::View('project.toolbar',$self,'เครือข่ายนักติดตามประเมินผล','assessor');

	if (!is_numeric($userId)) {$action=$userId;unset($userId);}

	if ($userId) {
		$assessorInfo = mydb::select('SELECT * FROM %person_group% WHERE `groupname` = "assessor" AND `uid` = :uid LIMIT 1',':uid',$userId);
		mydb::clearprop($assessorInfo);
		$psnid = $assessorInfo->psnid;
		//$ret .= $userId.' '.$psnid;
		$assessorInfo->psnInfo = R::Model('person.get',$psnid);
		$psnInfo = $assessorInfo->psnInfo;
		//$psnInfo->assessorId = $
		//$ret .= print_o($assessorInfo,'$assessorInfo');
	}

	$isAdmin = user_access('access administrator pages');

	switch ($action) {
		case 'addtr':
			$data=(object)post();
			if (post('save')) {
				$ret .= __project_assessor_addedu($assessorInfo,$data);
				$ret .= __project_assessor_addjob($assessorInfo,$data);
				$ret .= __project_assessor_addskill($assessorInfo,$data);
				$ret .= __project_assessor_addproject($assessorInfo,$data);
				$ret .= __project_assessor_addref($assessorInfo,$data);
				$ret .= R::View('project.assessor.info',$assessorInfo);
				location('project/assessor');
			} else {
				$ret.=R::View('project.assessor.info',$assessorInfo);
			}
			//$ret.=print_o($data,'$data');
			break;

		case 'uploadphoto':
			$data->tpid=$tpid;
			$data->prename='assessor_'.$psnid.'_';
			$data->tagname='assessor';
			$data->title=$psnInfo->fullname;
			$data->refid=$psnid;
			$data->deleteurl='project/assessor/'.$userId.'/delphoto/';
			$uploadResult=R::Model('photo.upload',$_FILES['photo'],$data);
			$ret.=$uploadResult->link;
			//$ret.=print_o($uploadResult,'$uploadResult');
			return $ret;
			break;

		case 'delphoto':
			if ($tranId && SG\confirm()) {
				$result=R::Model('photo.delete',$tranId);
			}
			return 'Photo deleted.';
			break;

		case 'deltr':
			if ($tranId && $userId && SG\confirm()) {
				mydb::query('DELETE FROM %person_tr% WHERE `psntrid`=:trid AND `uid`=:uid LIMIT 1',':trid',$tranId,':uid',$userId);
			}
			$ret.=R::View('project.assessor.info',$assessorInfo);
			break;

		case 'addjob':
			$ret.=R::View('project.assessor.info',$assessorInfo);
			break;

		case 'takecourse':
			$ret.='User Take Course';
			//$ret.=R::Page('project.assessor.takecourse',$)
			break;

		case 'cancel' :
			if ($isAdmin) {
				$newStatus = $assessorInfo->status > 0 ? 0 : 1;
				$stmt = 'UPDATE %person_group% SET `status` = :newStatus WHERE `groupname` = "assessor" AND `uid` = :uid LIMIT 1';
				mydb::query($stmt,':uid',$userId, ':newStatus',$newStatus);
				//$ret .= mydb()->_query;
			}
			break;

		default:
			if ($userId) {
				$ret.=R::View('project.assessor.info',$assessorInfo);
			} else {
				$ret.=R::Page('project.assessor.list',$self);
			}
			break;
	}
	//$ret.=print_o($psnInfo,'$psnInfo');
	return $ret;
}

function __project_assessor_addedu($assessorInfo,$dataInfo) {
	foreach ($dataInfo->edu as $data) {
		$data=(object)$data;
		if ($data->grade || $data->faculty || $data->college) {
			$data->psntrid=$data->trid;
			$data->psnid=$assessorInfo->psnInfo->psnid;
			$data->tagname='education';
			$data->date1=sg_date($data->year.'-01-01','Y-m-d');
			$data->uid=$assessorInfo->uid;
			$data->created=date('U');
			$stmt='INSERT INTO %person_tr%
							(`psntrid`,`psnid`,`uid`,`tagname`,`date1`,`detail1`,`detail2`,`detail3`,`detail4`,`created`)
							VALUES
							(:psntrid,:psnid,:uid,:tagname,:date1,:grade,:faculty,:branch,:college,:created)
							ON DUPLICATE KEY UPDATE
							`date1`=:date1, `detail1`=:grade, `detail2`=:faculty, `detail3`=:branch, `detail4`=:college
							';
			mydb::query($stmt,$data);
			$ret.=debugMsg(mydb()->_query);
		}
	}
	return $ret;
}

function __project_assessor_addjob($assessorInfo,$dataInfo) {
	foreach ($dataInfo->job as $data) {
		$data=(object)$data;
		if ($data->position || $data->company) {
			$data->psntrid=$data->trid;
			$data->psnid=$assessorInfo->psnInfo->psnid;
			$data->tagname='job';
			$data->date1=sg_date($data->year.'-01-01','Y-m-d');
			$data->uid=$assessorInfo->uid;
			$data->created=date('U');
			$stmt='INSERT INTO %person_tr%
							(`psntrid`,`psnid`,`uid`,`tagname`,`date1`,`detail1`,`detail2`,`detail3`,`created`)
							VALUES
							(:psntrid,:psnid,:uid,:tagname,:date1,:position,:company,:orgtype,:created)
							ON DUPLICATE KEY UPDATE
							`date1`=:date1, `detail1`=:position, `detail2`=:company, `detail3`=:orgtype
							';
			mydb::query($stmt,$data);
			//$ret.=debugMsg(mydb()->_query);
		}
	}
	return $ret;
}

function __project_assessor_addskill($assessorInfo,$dataInfo) {
	foreach ($dataInfo->skill as $data) {
		$data=(object)$data;
		if ($data->skill) {
			$data->psntrid=$data->trid;
			$data->psnid=$assessorInfo->psnInfo->psnid;
			$data->tagname='skill';
			$data->uid=$assessorInfo->uid;
			$data->created=date('U');
			$stmt='INSERT INTO %person_tr%
							(`psntrid`,`psnid`,`uid`,`tagname`,`detail1`,`created`)
							VALUES
							(:psntrid,:psnid,:uid,:tagname,:skill,:created)
							ON DUPLICATE KEY UPDATE
							`detail1`=:skill
							';
			mydb::query($stmt,$data);
			//$ret.=debugMsg(mydb()->_query);
		}
	}
	return $ret;
}

function __project_assessor_addproject($assessorInfo,$dataInfo) {
	foreach ($dataInfo->project as $data) {
		$data=(object)$data;
		if ($data->title) {
			$data->psntrid=$data->trid;
			$data->psnid=$assessorInfo->psnInfo->psnid;
			$data->tagname='project';
			$data->date1=sg_date($data->year.'-01-01','Y-m-d');
			$data->uid=$assessorInfo->uid;
			$data->created=date('U');
			$stmt='INSERT INTO %person_tr%
							(`psntrid`,`psnid`,`uid`,`tagname`,`date1`,`detail1`,`detail2`,`created`)
							VALUES
							(:psntrid,:psnid,:uid,:tagname,:date1,:title,:granter,:created)
							ON DUPLICATE KEY UPDATE
							`date1`=:date1, `detail1`=:title, `detail2`=:granter
							';
			mydb::query($stmt,$data);
			//$ret.=debugMsg(mydb()->_query);
		}
	}
	return $ret;
}

function __project_assessor_addref($assessorInfo,$dataInfo) {
	foreach ($dataInfo->ref as $data) {
		$data=(object)$data;
		if ($data->name || $data->company) {
			$data->psntrid=$data->trid;
			$data->psnid=$assessorInfo->psnInfo->psnid;
			$data->tagname='reference';
			$data->uid=$assessorInfo->uid;
			$data->created=date('U');
			$stmt='INSERT INTO %person_tr%
							(`psntrid`,`psnid`,`uid`,`tagname`,`detail1`,`detail2`,`created`)
							VALUES
							(:psntrid,:psnid,:uid,:tagname,:name,:company,:created)
							ON DUPLICATE KEY UPDATE
							`detail1`=:name, `detail2`=:company
							';
			mydb::query($stmt,$data);
			//$ret.=debugMsg(mydb()->_query);
		}
	}
	return $ret;
}

?>