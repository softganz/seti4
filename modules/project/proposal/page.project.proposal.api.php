<?php
/**
* Project API :: Proposal API
* Created 2021-09-30
* Modify  2021-09-30
*
* @param Int $proposalId
* @param String $action
* @param Int $tranId
* @return Mixed
*
* @usage project/proposal/api/{id}/{action}[/{tranId}]
*/

$debug = true;

import('model:project.proposal.php');

class ProjectProposalApi extends Page {
	var $proposalId;
	var $action;
	var $tranId;

	function __construct($proposalId, $action, $tranId = NULL) {
		$this->proposalId = $proposalId;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		// debugMsg('proposalId '.$this->proposalId.' Action = '.$this->action.' TranId = '.$this->tranId);

		$proposalInfo = is_numeric($this->proposalId) ? ProjectProposalModel::get($this->proposalId) : NULL;
		$this->proposalId = $projectId = $proposalInfo->projectId;
		$tranId = $this->tranId;

		// Public API
		$publicApi = ['objective.info', 'exp.calculate', 'budget.calculate'];
		$checkRight = ['follow.make', 'status.set', 'review.save', 'refno.save'];

		$isRight = $proposalInfo->RIGHT & _IS_RIGHT;
		$isAdmin = $proposalInfo->RIGHT & _IS_ADMIN;
		$isEdit = $proposalInfo->RIGHT & _IS_EDITABLE;

		if (empty($this->proposalId)) {
			return message(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);
		} else if (in_array($this->action, $publicApi)) {
			return $this->_publicApi();
		} else if (in_array($this->action, $checkRight)) {
			// Check right in each case
		} else if (!$isEdit) {
			return message(['code' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);
		}

		$ret = '';

		switch ($this->action) {
			case 'delete':
				if (SG\confirm()) {
					$ret .= 'DELETE!!!';
					$result = ProjectProposalModel::delete($projectId);
					// debugMsg($result, '$result');
				}
				break;

			case 'send':
				$stmt = 'UPDATE %project_dev% SET `status` = 2 WHERE `tpid` = :projectId LIMIT 1';
				mydb::query($stmt, ':projectId', $projectId);
				break;

			case 'docs.upload':
				$data = (Object) post('document');
				$data->tpid = $projectId;
				if ($data->title == 'ไฟล์เอกสาร') $data->title = $_FILES['document']['name'];
				$docFiles = $_FILES['document'];
				$docOptions = (Object) ['removeOldFile' => post('removeOldFile') ? true : false];
				$result = R::Model('doc.upload',$docFiles, $data, $docOptions);

				if ($result->error) header('HTTP/1.0 406 Not Acceptable');
				$ret .= $result->error ? implode(',', $result->error) : 'Upload Completed';

				break;

			case 'docs.delete':
				if ($tranId && SG\confirm()) {
					R::Model('doc.delete', $tranId);
				}
				break;

			case 'area.delete':
				$ret .= 'DELETE';
				if ($tranId && SG\confirm()) {
					$stmt = 'DELETE FROM %project_prov% WHERE `tpid` = :projectId AND `autoid` = :trid AND `tagname` = :tagname LIMIT 1';
					mydb::query($stmt, ':projectId', $projectId, ':trid', $tranId, ':tagname', _PROPOSAL_TAGNAME);
					$ret .= ' COMPLETED';
					//$ret .= mydb()->_query;
				} else {
					$ret .= ' ERROR';
				}
				break;

			case 'area.save':
				if (post('changwat') != '') {
					$data = (object) post();
					$data->tagname = _PROPOSAL_TAGNAME;
					$stmt = 'INSERT INTO %project_prov%
								(`tpid`,`tagname`,`changwat`,`ampur`,`tambon`,`areatype`)
								VALUES
								(:projectId,:tagname,:changwat,:ampur,:tambon,:areatype)';
					mydb::query($stmt,':projectId',$projectId,$data);
					//$ret .= mydb()->_query;
				}
				break;

			case 'problem.edit' :
				$data = new stdClass();
				$data->tpid = $projectId;
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
								(:projectId, :uid, :formid, :part, :problemother, :problemdetail, :problemsize, :created)';
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
								(:projectId, :uid, :refid, :tagname, :formid, :part, :problemother, :problemsize, :created)';
					mydb::query($stmt,$data);
					//$ret .= mydb()->_query;
				}
				//$devInfo = R::Model('project.develop.get',$projectId);
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
				if ($projectId && $tranId && SG\confirm()) {
					$stmt = 'DELETE FROM %project_tr%
						WHERE `tpid` = :projectId AND `trid` = :trid AND `formid` = "develop" AND `part` = "problem"
						LIMIT 1';
					mydb::query($stmt,':projectId',$projectId, ':trid',$tranId);
				}
				break;

			case 'objective.save' :
				$data=new stdClass();
				$data->tpid=$projectId;
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
								(:projectId, 1, :uid, :formid, :part, :objective, :indicator, :problemsize, :targetsize, :created)';
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
								(:projectId, 1, :uid, :refid, :tagname, :formid, :part, :objective, :indicator, :targetsize, :created)';
					mydb::query($stmt,$data);
					//$ret.=mydb()->_query;
				}


				//$ret.=print_o($data,'$data');
				//$ret.=print_o(post(),'post()');
				//location('paper/'.$projectId);
				break;

			case 'objective.remove' :
				if ($tranId && SG\confirm()) {
					mydb::query('DELETE FROM %project_tr% WHERE `tpid` = :projectId AND `trid` = :trid AND `formid` = :tagname AND `part` = "objective" LIMIT 1',':projectId', $projectId, ':tagname', _PROPOSAL_TAGNAME, ':trid', $tranId);
				}
				break;

			case 'target.add':
				$data = new stdClass();
				$data->tpid = $projectId;
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
					(:projectId, :tagname, :targetname, :targetsize)
					ON DUPLICATE KEY UPDATE `amount` = :targetsize';
				mydb::query($stmt, $data);
				break;

			case 'target.delete':
				if (post('id') != '' && SG\confirm()) {
					$stmt = 'DELETE FROM %project_target% WHERE `tpid` = :projectId AND `tgtid` = :tgtid AND `tagname` = "develop" LIMIT 1';
					mydb::query($stmt, ':projectId', $projectId, ':tgtid', post('id'));
					$ret .= 'ลบกลุ่มเป้าหมายเรียบร้อย';
				}
				break;

			case 'plan.save':
				$ret .= 'SAVE PLAN';

				$data = (Object) post('plan');

				if ($data->title) {
					if ($before = $data->before) {
						$sorder = $before;
						// เพิ่มลำดับของกิจกรรมหลัง
						mydb::query('UPDATE %project_tr% SET `sorder`= `sorder`+1 WHERE `tpid` = :projectId AND `formid` = :tagname AND `part` = "activity" AND `sorder` >= :before ORDER BY `sorder` ASC',':projectId',$projectId, ':tagname', _PROPOSAL_TAGNAME ,':before',$before);
					} else {
						$sorder = mydb::select('SELECT MAX(`sorder`) `maxOrder` FROM %project_tr% WHERE `tpid` = :projectId AND `formid` = :tagname AND `part` = "activity" LIMIT 1',':projectId',$projectId, ':tagname', _PROPOSAL_TAGNAME)->maxOrder+1;
					}

					$data->tpid = $projectId;
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
						:projectId, :parent, :sorder, :uid, :tagname , "activity", :fromdate, :todate, :title
						, :detail, :outputoutcome, :otherresource, :orgsupport, :budget, :created
						)';
					mydb::query($stmt, $data);
					$addTrid = mydb()->insert_id;

					//$ret.=mydb()->_query.'<br />';
					//$ret.=print_o($data,'$data');

					// เรียงลำดับกิจกรรมใหม่
					if ($before) {
						mydb::query('SET @n:=0 ;');
						mydb::query('UPDATE %project_tr% SET `sorder`= @n := @n+1 WHERE `tpid`=:projectId AND `formid`=:tagname AND `part`="activity" ORDER BY `sorder` ASC;',':projectId',$projectId, ':tagname',_PROPOSAL_TAGNAME);
					}

					// Calculate total budget
					ProjectProposalModel::calculateBudget($projectId);
				}
				//$ret .= print_o(post(),'post()');
				break;

			case 'plan.remove' :
				if ($tranId && SG\confirm()) {
					// Delete Objective
					$stmt='DELETE FROM %project_tr% WHERE `tpid` = :projectId AND `formid` = :tagname AND `part` = "actobj" AND `refid` = :trid';
					mydb::query($stmt,':projectId',$projectId, ':tagname',_PROPOSAL_TAGNAME, ':trid',$tranId);
					//$ret.=mydb()->_query.'<br />';

					// Delete Expense
					$stmt = 'DELETE FROM %project_tr% WHERE `tpid` = :projectId AND `formid` = :tagname AND `part` = "exptr" AND `parent` = :trid';
					mydb::query($stmt,':projectId',$projectId, ':tagname',_PROPOSAL_TAGNAME, ':trid',$tranId);
					//$ret.=mydb()->_query.'<br />';

					// Delete Plan
					$stmt = 'DELETE FROM %project_tr% WHERE `tpid` = :projectId AND `trid` = :trid AND `formid` = :tagname AND `part` = "activity" LIMIT 1';
					mydb::query($stmt, ':projectId',$projectId, ':tagname',_PROPOSAL_TAGNAME, ':trid',$tranId);
					//$ret.=mydb()->_query.'<br />';

					ProjectProposalModel::calculateBudget($projectId);
				}
				break;

				case 'exp.save':
					if ($projectId && $tranId && post('exp')) {
						//$ret.=print_o(post('exp'),'exp');
						$exp = (Object) post('exp');

						$exp->parent = $tranId;
						if (empty($exp->expid)) $exp->expid = NULL;
						$exp->amt = sg_strip_money($exp->amt);
						$exp->unitprice = sg_strip_money($exp->unitprice);
						$exp->times = sg_strip_money($exp->times);
						$exp->total = sg_strip_money($exp->total);
						$exp->tpid = $projectId;
						$exp->formid = _PROPOSAL_TAGNAME;
						$exp->uid = $exp->modifyby=i()->uid;
						$exp->created = $exp->modified=date('U');

						$stmt = 'INSERT INTO %project_tr%
							(`trid`, `tpid`, `parent`, `gallery`, `formid`, `part`, `num1`, `num2`, `num3`, `num4`, `detail1`, `text1`, `uid`, `created`)
							VALUES
							(:expid, :projectId, :parent, :expcode, :formid,"exptr",:amt,:unitprice,:times,:total,:unitname,:detail,:uid,:created)
							ON DUPLICATE KEY
							UPDATE `gallery` = :expcode, `num1` = :amt, `num2` = :unitprice, `num3` = :times, `num4` = :total, `detail1` = :unitname, `text1` = :detail, `modified` = :modified, `modifyby` = :modifyby';

						mydb::query($stmt,$exp);
						//$ret .= mydb()->_query;

						R::On('project.proposal.change',$projectId,'addexp',array('value'=>'บันทึกค่าใช้จ่ายพัฒนาโครงการ  '.$exp->expcode.'='.$exp->total));

						ProjectProposalModel::calculateExpense($projectId);
						//$ret.=R::Page('project.develop.plan.single',NULL,$projectId,'edit');
						//$proposalInfo=R::Model('project.develop.get',$projectId);
						//$ret.=R::View('project.develop.plan.render', $proposalInfo, $proposalInfo->activity[$tranId], true);
						//$ret.=R::View('project.develop.plan.activity',$proposalInfo,$tranId);
					}
					break;

			case 'owner.add':
				if (post('name')) {
					mydb::query(
						'INSERT INTO %project_tr%
						(`tpid`, `uid`, `formid`, `part`, `detail1`, `detail2`, `detail3`, `created`)
						VALUES
						(:projectId, :uid, "develop", "owner", :name, :phone, :email, :created)',
						[
							':projectId' => $this->proposalId,
							':name' => post('name'),
							':phone' => post('phone'),
							':email' => post('email'),
							':uid' => i()->uid,
							':created' => date('U'),
						]
					);
					// $ret .= mydb()->_query;
				}
				break;

			case 'coorg.add':
				$data = (Object) post('data');
				// Create new Org
				if (!$data->orgId && $data->name) {
					import('model:org.php');
					$result = OrgModel::create($data,'{debug:false}');
					$data->orgId = $result->orgId;
				}

				// Add Co-org
				if ($data->orgId) {
					mydb::query(
						'INSERT INTO %project_tr%
						(`tpid`, `formid`, `part`, `refid`, `uid`, `created`)
						VALUES
						(:projectId, "develop", "coorg", :orgId, :uid, :created)',
						[
							':projectId' => $data->projectId,
							':orgId' => $data->orgId,
							':uid' => i()->uid,
							':created' => date('U'),
						]
					);
				}
				break;

			case 'coorg.remove':
				if ($tranId && SG\confirm()) {
					mydb::query(
						'DELETE FROM %project_tr% WHERE `trid` = :tranId AND `formid` = "develop" AND `part` = "coorg" LIMIT 1',
						[':tranId' => $tranId]
					);
				}
				break;

			case 'tran.remove':
				if ($tranId && SG\confirm()) {
					mydb::query('DELETE FROM %project_tr% WHERE `trid` = :tranId LIMIT 1', [':tranId' => $tranId]);
				}
				break;

			case 'exp.remove' :
				if ($tranId && post('expid') && SG\confirm()) {
					$stmt = 'DELETE FROM %project_tr% WHERE `tpid` = :projectId AND `formid` = :tagname AND `part` = "exptr" AND `trid` = :trid LIMIT 1';

					mydb::query($stmt,':projectId', $projectId, ':tagname', _PROPOSAL_TAGNAME, ':trid', post('expid'));

					ProjectProposalModel::calculateExpense($projectId);
					R::On('project.proposal.change', $projectId);
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

						R::On('project.proposal.change',$projectId);
						$parent=$proposalInfo->activity[$tranId]->parent;
						$proposalInfo=R::Model('project.develop.get',$projectId);
						//$ret.=print_o($activity,'$activity');
						$ret.=R::View('project.develop.plan.detail',$proposalInfo,$parent);
						$ret.=R::View('project.develop.plan.activity',$proposalInfo,$parent);
						return $ret;
					}
				} else {
					$ret.='<h4>ย้ายกิจกรรมไปอยู่ภายใต้กิจกรรมหลักอื่น</h4>';
					$ret.='<h5>กิจกรรม : '.$activity->title.'</h5>';

					$form=new Form('data',url('project/develop/plan/'.$projectId.'/changeparent/'.$tranId),'project-edit-movemainact','sg-form');
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

			// Check Right
			case 'status.set':
				if ($tranId && ($isAdmin || ($isRight && in_array($tranId, [2,5])))) {
					mydb::query(
						'UPDATE %project_dev% SET `status` = :status WHERE `tpid` = :projectId LIMIT 1',
						[':projectId' => $projectId, ':status' => $tranId]
					);

					if (post('rev')) {
						unset($proposalInfo->data['revision']);
						mydb::query(
							'INSERT INTO %bigdata%
							(`keyname`, `keyid`, `fldname`, `flddata`, `created`, `ucreated`)
							VALUES
							("project.develop", :projectId, "revision", :dataJSON, :created, :ucreated)',
							[
								':projectId' => $projectId,
								':dataJSON' => preg_replace('/\r|\n/','\n',json_encode($proposalInfo, JSON_UNESCAPED_UNICODE)),
								':created' => date('U'),
								':ucreated' => i()->uid,
							]
						);
						// debugMsg(mydb()->_query);
					}

					if ($tranId == 2) {
						import('model:project.nxtgen.php');
						ProjectNxtGenModel::createProposalRefNo($this->proposalId);
				}
				}
				break;

			case 'review.save':
				if (!$isAdmin) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);
				$post = (Object) post();
				if ($post->msg) {
					$data = (Object) [
						'keyname' => 'project.develop',
						'fldname' => 'review',
						'fldref' => $post->section,
						'keyid' => $this->proposalId,
						'flddata' => $post->msg,
						'created' => date('U'),
						'ucreated' => i()->uid,
					];
					mydb::query(
						'INSERT INTO %bigdata%
							(`keyname`, `keyid`, `fldname`, `fldref`, `flddata`, `created`, `ucreated`)
						VALUES
							(:keyname, :keyid, :fldname, :fldref, :flddata, :created, :ucreated)',
						$data
					);
					// $ret .= mydb()->_query;

					// Update change time
					// $isCommentator = mydb::select('SELECT `uid` FROM %users% WHERE `uid` = :uid AND `roles`="commentator" LIMIT 1',':uid',i()->uid)->uid;
					// if ($isCommentator) {
					// 	mydb::query('UPDATE %topic% SET `commentsssdate` = :changed WHERE `tpid` = :tpid LIMIT 1',':tpid',$tpid, ':changed', date('Y-m-d H:i:s'));
					// } else {
					// 	mydb::query('UPDATE %topic% SET `commenthsmidate` = :changed WHERE `tpid` = :tpid LIMIT 1',':tpid',$tpid, ':changed', date('Y-m-d H:i:s'));
					// }
				}
				break;

			// For admin only
			case 'follow.make':
				if (!$isAdmin) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);
				$isCreateFollow = R::Model('project.right.develop.createfollow',$proposalInfo);
				$isRemoveProjectData = $isAdmin && post('delproject');

				// Remove all follow data before create follow project
				if ($isRemoveProjectData && SG\confirm()) {
					$stmt = 'DELETE FROM %project% WHERE `tpid` = :projectId LIMIT 1';
					mydb::query($stmt, ':projectId', $projectId);

					$stmt = 'DELETE FROM %bigdata% WHERE `keyname` LIKE "project.info%" AND `keyid` = :projectId';
					mydb::query($stmt, ':projectId', $projectId);

					$stmt = 'DELETE FROM %project_prov% WHERE `tagname` = "info" AND `tpid` = :projectId';
					mydb::query($stmt, ':projectId', $projectId);

					$stmt = 'DELETE FROM %project_tr% WHERE `tpid` = :projectId AND `formid` LIKE "info"';
					mydb::query($stmt, ':projectId', $projectId);

					$stmt = 'DELETE FROM %topic_parent% WHERE `tpid` = :projectId';
					mydb::query($stmt, ':projectId', $projectId);

					$stmt = 'DELETE FROM %project_target% WHERE `tpid` = :projectId AND `tagname` LIKE "info%"';
					mydb::query($stmt, ':projectId', $projectId);

					$stmt = 'DELETE FROM %calendar% WHERE `tpid` = :projectId';
					mydb::query($stmt, ':projectId', $projectId);
				}

				$followReady = mydb::select(
					'SELECT `tpid` `projectId` FROM %project% WHERE `tpid` = :projectId LIMIT 1',
					[':projectId' => $projectId]
				)->projectId;

				if ($followReady) {
					return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'msg' => 'โครงการได้ทำการสร้างเป็นติดตามแล้ว ไม่สามารถสร้างซ้ำได้']);
				} else if ($isCreateFollow && SG\confirm()) {
					$result = ProjectProposalModel::makeFollow($this->proposalId,'{debug: false}');

					import('model:project.nxtgen.php');
					ProjectNxtGenModel::createFollowRefNo($this->proposalId);

					// $ret .= print_o($result, '$result');
				}
				break;

			case 'refno.save':
				if (!$isAdmin) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);
				if (property_exists($proposalInfo->info,'refNo') && empty($proposalInfo->info->refNo)) {
					import('model:format.php');
					$refNo = '';
					// debugMsg('UPDATE REF NO');
					$cfgName = 'refNo.PS';
					// debugMsg(cfg($cfgName));
					$i = 0;
					// $nextNo = FormatModel::nextNo('PROPOSAL', 'PS');
					// break;
					do {
						$nextNo = FormatModel::nextNo('PROPOSAL', 'PS');
						$docShopId = $nextNo->shopId;
						$docFormat = $nextNo->format;
						$refNo = $nextNo->nextNo;
						// debugMsg($nextNo, '$nextNo');

						$isDup = mydb::select(
							'SELECT `refNo` FROM %project_dev% d WHERE d.`refNo` = :refNo LIMIT 1',
							[':refNo' => $refNo]
						)->refNo;


						// debugMsg('$isDup = '.($isDup ? 'duplicate to Recieve no '.$isDup:'not duplicate').'<br />'.mydb()->_query);

						if ($isDup) {
							$nextNo->lastNo = $refNo;
							unset($nextNo->nextNo);
							cfg_db($cfgName, $nextNo);
							// mydb::query(
							// 	'UPDATE %variable% SET `value` = :value WHERE `name` = :name LIMIT 1',
							// 	[':name' => $cfgName, ':value' => json_encode($nextNo)]
							// );
							// debugMsg(mydb()->_query);
						}
						if (++$i > 5) break;
					} while ($isDup);

					$nextNo->lastNo = $refNo;
					unset($nextNo->nextNo);
					cfg_db($cfgName, $nextNo);
					// mydb::query(
					// 	'UPDATE %variable% SET `value` = :value WHERE `name` = :name LIMIT 1',
					// 	[':name' => $cfgName, ':value' => json_encode($nextNo)]
					// );
					// debugMsg(mydb()->_query);

					mydb::query(
						'UPDATE %project_dev% SET `refNo` = :refNo WHERE `tpid` = :projectId',
						[':projectId' => $this->proposalId, ':refNo' => $refNo]
					);
					// debugMsg(mydb()->_query);
					// debugMsg($proposalInfo, '$proposalInfo');
				}
				break;

			default:
				$ret .= 'SORRY!!! NO ACTION';
				break;
		}

		return $ret;
	}

	function _publicApi() {
		$ret = NULL;
		switch ($this->action) {
			case 'objective.info' :
				$ret.='<h4>วัตถุประสงค์</h4>';
				$ret.='<p>'.$info->objective[$tranId]->title.'</p>';
				$ret.='<h4>ตัวชี้วัดความสำเร็จ</h4>';
				$ret.='<p>'.nl2br($info->objective[$tranId]->indicator).'</p>';
				break;

			case 'exp.calculate' :
				$ret .= ProjectProposalModel::calculateExpense($projectId, '{debug: true}');
				$ret .= 'คำนวณค่าใช้จ่ายเรียบร้อย';
				break;

			case 'budget.calculate':
				// Calculate total budget
				ProjectProposalModel::calculateBudget($projectId);
				$ret .= 'คำนวณงบประมาณเรียบร้อย';
				break;

		}
		return $ret;
	}
}
?>