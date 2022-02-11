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

function r_saveup_rcv_tran_add($rcvInfo, $rcvTran, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$result['title'] = '<b>Update Recieve Transaction of '.$rcvInfo->rcvid.'</b>';

	$tranNo = 0;

	foreach ($rcvTran as $rcvtr) {
		$rcvtr = (object) $rcvtr;

		++$tranNo;

		// Save recieve transaction
		$rcvtr->rcvid = $rcvInfo->rcvid;
		$rcvtr->rcvno = $rcvInfo->rcvno;
		$rcvtr->period = $rcvtr->period ? $rcvtr->period : $rcvInfo->period;
		if (empty($rcvtr->loanno)) $rcvtr->loanno = NULL;
		$stmt = 'INSERT INTO %saveup_rcvtr%
						(`rcvid`, `rcvno`, `refno`, `period`, `mid`, `glcode`, `amt`)
						VALUES
						(:rcvid, :rcvno, :loanno, :period, :mid, :glcode, :amt )';
		mydb::query($stmt, $rcvtr);
		$rcvtr->aid = mydb()->insert_id;

		$result['process #'.$tranNo][] = '<b>SAVE Recieve Transaction #'.$tranNo.'</b>';
		$result['process #'.$tranNo]['data'] = $rcvtr;
		$result['process #'.$tranNo][] = mydb()->_query;

		// Check card
		$glcode = mydb::select('SELECT `gltype`, `card` FROM %saveup_glcode% WHERE `glcode` = :glcode LIMIT 1', ':glcode', $rcvtr->glcode);


		// Save transaction to member card
		if ($glcode->card) {
			$memcard->mid = $rcvtr->mid;
			$memcard->card = $glcode->card;
			$memcard->trno = $rcvtr->aid;
			$memcard->date = $rcvInfo->rcvdate;
			$memcard->refno = $rcvInfo->rcvno;
			switch ($glcode->gltype) {
				case 'ASSEST' : $memcard->amt = -$rcvtr->amt; break;
				case 'DEBT' : $memcard->amt = $rcvtr->amt; break;
				default : $memcard->amt = $rcvtr->amt;
			}
			$stmt = 'INSERT INTO %saveup_memcard%
							(`mid`, `card`, `trno`, `date`, `refno`, `amt`)
							VALUES
							(:mid, :card, :trno, :date, :refno, :amt)';
			mydb::query($stmt,$memcard);
			$result['process #'.$tranNo][] = mydb()->_query;
		}

		// Update loan balance
		if ($rcvtr->loanno AND substr($rcvtr->glcode,0,5) == 'LOAN-') {
			$balanceResult = R::Model('saveup.loan.update.balance', $rcvtr->loanno);
			$result['process #'.$tranNo][] = '<p><b>Calculate Loan Balance of '.$rcvtr->loanno.'</b></p>'.print_o($balanceResult, '$balanceResult');
		}
	}

	if ($rcvTran) R::Model('saveup.rcv.update.total', $rcvInfo->rcvid);

	return $result;
}
?>