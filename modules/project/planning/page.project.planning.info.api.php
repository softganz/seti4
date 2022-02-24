<?php
/**
* Project API :: Planning API
* Created 2021-06-28
* Modify  2021-09-28
*
* @param Int $projectId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage project/planning/api/{id}/{action}[/{tranId}]
*/

$debug = true;

import('model:project.planning.php');

class ProjectPlanningInfoApi extends Page {
	var $projectId;
	var $action;
	var $tranId;
	var $planningInfo;

	function __construct($projectId = NULL, $action = NULL, $tranId = NULL) {
		$this->planningInfo = ProjectPlanningModel::get($projectId, '{initTemplate: true}');
		$this->projectId = $projectId;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		$projectId = $this->projectId;
		$tranId = $this->tranId;

		$isEdit = $this->planningInfo->RIGHT & _IS_EDITABLE;

		if (!$projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);
		else if (!$isEdit) return new ErrorMessage(['code' => _HTTP_ERROR_UNAUTHORIZED, 'text' => 'ACCESS DENIED']);

		$ret = '';

		switch ($this->action) {

			case 'delete':
				if ($isEdit) {
					if ($projectId AND SG\confirm()) {
						import('model:project.php');
						// Delete on no child project
						$stmt='SELECT * FROM %project% WHERE `projectset`=:projectset LIMIT 1';
						$childProject=mydb::select($stmt,':projectset',$projectId);

						if ($childProject->_empty) {
							$result = ProjectModel::delete($projectId);
							//$ret.=print_o($result,'$result');
						} else {
							$ret.=message('error','แผนงานมีโครงการย่อย ไม่สามารถลบทิ้งได้');
						}
					}
				} else {
					$ret.=message('error','access denied');
				}
				break;

			case 'addtr':
				if ($isEdit && $tranId) {
					$data = (Object) [
						'tpid' => $tpid,
						'refid' => NULL,
						'formid' => 'info',
						'part' => $tranId,
						'uid' => i()->uid,
						'created' => date('U'),
					];
					mydb::query(
						'INSERT INTO %project_tr%
						(`tpid`,`refid`,`formid`,`part`,`uid`,`created`)
						VALUES
						(:tpid,:refid,:formid,:part,:uid,:created)',
						$data
					);
				}
				break;

			case 'removetr':
				if ($isEdit && $tranId && SG\confirm()) {
					$stmt = 'DELETE FROM %project_tr% WHERE `tpid`=:tpid AND `trid`=:trid LIMIT 1';
					mydb::query($stmt,':tpid',$projectId, ':trid',$tranId);
				}
				$ret .= 'Remove complete';
				break;

			case 'problem.save':
				if (post('problem')) {
					$data = (Object) post('problem');
					$data->trid = $tranId ? $tranId : NULL;
					$data->tpid = $projectId;
					$data->refid = SG\getFirst($data->refid);
					$data->formid = 'info';
					$data->part = 'problem';
					$data->detailproblem = SG\getFirst($data->detailproblem);
					$data->detailobjective = SG\getFirst($data->detailobjective);
					$data->uid = $data->modifyby = i()->uid;
					$data->created = $data->modified = date('U');
					/*
						, o.`refid`
						, o.`detail1` `problem`
						, o.`text1` `detailproblem`
						, o.`detail2` `objective`
						, o.`text2` `detailobjective`
						, o.`text3` `indicator`
						, o.`num1` `problemsize`
						, o.`num2` `targetsize`
						, o.`uid`, o.`created`, o.`modified`, o.`modifyby`
					*/
					$stmt = 'INSERT INTO %project_tr%
						(`trid`, `tpid`, `refid`
						, `formid`, `part`
						, `detail1`, `text1`
						, `detail2`, `text2`, `text3`
						, `num1`, `num2`
						, `uid`, `created`)
						VALUES
						(
						:trid, :tpid, :refid
						, :formid, :part
						, :problemname, :detailproblem
						, :objective, :detailobjective, :indicator
						, :problemsize, :targetsize
						, :uid, :created
						)
						ON DUPLICATE KEY UPDATE
						`detail1` = :problemname
						, `modified` = :modified
						, `modifyby` = :modifyby
						';

					mydb::query($stmt, $data);
					$ret .= 'บันทึกข้อมูลสถานการณ์เรียบร้อย';
					//$ret .= mydb()->_query;
				}
				//$ret .= print_o(post(), 'post()');
				break;

			case 'project.save':
				mydb::query(
					'INSERT INTO %project_tr%
					(`trid`, `tpid`, `refcode`, `formid`, `part`, `uid`, `detail1`, `detail2`, `num1`, `created`)
					VALUES
					(:trid, :tpid, :supportType, "info", "project", :uid, :title, :orgnamedo, :budget, :created)',
					[
						':trid' => $tranId,
						':tpid' => $projectId,
						':supportType' => post('supportType'),
						':title' => post('title'),
						':budget' => sg_strip_money(post('budget')),
						':uid' => i()->uid,
						':orgnamedo' => post('orgnamedo'),
						':created' => date('U'),
					]
				);
				// debugMsg(mydb()->_query);
				// debugMsg(post(),'post()');
				break;

			case 'makedev' :
				$refId = post('refid');
				if ($refId) {
					$title = mydb::select('SELECT `detail1` FROM  %project_tr% WHERE `trid` = :trid LIMIT 1', ':trid', $refId)->detail1;
				} else {
					$title = post('title');
				}

				if ($isEdit && $title) {
					// Prepare data
					$data = new stdClass();
					$data->title = $title;
					$data->budget = post('budget');
					$data->created = post('created') ? sg_date(post('created'),'Y-m-d') : NULL;
					$data->date_approve = post('date_approve') ? sg_date(post('date_approve'),'U') : NULL;
					if (post('year')) {
						$data->pryear = post('year');
					} else if ($data->date_approve) {
						$data->pryear = sg_date($data->date_approve,'Y')+(sg_date($data->date_approve,'m') >= 10 ? 1 : 0);
					}

					// Start create project development
					$result = R::Model('project.develop.create',$data);

					// Create complete
					if ($result->tpid) {
						if ($refId) {
							$stmt = 'UPDATE %project_tr% SET `refid` = :tpid WHERE `trid` = :refid LIMIT 1';
							mydb::query($stmt, ':tpid', $result->tpid, ':refid', $refId);
							//$ret.=mydb()->_query;
						}
						if (post('group')) {
							$stmt = 'INSERT INTO %project_tr% SET `tpid` = :tpid, `formid`="develop", `part`="supportplan", `refid` = :refid, `uid` = :uid , `created` = :created';
							mydb::query($stmt, ':tpid', $result->tpid, ':refid', post('group'), ':uid', i()->uid, ':created', date('U'));
							//$ret.=mydb()->_query;
						}
						location('project/develop/'.$result->tpid.'/edit');
					}
					//$ret.=print_o($result,'$result');
					//$ret.=print_o(post(),'post()');
					//$ret.=print_o($data,'$data');
					//$ret.=print_o($fundInfo,'$fundInfo');
				}
				break;

			default:
				$ret .= 'NO ACTION';
				break;
		}

		return $ret;
	}
}
?>