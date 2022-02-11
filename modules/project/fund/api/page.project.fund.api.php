<?php
/**
* Project :: Fund API
* Created 2021-09-12
* Modify  2021-09-12
*
* @param Int $orgId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage project/fund/api/{orgId}/{action}[/{tranId}]
*/

$debug = true;

class ProjectFundApi extends Page {
	var $orgId;
	var $action;
	var $tranId;
	var $orgInfo;

	function __construct($orgId, $action, $tranId = NULL) {
		$this->orgId = $orgId;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		$tranId = $this->tranId;
		// debugMsg('orgId '.$this->orgId.' Action = '.$this->action.' TranId = '.$this->tranId);

		$this->orgInfo = $this->orgId ? R::Model('project.fund.get', $this->orgId) : NULL;
		$this->orgId = $orgId = $this->orgInfo->orgId;

		// debugMsg($this->orgInfo, '$orgInfo');

		if (empty($this->orgId)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		// $isAccess = $this->orgInfo->RIGHT & _IS_ACCESS;
		$isEdit = $this->orgInfo->right->edit;

		// if (!$isAccess) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Not Allow']);
		if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);

		$ret = '';

		switch ($this->action) {
			case 'financial.plan.create' :
				$budgetYear = $tranId;
				mydb::query(
					'INSERT INTO %project_fundmoneyplan%
					(`orgId`, `budgetYear`, `uid`, `created`)
					VALUES
					(:orgId, :budgetYear, :uid, :created)
					ON DUPLICATE KEY UPDATE
					`orgId` = :orgId',
					[
						':orgId' => $orgId,
						':budgetYear' => $budgetYear,
						':uid' => i()->uid,
						':created' => date('U'),
					]
				);
				// debugMsg(mydb()->_query);
				// debugMsg(mydb()->insert_id);
				break;

			case 'moneyplan.project.save':
				mydb::query(
					'INSERT INTO %project_tr%
					(`trid`, `tpid`, `refcode`, `formid`, `part`, `uid`, `detail1`, `detail2`, `num1`, `created`)
					VALUES
					(:projectDoId, :planningId, :supportType, "info", "project", :uid, :projectTitle, :orgNameDo, :budget, :created)
					ON DUPLICATE KEY UPDATE
						`refcode` = :supportType
						, `tpid` = :planningId
						, `detail1` = :projectTitle
						, `detail2` = :orgNameDo
						, `num1` = :budget
					',
					[
						':projectDoId' => post('projectDoId'),
						':planningId' => SG\getFirst(post('planningId')),
						':supportType' => post('supportType'),
						':projectTitle' => post('projectTitle'),
						':budget' => sg_strip_money(post('budget')),
						':orgNameDo' => post('orgNameDo'),
						':uid' => i()->uid,
						':created' => date('U'),
					]
				);
				// debugMsg('<pre>'.mydb()->_query.'</pre>');
				// debugMsg(post(),'post()');
				break;

			case 'moneyplan.project.remove':
				if ($tranId && SG\confirm()) {
					mydb::query('DELETE FROM %project_tr% WHERE `trid` = :projectDoId AND `formid` = "info" AND `part` = "project" LIMIT 1', [':projectDoId' => $tranId]);
					// debugMsg(mydb()->_query);
				}
				break;

			case 'moneyplan.income.save':
				if ($budgetYear = $tranId) {
					mydb::query(
						'UPDATE %project_fundmoneyplan% SET
							`openBalance` = :openBalance
							, `incomeNhso` = :incomeNhso
							, `incomeLocal` = :incomeLocal
							, `incomeOther` = :incomeOther
						WHERE `orgId` = :orgId AND `budgetYear` = :budgetYear
						LIMIT 1',
						[
							':orgId' => $orgId,
							':budgetYear' => $budgetYear,
							':openBalance' => sg_strip_money(post('openBalance')),
							':incomeNhso' => sg_strip_money(post('incomeNhso')),
							':incomeLocal' => sg_strip_money(post('incomeLocal')),
							':incomeOther' => sg_strip_money(post('incomeOther')),
						]
					);
				}
				break;

			case 'moneyplan.expense.save':
				if ($budgetYear = $tranId) {
					mydb::query(
						'UPDATE %project_fundmoneyplan% SET
							`budget10_1` = :budget10_1
							, `budget10_2` = :budget10_2
							, `budget10_3` = :budget10_3
							, `budget10_4` = :budget10_4
							, `budget10_5` = :budget10_5
						WHERE `orgId` = :orgId AND `budgetYear` = :budgetYear
						LIMIT 1',
						[
							':orgId' => $orgId,
							':budgetYear' => $budgetYear,
							':budget10_1' => sg_strip_money(post('budget10_1')),
							':budget10_2' => sg_strip_money(post('budget10_2')),
							':budget10_3' => sg_strip_money(post('budget10_3')),
							':budget10_4' => sg_strip_money(post('budget10_4')),
							':budget10_5' => sg_strip_money(post('budget10_5')),
						]
					);
				}
				break;

			default:
				return message(['code' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}
}
?>