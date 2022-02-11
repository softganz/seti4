<?php
/**
 * Show loan information
 *
 * @param Record Set $loanInfo
 * @return String
 */
function view_saveup_loan_show($loanInfo) {
	$tables = new Table();
	$tables->addClass('loan-view loan-info');
	$tables->caption='รายละเอียดใบกู้เงิน '.$loanInfo->loanno.' '.$loanInfo->name;
	$tables->thead = array('detail1 -nowrap'=>'','','','detail2 -nowrap'=>'');
	$tables->rows[]=array('เอกสารสัญญากู้ที่','<strong>'.$loanInfo->loanno.'</strong>','ได้รับอนุมัติเงินกู้เป็นจำนวนเงิน','<strong>'.number_format($loanInfo->info->total,2).'</strong> บาท');
	$tables->rows[]=array('อนุมัติเมื่อวันที่',sg_date($loanInfo->info->loandate,'ว ดดด ปปปป'),'ชำระคืนงวดละ',number_format($loanInfo->info->pay_per_period,2).' บาท');
	$tables->rows[]=array('ชื่อสมาชิก','<a href="'.url('saveup/member/view/'.$loanInfo->mid).'">'.$loanInfo->mid.' '.$loanInfo->name.'</a>','ยอดยกมา',number_format($loanInfo->info->open_balance,2).' บาท');
	$tables->rows[]=array('เริ่มชำระเดือน',sg_date($loanInfo->first_pay_month,'ดดด ปปปป'),'ชำระคืน',number_format($loanInfo->paid,2).' บาท');
	$tables->rows[]=array('ครบกำหนดชำระเดือน',sg_date($loanInfo->last_pay_month,'ดดด ปปปป'),'คงค้าง','<strong>'.number_format($loanInfo->balance,2).'</strong> บาท');
	$tables->rows[]=array('อัตราค่าบำรุง',$loanInfo->info->feerate.'% ต่อปี','GL-CODE',$loanInfo->glcode);
	$tables->rows[]=array('กำหนดชำระคืน',$loanInfo->info->period.' งวด');
	if ($loanInfo->info->memo) $tables->rows[]='<tr><td>บันทึก</td><td colspan="2">'.$loanInfo->info->memo.'</td></tr>';

	$ret .= $tables->build();


	/*
	// Get load transaction
	$stmt='SELECT r.*, tr.amt, tr.glcode
					FROM %saveup_rcvmast% r
						LEFT JOIN %saveup_rcvtr% tr USING (`rcvno`)
					WHERE r.`refno`=:loanno AND r.status != "Cancel" AND tr.glcode=:glcode
					ORDER BY rcvno ASC ';

	$stmt='SELECT r.`rcvno`, r.`rcvdate`, r.`status`, tr1.`amt` amt, tr2.`amt` fee
					FROM %saveup_rcvmast% r
						LEFT JOIN %saveup_rcvtr% tr1 ON tr1.`rcvno`=r.`rcvno` AND tr1.`glcode`=:glcode
						LEFT JOIN %saveup_rcvtr% tr2 ON tr2.`rcvno`=r.`rcvno` AND tr2.`glcode` LIKE "REVENUE-%"
					WHERE r.`refno`=:loanno AND r.`status` != "Cancel"
					GROUP BY `rcvno`
					ORDER BY `rcvno` ASC ';

	$stmt = 'SELECT
					tr.*
					, m.`rcvdate`
					FROM %saveup_rcvtr% tr
						LEFT JOIN %saveup_rcvmast% m USING(`rcvno`)
					WHERE tr.`refno` = :loanno
					ORDER BY tr.`rcvno` ASC, tr.`aid`';

	$dbs=mydb::select($stmt,':loanno',$loanInfo->loanno,':glcode',$loanInfo->glcode);
	*/


	$tables = new Table();
	$tables->addClass('loan-view loan-tran');
	$tables->caption = 'บันทึกการรับชำระหนี้';
	$tables->thead = array('no'=>'งวด','rcvdate -date'=>'วันที่รับชำระ','เลขที่รับชำระ','บัญชี','open_balance -money'=>'ยอดยกมา','amount -money'=>'จำนวนเงินชำระ (บาท)','fee -money'=>'ค่าบำรุง/ค่าปรับ (บาท)','balance -money'=>'ยอดคงเหลือ (บาท)');
	$no=$paid=$fee=0;
	$balance=$loanInfo->info->open_balance;

	$tables->rows[]=array('<td></td>',sg_date($loanInfo->info->loandate,cfg('date.format')),'','ยอดยกมา','','','',number_format($balance,2));

	foreach ($loanInfo->trans as $rs) {
		if ($rs->glcode == $loanInfo->glcode) {
			$tranType = 'paid';
			$open_balance = $balance;
			$balance -= $rs->amt;
			$paid += $rs->amt;
			++$no;
		} else {
			$tranType = 'fee';
			$fee += $rs->amt;
			/*
			$tables->rows[]=array(
													'<td></td>',
													sg_date($rs->rcvdate, cfg('date.format')),
													'<a href="'.url('saveup/rcv/view/'.$rs->rcvno).'">'.$rs->rcvno.'</a>',
													'<td colspan="2">'.$rs->glcode.'</td>',
													number_format($rs->amt,2),
													'',
													);
													*/
		}
		$tables->rows[]=array(
												$tranType == 'paid' ? $no : '<td></td>',
												sg_date($rs->rcvdate, cfg('date.format')),
												'<a href="'.url('saveup/rcv/'.$rs->rcvid).'">'.$rs->rcvno.'</a>',
												$rs->glName,
												$tranType == 'paid' ? number_format($open_balance,2) : '',
												$tranType == 'paid' ? number_format($rs->amt,2) : '',
												$tranType == 'fee' ? number_format($rs->amt,2) : '',
												$tranType == 'paid' ? number_format($balance,2) : '',
												);
	}
	$tables->tfoot[]=array('<td></td>','','','รวม','',number_format($paid,2),number_format($fee,2),'<strong>'.number_format($balance,2).'</strong>');
	$ret .= $tables->build();

	//$ret .= print_o($loanInfo,'$loanInfo');
	return $ret;
}
?>