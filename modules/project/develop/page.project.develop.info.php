<?php
/**
 * Project Development View
 * @param Object $self
 * @param Int $tpid
 * @param String $action
 * @return String
 */

// TODO : เพิ่มเจ้าของโครงการพัฒนา (จากเดิมเพิ่มได้เฉพาะพี่เลี้ยง)

function project_develop_info($self, $tpid, $action = NULL, $tranId = NULL) {
	$devInfo = is_object($tpid) ? $tpid : R::Model('project.develop.get',$tpid);
	$tpid = $devInfo->tpid;

	if (empty($tpid))
		return $ret.message('error','ขออภัย : ไม่มีโครงการที่กำลังพัฒนาอยู่ในระบบ');


	$isAdmin = $devInfo->RIGHT & _IS_ADMIN;
	$isTrainer = $devInfo->RIGHT & _IS_TRAINER;
	$isEditable = $devInfo->RIGHT & _IS_EDITABLE;
	$isFullView = $devInfo->RIGHT & _IS_RIGHT;

	$is_comment_sss = user_access('comment project');
	$is_comment_hsmi = user_access('administer papers,administer projects') || $isTrainer;



	switch ($action) {
		case 'problem.edit' :
			$data = new stdClass();
			$data->tpid = $tpid;
			$data->uid = i()->uid;
			$data->formid = 'develop';
			$data->part = 'problem';
			$data->created = date('U');
			$data->problemother = post('problemother');
			$data->problemdetail = post('problemdetail');
			$data->problemsize = post('problemsize');
			$data->problemref = post('problemref');

			if ($data->problemother) {
				$stmt = 'INSERT INTO %project_tr%
							(`tpid`, `uid`, `formid`, `part`, `detail1`, `text1`, `num1`, `created`)
							VALUES
							(:tpid, :uid, :formid, :part, :problemother, :problemdetail, :problemsize, :created)';
				mydb::query($stmt,$data);
				//$ret.=mydb()->_query;
			}

			if ($data->problemref) {
				list($a,$b,$c,$refid) = explode(':', post('problemref'));
				$data->refid = $refid;
				$data->tagname = $a.':'.$b.':'.$c;
				$stmt = 'SELECT * FROM %tag% WHERE `taggroup`=:taggroup AND `catid`=:catid LIMIT 1';
				$problemRs = mydb::select($stmt,':taggroup',$data->tagname, ':catid',$data->refid);
				$detail = json_decode($problemRs->description);
				$data->problemother = $problemRs->name;
				//$ret.=print_o($problemRs);
			}

			if ($data->refid) {
				$stmt = 'INSERT INTO %project_tr%
							(`tpid`, `uid`, `refid`, `tagname`, `formid`, `part`, `detail1`, `num1`, `created`)
							VALUES
							(:tpid, :uid, :refid, :tagname, :formid, :part, :problemother, :problemsize, :created)';
				mydb::query($stmt,$data);
				//$ret .= mydb()->_query;
			}
			//$devInfo = R::Model('project.develop.get',$tpid);
			//$ret.=print_o($data,'$data');
			break;

		case 'problem.detail':
			$problem = NULL;
			$refid = post('ref');
			foreach ($planInfo->problem as $rs) {
				if (($tranId && $rs->trid == $tranId) || ($refid && $rs->refid == $refid)) {
					$problem = $rs;
					break;
				}
			}
			$ret .= '<div id="project-info" '.sg_implode_attr($inlineAttr).'>'._NL;
			$ret .= '<h2>รายละเอียดสถานการณ์ปัญหา</h2>';
			$ret .= view::inlineedit(
							array('group'=>'project:problem:'.$refid,'fld'=>'text1','tr'=>$problem->trid,'refid'=>$refid,'class'=>'-fill','ret'=>'html','placeholder'=>'...'),
							$problem->detailproblem,
							true, //$isEdit,
							'textarea'
						);
			$ret .= '</div><!-- project-info -->';
			//$ret.=print_o($problem,'$problem');
			//$ret.=print_o($planInfo,'$planInfo');
			return $ret;
			break;

		case 'problem.remove' :
			if ($isEditable && $tpid && $tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %project_tr%
								WHERE `tpid` = :tpid AND `trid` = :trid AND `formid` = "develop" AND `part` = "problem"
								LIMIT 1';
				mydb::query($stmt,':tpid',$tpid, ':trid',$tranId);
			}
			break;	

		case 'objective.edit' :
			$data=new stdClass();
			$data->tpid=$tpid;
			$data->uid=i()->uid;
			$data->formid='develop';
			$data->part='objective';
			$data->created=date('U');
			$data->objective=post('objective');
			$data->indicator=post('indicator');
			$data->problemsize = SG\getFirst(post('problemsize'));
			$data->targetsize=post('targetsize');

			if ($data->objective) {
				$stmt='INSERT INTO %project_tr%
							(`tpid`, `parent`, `uid`, `formid`, `part`, `text1`, `text2`, `num1`, `num2`, `created`)
							VALUES
							(:tpid, 1, :uid, :formid, :part, :objective, :indicator, :problemsize, :targetsize, :created)';
				mydb::query($stmt,$data);
				//$ret.=mydb()->_query;
			}

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

			$devInfo=R::Model('project.develop.get',$tpid);

			//$ret.=print_o($data,'$data');
			//$ret.=print_o(post(),'post()');
			//location('paper/'.$tpid);
			break;

		case 'objective.remove' :
			if ($tpid && $tranId && SG\confirm()) {
				mydb::query('DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `trid`=:trid AND `formid`="develop" AND `part`="objective" LIMIT 1',':tpid',$tpid, ':trid',$tranId);
			}
			break;

		case 'objective.info' :
			$ret.='<h4>วัตถุประสงค์</h4>';
			$ret.='<p>'.$info->objective[$tranId]->title.'</p>';
			$ret.='<h4>ตัวชี้วัดความสำเร็จ</h4>';
			$ret.='<p>'.nl2br($info->objective[$tranId]->indicator).'</p>';
			//$ret.=print_o($info,'$info');
			return $ret;
			break;

		case 'target.add':
			$isEdit = true;

			$data = new stdClass();
			$data->tpid = $tpid;
			$data->uid = i()->uid;
			$data->targetname = post('targetname');
			$data->targetsize = post('targetsize');
			$data->created = date('U');

			if (is_numeric($data->targetname)) {
				$isCodeExist = mydb::select('SELECT `catid` FROM %tag% WHERE `taggroup` = "project:target" AND `catid` = :catid AND `catparent` IS NOT NULL AND `process` IS NOT NULL LIMIT 1', ':catid',$data->targetname)->catid;
				if (!$isCodeExist) break;
			}

			$stmt = 'INSERT INTO %project_target% (`tpid`, `tagname`, `tgtid`, `amount`) VALUES (:tpid, "develop", :targetname, :targetsize)';
			mydb::query($stmt, $data);
			break;

		case 'target.delete':
			$isEdit = true;
			if ($tpid && post('id') != '' && SG\confirm()) {
				$stmt = 'DELETE FROM %project_target% WHERE `tpid` = :tpid AND `tgtid` = :tgtid AND `tagname` = "develop" LIMIT 1';
				mydb::query($stmt, ':tpid', $tpid, ':tgtid', post('id'));
			}
			//return $ret;
			break;

		default:
			$ret .= 'NO ACTION';
			break;
	}
	return $ret;
}
?>