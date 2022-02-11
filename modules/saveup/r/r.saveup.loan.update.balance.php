<?php
/**
* Update Loan Balance
* Created 2019-05-18
* Modify  2019-08-18
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_saveup_loan_update_balance($conditions, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['id' => $conditions];
	}

	$id = $conditions->id;

	$loanInfo = R::Model('saveup.loan.get', $id);
	//debugMsg($loanInfo, '$loanInfo');

	$balance = $loanInfo->open_balance;

	$result['title'] = '<b>Calculate Loan Balance of '.$loanInfo->loanno.'</b>';
	$result['open_balance'] = $balance;
	$result['balance'] = $loanInfo->balance;

	$result['process'][] = 'Open balance = '.number_format($balance, 2);

	if ($loanInfo->glcode) {
		foreach ($loanInfo->trans as $item) {
			if ($item->glcode != $loanInfo->glcode) continue;
			$balance -= $item->amt;
			$result['process'][] = 'Paid of '.$item->aid.'-'.$item->glcode.' amount '.number_format($item->amt,2).' balance = '.number_format($balance,2);
		}
	}

	$result['process'][] = 'Balance = '.number_format($balance, 2);

	$stmt = 'UPDATE %saveup_loan% SET `balance` = :balance WHERE `loanno` = :loanno LIMIT 1';
	mydb::query($stmt, ':balance', $balance, ':loanno', $loanInfo->loanno);

	$result['query'][] = mydb()->_query;

	$result['balance'] = $balance;

	$result['para'] = $conditions;
	//$result['query'][] =
	return $result;
}
?>