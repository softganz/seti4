<?php
/**
 * API for get avalible gl code of member
 * 
 * @param String $mid or $_GET['mid'] : Member Id
 * @param Integer $n or $_GET['n'] or $_POST['n'] : Item for result
 * @param Integer $p or $_GET['p'] or $_POST['p'] : Page for result
 * @return json[{value: glcode, label: desctiption, loanno: Loan Number},...]
 */
function saveup_api_member_glcode($self, $mid = NULL,$n  = NULL, $p = NULL) {
	sendheader('text/html');
	$mid = SG\getFirst($mid,trim(post('mid')));
	$n = intval(SG\getFirst($item,$_GET['n'],$_POST['n'],20));
	$p = intval(SG\getFirst($p,$_GET['p'],$_POST['p'],1));

	if (!user_access('access saveup content')) return '[]';
	if (empty($mid)) return '[]';

	$result=array();


	// Get saving account
	$stmt = 'SELECT `glcode`, `desc` FROM %saveup_glcode% WHERE `parent` = "SAVING" ';

	$dbs = mydb::select($stmt,':name','%'.$name.'%',':lname','%'.$lname.'%');
	
	foreach ($dbs->items as $rs) {
		$result[] = array(
									'value'=>$rs->glcode,
									'label'=>htmlspecialchars($rs->desc)
								);
	}

	// Get loan account
	$stmt = 'SELECT * FROM %saveup_loan% WHERE `mid` = :mid AND `balance` > 0';
	$loanDbs = mydb::select($stmt,':mid',$mid);

	foreach ($loanDbs->items as $rs) {
		$stmt = 'SELECT * FROM %saveup_glcode% WHERE `glcode`=:loan OR `glcode` LIKE :revenue ';
		$loandbs = mydb::select($stmt,':loan',$rs->glcode, ':revenue','REVENUE-'.$rs->glcode.'%');
		//print_o($loandbs,'$loandbs',1);
		foreach ($loandbs->items as $loanrs) {
			unset($payAmt, $feeAmt);
			$desc = $loanrs->desc;
			if ($loanrs->glcode == $rs->glcode) {
				$payAmt = number_format($rs->pay_per_period,2,'.','');
				$desc .= ' (เงินต้น '.number_format($rs->total,2).' บาท คงเหลือ '.number_format($rs->balance,2).' บาท)';
			} else if ($loanrs->glcode == 'REVENUE-'.$rs->glcode.'-10') {
				$payAmt = number_format($rs->balance*$rs->feerate/(100*12),2,'.','');
				$desc .= ' ('.number_format($payAmt,2).' บาท)';
			}
			unset($row);
			$row = array(
								'label'=>$desc,
								'value'=>$loanrs->glcode,
								'loanno'=>$rs->loanno,
							);
			if ($payAmt) $row['pay-amt'] = $payAmt;
			$result[] = $row;
		}
	}


	if (debug('api')) {
		$result[] = array('value'=>'query','label'=>$dbs->_query);
		if ($dbs->_error) $result[]=array('value'=>'error','label'=>$dbs->_error_msg);
		$result[] = array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
	}
	//debugMsg($result,'$result');
	return sg_json_encode($result);
}
?>