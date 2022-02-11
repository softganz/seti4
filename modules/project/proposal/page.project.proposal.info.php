<?php
/**
* Project Proposal Information Model
*
* @param Object $self
* @param Object $proposalInfo
* @param Object $action
* @param Object $tranId
* @return String
*/

import('model:project.proposal.php');

function project_proposal_info($self, $proposalInfo = NULL, $action = NULL, $tranId = NULL) {
	$tpid = $proposalInfo->tpid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$isAdmin = $proposalInfo->RIGHT & _IS_ADMIN;
	$isEdit = $proposalInfo->RIGHT & _IS_EDITABLE;

	//$ret .= 'Action = '.$action. ', Is edit = '.($isEdit ? 'YES' : 'NO').'<br />';

	switch ($action) {

		case 'delete':
			if ($isEdit && $tpid && SG\confirm()) {
				//TODO: Delete project proposal
			} else {
				$ret .= message('error','ACCESS DENIED');
			}
			break;

		case 'send':
			if ($isEdit && $tpid) {
				$stmt = 'UPDATE %project_dev% SET `status` = 2 WHERE `tpid` = :tpid LIMIT 1';
				mydb::query($stmt, ':tpid', $tpid);
			}
			break;

		case 'makefollow':
			$isCreateFollow = R::Model('project.right.develop.createfollow',$proposalInfo);
			$isRemoveProjectData = $isAdmin && post('delproject');

			// Remove all follow data before create follow project
			if ($isRemoveProjectData && SG\confirm()) {
				$stmt = 'DELETE FROM %project% WHERE `tpid` = :tpid LIMIT 1';
				mydb::query($stmt, ':tpid', $tpid);

				$stmt = 'DELETE FROM %bigdata% WHERE `keyname` LIKE "project.info%" AND `keyid` = :tpid';
				mydb::query($stmt, ':tpid', $tpid);

				$stmt = 'DELETE FROM %project_prov% WHERE `tagname` = "info" AND `tpid` = :tpid';
				mydb::query($stmt, ':tpid', $tpid);

				$stmt = 'DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `formid` LIKE "info"';
				mydb::query($stmt, ':tpid', $tpid);

				$stmt = 'DELETE FROM %topic_parent% WHERE `tpid` = :tpid';
				mydb::query($stmt, ':tpid', $tpid);

				$stmt = 'DELETE FROM %project_target% WHERE `tpid` = :tpid AND `tagname` LIKE "info%"';
				mydb::query($stmt, ':tpid', $tpid);

				$stmt = 'DELETE FROM %calendar% WHERE `tpid` = :tpid';
				mydb::query($stmt, ':tpid', $tpid);
			}

			if ($isCreateFollow && SG\confirm()) {
				$result = R::Model('project.develop.follow.create', $proposalInfo,'{debug: false}');

				//$ret .= print_o($result, '$result');
			}
			break;

		case 'status.set':
			if ($isAdmin && $tpid && $tranId) {
				$stmt = 'UPDATE %project_dev% SET `status` = :status WHERE `tpid` = :tpid LIMIT 1';
				mydb::query($stmt, ':tpid', $tpid, ':status', $tranId);
			}
			break;

		case 'docs.upload':
			if ($isEdit) {
				$data = (object)post('document');
				$data->tpid = $tpid;
				$docFiles = $_FILES['document'];
				$result = R::Model('doc.upload',$docFiles, $data);

				// $ret .= print_o($result,'$result');
			}
			$ret .= $isEdit ? 'EDITABLE' : 'NOT EDITABLE';
			// $ret .= print_o(post(),'post()');
			// $ret .= print_o($_FILES,'$_FILES');
			break;

		case 'docs.delete':
			if ($isEdit && $tranId && SG\confirm()) {
				R::Model('doc.delete', $tranId);
			}
			break;

		case 'area.delete':
			$ret .= 'DELETE';
			if ($isEdit && $tranId && SG\confirm()) {
				$stmt = 'DELETE FROM %project_prov% WHERE `tpid` = :tpid AND `autoid` = :trid AND `tagname` = :tagname LIMIT 1';
				mydb::query($stmt, ':tpid', $tpid, ':trid', $tranId, ':tagname', _PROPOSAL_TAGNAME);
				$ret .= ' COMPLETED';
				//$ret .= mydb()->_query;
			} else {
				$ret .= ' ERROR';
			}
			break;

		case 'area.save':
			if ($isEdit && post('changwat') != '') {
				$data = (object) post();
				$data->tagname = _PROPOSAL_TAGNAME;
				$stmt = 'INSERT INTO %project_prov%
							(`tpid`,`tagname`,`changwat`,`ampur`,`tambon`,`areatype`)
							VALUES
							(:tpid,:tagname,:changwat,:ampur,:tambon,:areatype)';
				mydb::query($stmt,':tpid',$tpid,$data);
				//$ret .= mydb()->_query;
			}
			break;

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

		case 'objective.save' :
			$data=new stdClass();
			$data->tpid=$tpid;
			$data->uid=i()->uid;
			$data->formid = _PROPOSAL_TAGNAME;
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


			//$ret.=print_o($data,'$data');
			//$ret.=print_o(post(),'post()');
			//location('paper/'.$tpid);
			break;

		case 'objective.remove' :
			if ($isEdit && $tpid && $tranId && SG\confirm()) {
				mydb::query('DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `trid` = :trid AND `formid` = :tagname AND `part` = "objective" LIMIT 1',':tpid', $tpid, ':tagname', _PROPOSAL_TAGNAME, ':trid', $tranId);
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
			if ($isEdit) {
				$data = new stdClass();
				$data->tpid = $tpid;
				$data->uid = i()->uid;
				$data->tagname = _PROPOSAL_TAGNAME;
				$data->targetname = post('targetname');
				$data->targetsize = post('targetsize');
				$data->created = date('U');

				if (is_numeric($data->targetname)) {
					$isCodeExist = mydb::select('SELECT `catid` FROM %tag% WHERE `taggroup` = "project:target" AND `catid` = :catid AND `catparent` IS NOT NULL AND `process` IS NOT NULL LIMIT 1', ':catid',$data->targetname)->catid;
					if (!$isCodeExist) break;
				}

				$stmt = 'INSERT INTO %project_target%
					(`tpid`, `tagname`, `tgtid`, `amount`)
					VALUES
					(:tpid, :tagname, :targetname, :targetsize)
					ON DUPLICATE KEY UPDATE `amount` = :targetsize';
				mydb::query($stmt, $data);
			}
			break;

		case 'target.delete':
			if ($isEdit && $tpid && post('id') != '' && SG\confirm()) {
				$stmt = 'DELETE FROM %project_target% WHERE `tpid` = :tpid AND `tgtid` = :tgtid AND `tagname` = "develop" LIMIT 1';
				mydb::query($stmt, ':tpid', $tpid, ':tgtid', post('id'));
				$ret .= 'ลบกลุ่มเป้าหมายเรียบร้อย';
			}
			break;

		case 'plan.save':
			$ret .= 'SAVE PLAN';

			if ($isEdit) {
				//$ret.=print_o(post(),'post()');
				//$ret.=__project_plan_add($tpid,NULL,NULL,$info);
				$data = (Object) post('plan');

				if ($data->title) {
					if ($before = $data->before) {
						$sorder = $before;
						// เพิ่มลำดับของกิจกรรมหลัง
						mydb::query('UPDATE %project_tr% SET `sorder`= `sorder`+1 WHERE `tpid` = :tpid AND `formid` = :tagname AND `part` = "activity" AND `sorder` >= :before ORDER BY `sorder` ASC',':tpid',$tpid, ':tagname', _PROPOSAL_TAGNAME ,':before',$before);
					} else {
						$sorder = mydb::select('SELECT MAX(`sorder`) `maxOrder` FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = :tagname AND `part` = "activity" LIMIT 1',':tpid',$tpid, ':tagname', _PROPOSAL_TAGNAME)->maxOrder+1;
					}

					$data->tpid = $tpid;
					$data->parent = $tranId;
					$data->tagname = _PROPOSAL_TAGNAME;
					$data->sorder = $sorder;
					$data->uid = i()->uid;
					$data->title = $data->title;
					$data->created = date('U');
					$data->fromdate = $data->fromdate ? sg_date($data->fromdate,'Y-m-d') : NULL;
					$data->todate = $data->todate ? sg_date(post('todate'),'Y-m-d') : NULL;
					$data->detail = $data->detail;
					$data->outputoutcome = $data->outputoutcome;
					$data->otherresource = $data->otherresource;
					$data->orgsupport = $data->orgsupport;
					$data->budget = $data->budget;

					$stmt = 'INSERT INTO %project_tr%
						(
						`tpid`, `parent`, `sorder`, `uid`, `formid`, `part`, `date1`, `date2`, `detail1`
						, `text1`,`text3`, `text7`, `text4`, `num1`, `created`
						)
						VALUES
						(
						:tpid, :parent, :sorder, :uid, :tagname , "activity", :fromdate, :todate, :title
						, :detail, :outputoutcome, :otherresource, :orgsupport, :budget, :created
						)';
					mydb::query($stmt, $data);
					$addTrid = mydb()->insert_id;

					//$ret.=mydb()->_query.'<br />';
					//$ret.=print_o($data,'$data');

					// เรียงลำดับกิจกรรมใหม่
					if ($before) {
						mydb::query('SET @n:=0 ;');
						mydb::query('UPDATE %project_tr% SET `sorder`= @n := @n+1 WHERE `tpid`=:tpid AND `formid`=:tagname AND `part`="activity" ORDER BY `sorder` ASC;',':tpid',$tpid, ':tagname',_PROPOSAL_TAGNAME);
					}

					// Calculate total budget
					ProjectProposalModel::calculateBudget($tpid);


					/*
					// Return case
					if (post('ret')) {
						$ret .= R::Page('project.develop.plan.'.post('ret'), NULL, $tpid, 'edit');
					} else $ret .= R::Page('project.develop.plan.tree', NULL, $tpid, 'view', $tranId);
					*/
				}
				/* else {
					$options = (object)array('ret' => post('ret'), 'rel' => post('rel'));
					$ret .= R::View('project.develop.plan.form', $tpid, $tranId, $options);
				}
				*/
			}
			//$ret .= print_o(post(),'post()');
			break;

		case 'plan.remove' :
			if ($isEdit && $tranId && SG\confirm()) {
				// Delete Objective
				$stmt='DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = :tagname AND `part` = "actobj" AND `refid` = :trid';
				mydb::query($stmt,':tpid',$tpid, ':tagname',_PROPOSAL_TAGNAME, ':trid',$tranId);
				//$ret.=mydb()->_query.'<br />';

				// Delete Expense
				$stmt = 'DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = :tagname AND `part` = "exptr" AND `parent` = :trid';
				mydb::query($stmt,':tpid',$tpid, ':tagname',_PROPOSAL_TAGNAME, ':trid',$tranId);
				//$ret.=mydb()->_query.'<br />';

				// Delete Plan
				$stmt = 'DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `trid` = :trid AND `formid` = :tagname AND `part` = "activity" LIMIT 1';
				mydb::query($stmt, ':tpid',$tpid, ':tagname',_PROPOSAL_TAGNAME, ':trid',$tranId);
				//$ret.=mydb()->_query.'<br />';

				ProjectProposalModel::calculateBudget($tpid);
			}
			break;

			case 'exp.save':
				if ($tpid && $tranId && post('exp')) {
					//$ret.=print_o(post('exp'),'exp');
					$exp = (Object) post('exp');

					$exp->parent = $tranId;
					if (empty($exp->expid)) $exp->expid = NULL;
					$exp->amt = sg_strip_money($exp->amt);
					$exp->unitprice = sg_strip_money($exp->unitprice);
					$exp->times = sg_strip_money($exp->times);
					$exp->total = sg_strip_money($exp->total);
					$exp->tpid = $tpid;
					$exp->formid = _PROPOSAL_TAGNAME;
					$exp->uid = $exp->modifyby=i()->uid;
					$exp->created = $exp->modified=date('U');

					$stmt = 'INSERT INTO %project_tr%
						(`trid`, `tpid`, `parent`, `gallery`, `formid`, `part`, `num1`, `num2`, `num3`, `num4`, `detail1`, `text1`, `uid`, `created`)
						VALUES
						(:expid, :tpid, :parent, :expcode, :formid,"exptr",:amt,:unitprice,:times,:total,:unitname,:detail,:uid,:created)
						ON DUPLICATE KEY
						UPDATE `gallery` = :expcode, `num1` = :amt, `num2` = :unitprice, `num3` = :times, `num4` = :total, `detail1` = :unitname, `text1` = :detail, `modified` = :modified, `modifyby` = :modifyby';

					mydb::query($stmt,$exp);
					//$ret .= mydb()->_query;

					R::On('project.proposal.change',$tpid,'addexp',array('value'=>'บันทึกค่าใช้จ่ายพัฒนาโครงการ  '.$exp->expcode.'='.$exp->total));

					ProjectProposalModel::calculateExpense($tpid);
					//$ret.=R::Page('project.develop.plan.single',NULL,$tpid,'edit');
					//$proposalInfo=R::Model('project.develop.get',$tpid);
					//$ret.=R::View('project.develop.plan.render', $proposalInfo, $proposalInfo->activity[$tranId], true);
					//$ret.=R::View('project.develop.plan.activity',$proposalInfo,$tranId);
				}
				break;

		/*
		case 'plan.changeparent':
			$activity=$proposalInfo->activity[$tranId];
			if (SG\confirm()) {
				if ($tranId && $to=post('to')) {
					if ($to=='master') $to=NULL;
					// บันทึก parent ของกิจกรรมที่ต้องการย้าย
					mydb::query('UPDATE %project_tr% SET `parent`=:to WHERE `trid`=:trid LIMIT 1',':trid',$tranId,':to',$to);
					//$ret.=mydb()->_query.'<br />';

					R::On('project.proposal.change',$tpid);
					$parent=$proposalInfo->activity[$tranId]->parent;
					$proposalInfo=R::Model('project.develop.get',$tpid);
					//$ret.=print_o($activity,'$activity');
					$ret.=R::View('project.develop.plan.detail',$proposalInfo,$parent);
					$ret.=R::View('project.develop.plan.activity',$proposalInfo,$parent);
					return $ret;
				}
			} else {
				$ret.='<h4>ย้ายกิจกรรมไปอยู่ภายใต้กิจกรรมหลักอื่น</h4>';
				$ret.='<h5>กิจกรรม : '.$activity->title.'</h5>';

				$form=new Form('data',url('project/develop/plan/'.$tpid.'/changeparent/'.$tranId),'project-edit-movemainact','sg-form');
				if (_AJAX) $form->addData('rel','#project-develop-plan-item-'.($activity->parent?$activity->parent:'master'));
				$form->addAttr('onsubmit','$.colorbox.close()');

				$form->addField('confirm',array('type'=>'hidden','name'=>'confirm','value'=>'yes'));

				$options=array();
				$options['master']='กิจกรรมหลัก/แผนดำเนินงาน';
				foreach ($proposalInfo->activity as $item) {
					//$ret.=$tranId.' : '.$item->trid.' : '.$item->sorder.' : '.$item->title.' expense='.count($item->expense).'<br />';
					if ($tranId==$item->trid || $item->expense) continue;
					if ($item->parent) {
						$options['ภายใต้กิจกรรมย่อยอื่น:'][$item->trid]=$item->title;
					} else {
						$options['ภายใต้กิจกรรมหลักอื่น:'][$item->trid]=$item->title;
					}
				}
				$form->addField('to',array('type'=>'radio','name'=>'to','label'=>'เลือกกิจกรรมหลักที่ต้องการย้ายกิจกรรมนี้ไป:','options'=>$options));

				$form->addField(
									'save',
									array(
										'type'=>'button',
										'name'=>'save',
										'value'=>'<i class="icon -save -white"></i><span>ย้ายกิจกรรม</span>',
										'posttext'=>' หรือ <a href="javascript:void(0)" onclick="$.colorbox.close()">ยกเลิก</a>'
										)
									);

				$ret .= $form->build();
				//$ret.=print_o($proposalInfo->activity,'$activity');
				return $ret;
			}
			break;
		*/

		case 'exp.remove' :
			if ($isEdit && $tranId && post('expid') && SG\confirm()) {
				$stmt = 'DELETE FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = :tagname AND `part` = "exptr" AND `trid` = :trid LIMIT 1';

				mydb::query($stmt,':tpid', $tpid, ':tagname', _PROPOSAL_TAGNAME, ':trid', post('expid'));

				ProjectProposalModel::calculateExpense($tpid);
				R::On('project.proposal.change', $tpid);
			}
			break;

		case 'budget.calculate':
			// Calculate total budget
			ProjectProposalModel::calculateBudget($tpid);
			$ret .= 'คำนวณงบประมาณเรียบร้อย';
			break;

		case 'exp.calculate' :
			$ret .= ProjectProposalModel::calculateExpense($tpid, '{debug: true}');
			$ret .= 'คำนวณค่าใช้จ่ายเรียบร้อย';
			break;

		default:
			$ret .= 'SORRY!!! NO ACTION';
			break;
	}

	return $ret;
}
?>