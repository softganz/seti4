<?php
/**
 * Cancel recieve - cancel recieve and item from remove member card
 *
 * @param String $rcvno
 * @param Array $_POST
 * @param String
 */
function saveup_loan_cancel($self, $loanNo) {
	$loanInfo = R::Model('saveup.loan.get', $loanNo);

	R::View('saveup.toolbar',$self,'ยกเลิกใบกู้เงิน '.$loanNo.' - '.$loanInfo->name,'loan',$loanInfo);

	if ( !$loanInfo ) {
		$ret .= message('error','ไม่มีใบกู้เงินตามที่ระบุ');
	} else if (!user_access('administer saveups','edit own saveup content',$loanInfo->info->uid)) {
		$ret .= message('error','Access denied','saveup');
	} else if ($loanInfo->info->open_balance > $loanInfo->balance) {
		$ret .= message('error','ใบกู้เงินนี้มีการชำระเงินแล้ว ไม่สามารถยกเลิกได้');
	} else if ( $_POST['confirm'] == 'no' ) {
		location('saveup/loan/view/'.$loanNo);
	} else if ( $_POST['confirm'] == 'yes' ) {
		mydb::query('UPDATE %saveup_loan% SET `status` = "Cancel" WHERE loanno=:loanno LIMIT 1',':loanno',$loanNo);
		mydb::query('DELETE FROM %saveup_memcard% WHERE refno = :rcvno',':rcvno',$loanNo);
		saveup_model::log('keyword','CANCEL','status',20,'detail','ใบกู้เงินเลขที่ '.$loanNo.' ถูกยกเลิกโดย '.i()->username.'('.i()->uid.')');
	} else {
		$form = new Form([
			'action' => url(q()),
			'class' => 'saveup-rcv-delete',
			'children' => [
				'confirm' => [
					'type' => 'radio',
					'name' => 'confirm',
					'label' => 'คุณต้องการยกเลิกใบกู้เงินเลขที่ "<strong>'.$loanInfo->loanno.'</strong>" ใช่หรือไม่?',
					'options' => ['no' => 'ไม่ ฉันไม่ต้องการยกเลิก', 'yes' => 'ใช่ ฉันต้องการยกเลิก'],
				],
				'proceed' => [
					'type' => 'button',
					'value' => 'ดำเนินการยกเลิกใบกู้เงิน',
				],
			],
		]);

		$ret .= $form->build();
	}
	$ret .= R::View('saveup.loan.show',$loanInfo);
	return $ret;
}
?>