<?php
/**
* Project Model :: Fund Finance
* Created 2021-09-09
* Modify 	2021-09-09
*
* @param Array $args
* @return Object
*
* @usage new FundFinanceModel([])
* @usage FundFinanceModel::function($conditions, $options)
*/

$debug = true;

class FundFinanceModel {
	function __construct($args = []) {
	}

	// public static function get($id, $options = '{}') {
	// 	$defaults = '{debug: false}';
	// 	$options = SG\json_decode($options, $defaults);
	// 	$debug = $options->debug;

	// 	$result = NULL;

	// 	return $result;
	// }

	// public static function items($conditions, $options = '{}') {
	// 	$defaults = '{debug: false}';
	// 	$options = SG\json_decode($options, $defaults);
	// 	$debug = $options->debug;

	// 	$result = NULL;

	// 	if (is_string($conditions) && preg_match('/^{/',$conditions)) {
	// 		$conditions = SG\json_decode($conditions);
	// 	} else if (is_object($conditions)) {
	// 		//
	// 	} else if (is_array($conditions)) {
	// 		$conditions = (Object) $conditions;
	// 	} else {
	//		$conditions = (Object) ['id' => $conditions];
	// 	}

	// 	return $result;
	// }

	public static function yearRecieveExpense($orgId, $dateEnd = NULL) {
		$rs = mydb::select('SELECT `openbalance`, `openbaldate` FROM %project_fund% WHERE `orgid` = :orgId LIMIT 1', ':orgId', $orgId);

		$dateEnd = SG\getFirst($dateEnd, date('Y-m-d'));
		$currentBudgetYear = sg_budget_year($dateEnd);
		$openBalanceAmount = $rs->openbalance;
		$openBalanceDate = $rs->openbaldate;

		$result = (Object) [
			'open' => (Object)['balance' => $rs->openbalance, 'date' => $rs->openbaldate],
			'close' => (Object)[],
			'currentYear' => (Object)[],
			'previousYear' => (Object)[],
			'allYear' => (Object)[],
		];

		$stmt = 'SELECT
			  LEFT(`glcode`,1) `glGroup`
			, ABS(SUM(`amount`)) `amount`
			FROM %project_gl%
			WHERE `orgid` = :orgId AND `refdate` >= :openBalanceDate AND YEAR(`refdate`)+IF(QUARTER(`refdate`) = 4,1,0) = :year
			GROUP BY `glGroup`;
			-- {key:"glGroup"}';

		$thisYearSum = mydb::select($stmt,':orgId',$orgId, ':openBalanceDate', $openBalanceDate, ':year',$currentBudgetYear)->items;

		$stmt = 'SELECT
			  LEFT(`glcode`,1) `glGroup`
			, ABS(SUM(`amount`)) `amount`
			FROM %project_gl%
			WHERE `orgid` = :orgId AND `refdate` >= :openBalanceDate AND YEAR(`refdate`)+IF(QUARTER(`refdate`) = 4,1,0) = :year - 1
			GROUP BY `glGroup`;
			-- {key:"glGroup"}';

		$prevYearSum = mydb::select($stmt,':orgId',$orgId, ':openBalanceDate', $openBalanceDate, ':year',$currentBudgetYear)->items;

		$stmt = 'SELECT
			  LEFT(`glcode`,1) `glGroup`
			, ABS(SUM(`amount`)) `amount`
			FROM %project_gl%
			WHERE `orgid` = :orgId AND `refdate` >= :openBalanceDate
			GROUP BY `glGroup`;
			-- {key:"glGroup"}';

		$allYearSum = mydb::select($stmt,':orgId',$orgId, ':openBalanceDate', $openBalanceDate)->items;

		$result->allYear = (Object)[
			'assest' => $allYearSum[1]->amount,
			'debt' => $allYearSum[2]->amount,
			'recieve' => $allYearSum[4]->amount,
			'expense' => $allYearSum[5]->amount,
			'openBalance' => $result->open->balance,
			'balance' => $result->open->balance + $allYearSum[4]->amount - $allYearSum[5]->amount,
		];

		$previousYearOpenBalance = $openBalanceAmount + ($result->allYear->recieve - $result->allYear->expense) - ($prevYearSum[4]->amount - $prevYearSum[5]->amount) - ($thisYearSum[4]->amount - $thisYearSum[5]->amount);
		$result->previousYear = (Object)[
			'assest' => $prevYearSum[1]->amount,
			'debt' => $prevYearSum[2]->amount,
			'recieve' => $prevYearSum[4]->amount,
			'expense' => $prevYearSum[5]->amount,
			'openBalance' => $previousYearOpenBalance,
			'balance' => $previousYearOpenBalance + $prevYearSum[4]->amount - $prevYearSum[5]->amount,
		];

		$result->currentYear = (Object)[
			'assest' => $allYearSum[1]->amount,
			'debt' => $allYearSum[2]->amount,
			'recieve' => $thisYearSum[4]->amount,
			'expense' => $thisYearSum[5]->amount,
			'openBalance' => $result->previousYear->balance,
			'balance' => $result->previousYear->balance + ($thisYearSum[4]->amount - $thisYearSum[5]->amount),
		];

		$result->close = (Object)[
			'balance' => $result->allYear->balance,
			'date' => $currentBudgetYear,
			'dateEnd' => $dateEnd,
		];

		// debugMsg($thisYearSum,'$thisYearSum');
		// debugMsg($prevYearSum,'$prevYearSum');
		// debugMsg($allYearSum,'$allYearSum');

		return $result;
	}
}
?>