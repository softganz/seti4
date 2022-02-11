<?php
/**
* Project API :: Project Follow List
* Created 2021-04-09
* Modify  2021-12-12
*
* @param $_REQUEST title,q,budgetYear,area,fundId,changwat,result,items,p
* @return json
*
* @usage project/nhso/api/follows?budgetYear=2021&title=แมลง+ยุง+มาลาเรีย
*/

import('package:project/fund/models/model.fund.php');

class ProjectNhsoApiFinancialPlan extends Page {
	function build() {
		// sendheader('text/html');

		$getFundArea = post('zone');
		$getChangwat = post('changwat');
		$getBudgetYear = post('budgetYear');
		$getFundId = post('fundId');

		$resultType = SG\getFirst(post('result'), 'json');
		$getItems = post('items');
		$getPage = intval(SG\getFirst(post('p'),1));

		$debug = debug('api');

		$result = (Object) [
			'description' => 'NSHO Financial Plan',
			'params' => (Object) [
				'budgetYear' => $getBudgetYear,
				'zone' => $getFundArea,
				'changwat' =>$getChangwat,
				// 'fundId' => $getFundId,
			],
			'count' => 0,
			'items' => NULL,
		];

		// Prepare Condition
		$items = SG\getFirst($getItems, '*');

		$conditions = (Object) [
			// 'orgId' => ,
			'zone' => $getFundArea,
			'changwat' => $getChangwat,
			'budgetYear' => $getBudgetYear,
		];

		$options = (Object) [
			'debug' => false,
			// 'order' => 'p.`tpid`',
			// 'sort' => 'ASC',
			'items' => '*',
		];

		// debugMsg(FundModel::moneyPlans($conditions, $options),'$moneys');
		foreach (FundModel::moneyPlans($conditions, $options)->items as $item) {
			// debugMsg($item,'$item');
			$result->items[] = (Object) [
				'fundId' => $item->fundId,
				'budgetYear' => $item->budgetYear,
				'openBalance' => floatval($item->openBalance),
				'incomeNhso' => floatval($item->incomeNhso),
				'incomeLocal' => floatval($item->incomeLocal),
				'budget10_1' => floatval($item->budget10_1),
				'budget10_2' => floatval($item->budget10_2),
				'budget10_3' => floatval($item->budget10_3),
				'budget10_4' => floatval($item->budget10_4),
				'budget10_5' => floatval($item->budget10_5),
			];
		};
		$result->count = count($result->items);

		return $result;
	}
}
?>