<?php
/**
* Model Name
* Created 2019-01-01
* Modify  2019-01-01
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_saveup_loan_get($conditions, $options = '{}') {
	$defaults = '{debug: false, data: "tran"}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['id' => $conditions];
	}

	$stmt = 'SELECT
			l.*
		, CONCAT(m.`firstname`," ",m.`lastname`) `name`
		FROM %saveup_loan% l
			LEFT JOIN %saveup_member% m USING(`mid`)
		WHERE `loanno` = :id LIMIT 1';

	$rs = mydb::select($stmt, $conditions);

	if ($rs->_empty) return $result;

	$result->loanno = $rs->loanno;
	$result->mid = $rs->mid;
	$result->name = $rs->name;
	$result->open_balance = $rs->open_balance;
	$result->paid = $rs->total - $rs->balance;
	$result->balance = $rs->balance;
	$d = sg_date($rs->loandate,'Y-m-d');
	list($yy,$mm,$dd) = explode('-',$d);
	$result->first_pay_month = date('Y-m-d', mktime(0,0,0,$mm+1,1,$yy));
	$result->last_pay_month = date('Y-m-d', mktime(0,0,0,$mm+$rs->period,1,$yy));

	$result->glcode = $rs->glcode;
	$feecode = cfg('saveup.loan.glcode.fee');
	$result->fee_glcode = $feecode[$rs->glcode];

	$result->info = mydb::clearprop($rs);

	if ($options->data == 'tran') {
		$result->trans = array();

		$stmt = 'SELECT
						tr.*
						, m.`rcvdate`
						, g.`desc` `glName`
						FROM %saveup_rcvtr% tr
							LEFT JOIN %saveup_rcvmast% m USING(`rcvno`)
							LEFT JOIN %saveup_glcode% g USING(`glcode`)
						WHERE tr.`refno` = :loanno
						ORDER BY m.`rcvdate` ASC, tr.`aid`';

		$result->trans = mydb::select($stmt,':loanno',$result->loanno)->items;
	}

	return $result;
}
?>