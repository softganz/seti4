<?php
/**
 * Recieve money from loan
 *
 * @return String
 */
function saveup_loan_rcv($self) {
	$loanNo = post('id');

	$loanInfo = R::Model('saveup.loan.get', $loanNo);

	R::View('saveup.toolbar',$self,'บันทึกการรับชำระหนี้ '.$loanNo.' - '.$loanInfo->name,'loan',$loanInfo);

	if ( !$loanInfo ) return $ret.message('error','ไม่มีใบกู้เงินตามที่ระบุ');


	$post = (object) post('rcv');

	if (post('cancel')) {
			location('saveup/loan/view/'.$loanInfo->loanno);
	} else if ($post->amt != '') {
		$post->amt = sg_strip_money($post->amt);
		$post->fee = sg_strip_money($post->fee);
		if (empty($post->rcvdate)) $missing[] = 'ชำระเมื่อวันที่';
		if ($post->amt < 0) $missing[] = 'จำนวนเงิน';
		if ($post->fee < 0) $missing[] = 'ค่าบำรุง';
		if ($post->amt > $loanInfo->balance) $error[] = 'ยอดเงินชำระมากกว่ายอดหนี้คงค้าง';
		if ($missing) $error[] = 'กรุณาป้อนข้อมูลต่อไปในี้ให้ครบถ้วน : '.implode(',',$missing);

		if (!$error) {
			// Save transaction master
			$rcvmast->rcvno = saveup_model::get_next_no('RCV');
			$rcvmast->rcvdate = $post->rcvdate;
			$rcvmast->refno = $loanInfo->loanno;
			$rcvmast->uid = SG\getFirst(i()->uid,'FUNC.NULL');
			$rcvmast->dbac = '';
			$rcvmast->total = $post->amt+$post->fee;
			$rcvmast->created = date('U');
			$rcvmast->memo = $post->memo;
			$stmt = 'INSERT INTO %saveup_rcvmast% (`rcvno`, `rcvdate`, `refno`, `uid`, `dbac`, `total`, `created`, `memo` )
							VALUES
							(:rcvno, :rcvdate, :refno, :uid, :dbac, :total, :created, :memo)';
			mydb::query($stmt,$rcvmast);

			// Save recieve transaction
			$tr->rcvid = mydb()->insert_id;
			$tr->rcvno = $rcvmast->rcvno;
			$tr->refno = $loanInfo->loanno;
			$tr->mid = $loanInfo->mid;
			$tr->glcode = $loanInfo->glcode;
			$tr->amt = $post->amt;
			$stmt = 'INSERT INTO %saveup_rcvtr% (`rcvid`, `rcvno`, `refno`, `mid`, `glcode`, `amt`)
							VALUES
							(:rcvid, :rcvno, :refno, :mid, :glcode, :amt )';
			mydb::query($stmt,$tr);

			// Save fee transaction
			if ($post->fee && $loanInfo->fee_glcode) {
				$tr->amt = $post->fee;
				$tr->glcode = $loanInfo->fee_glcode;
				mydb::query($stmt,$tr);
			}

			// Update loan balance
			$balance = $loanInfo->balance - $post->amt;
			mydb::query('UPDATE %saveup_loan% SET `balance` = :balance WHERE `loanno` = :loanno LIMIT 1',':balance',$balance,':loanno',$loanInfo->loanno);

			// Save member card
			$memcard->mid = $loanInfo->mid;
			$memcard->card = $loanInfo->glcode;
			$memcard->trno = 1;
			$memcard->date = $post->rcvdate;
			$memcard->refno = $rcvmast->rcvno;
			$memcard->amt = -$post->amt;
			$stmt = 'INSERT INTO %saveup_memcard% (`mid`, `card`, `trno`, `date`, `refno`, `amt`) VALUES (:mid, :card, :trno, :date, :refno, :amt)';
			mydb::query($stmt,$memcard);

			location('saveup/loan/view/'.$loanInfo->loanno);
		}
	}

	$ret .= message('error',$error);


	$form = new Form([
		'variable' => 'rcv',
		'action' => url(q(),'id='.$loanNo),
		'id' => 'edit-loan-rcv',
		'class' => 'box',
		'title' => 'บันทึกการรับชำระหนี้',
	]);

	$form->addField(
					'rcvdate',
					array(
						'type' => 'text',
						'label' => 'ชำระเมื่อวันที่',
						'require' => true,
						'value' => htmlspecialchars(SG\getFirst($post->rcvdate,date('Y-m-d'))),
					)
				);

	$form->addField(
					'amt',
					array(
						'type' => 'text',
						'label' => 'จำนวนเงิน (บาท)',
						'class' => '-money',
						'require' => true,
						'autocomplete' => 'OFF',
						'value' => htmlspecialchars(number_format(SG\getFirst($post->amt,$loanInfo->info->pay_per_period),2)),
					)
				);

	if ($loanInfo->fee_glcode) {
		$form->addField(
						'fee',
						array(
							'type' => 'text',
							'label' => 'ค่าบำรุง (บาท)',
							'class' => '-money',
							'require' => true,
							'autocomplete' => 'OFF',
							'value' => htmlspecialchars(number_format(SG\getFirst($post->fee,$loanInfo->balance*$loanInfo->info->feerate/(100*12)),2)),
						)
					);
	}

	$form->addField(
					'memo',
					array(
						'type' => 'textarea',
						'label' => 'หมายเหตุ',
						'class' => '-fill',
						'rows' => 2,
						'value' => htmlspecialchars($post->memo),
					)
				);

	$form->addField(
					'save',
					array(
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
						'container' => '{class: "-sg-text-right"}'
					)
				);

	$ret .= $form->build();
//		$ret.=print_o($loanInfo,'$loanInfo').print_o($post,'$post');

	$ret .= R::View('saveup.loan.show',$loanInfo);

$ret .= '
<script type="text/javascript">
$(document).ready(function() {
$("#edit-rcv-rcvdate").datepicker({
	dateFormat: "yy-mm-dd",
	disabled: false,
	monthNames: thaiMonthName
});
});
</script>';
	return $ret;
}
?>