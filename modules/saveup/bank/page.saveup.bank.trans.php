<?php
function saveup_bank_trans($self,$mid) {
	R::View('saveup.toolbar',$self,'ธนาคารขยะ','bank');
	$action=post('action');
	$acc=saveup_model::get_member($mid);
	$ret.='<h3>รายการฝาก-ถอน'.($mid?' : <a class="sg-action" href="'.url('saveup/bank/trans/'.$mid).'" data-rel="#saveup-main">'.$acc->firstname.' '.$acc->lastname.'</a>':'').'</h3>';

	if (post('trcode')!=''
		 	// รายการถอน หรือ รายการฝากที่มีจำนวน
			&& ((post('trcode')==0 && post('withdraw')>0)
				|| (post('trcode')>0 && post('amt')>0 && post('unitprice')>0))
		) {
		$post=(object)post(NULL,_TRIM);
		$post->mid=$mid;
		if ($post->trcode==0) {
			$post->total=-abs($post->withdraw);
			$post->amt=0;
			$post->unitprice=0;
		} else {
			$post->total=$post->amt*$post->unitprice;
			$post->amt=abs($post->amt);
			$post->unitprice=abs($post->unitprice);
		}
		$post->created=date('U');
		$post->uid=i()->uid;
		$stmt='INSERT INTO %saveup_westbanktr%
						(`trid`, `trdate`, `mid`, `uid`, `cat_id`, `amt`, `unitprice`, `total`, `created`)
						VALUES (:trid, :trdate, :mid, :uid, :trcode, :amt, :unitprice, :total, :created)
						ON DUPLICATE KEY UPDATE `trdate`=:trdate, `uid`=:uid, `cat_id`=:trcode, `amt`=:amt, `unitprice`=:unitprice, `total`=:total ';
		mydb::query($stmt,$post);
		$ret.=notify('บันทึกรายการฝากเรียบร้อย');
	}

	switch ($action) {
		case 'delete' :
			mydb::query('DELETE FROM %saveup_westbanktr% WHERE `trid`=:trid LIMIT 1',':trid',post('trid'));
			$ret.=notify('ลบรายการเรียบร้อย');
			break;
	}

	$ret.='<form id="saveup-bank-trans-add" method="post" action="'.url('saveup/bank/trans/'.$mid).'"><input type="hidden" name="trid" value="'.$acc->trid.'" /><input type="hidden" name="trdate" id="saveup-bank-trans-date" value="'.SG\getFirst(post('trdate'),date('Y-m-d')).'" />'._NL;

	$tables = new Table();
	$tables->addClass('saveup-bank-trans');
	$tables->thead['date']='วันที่';
	if (!$mid) $tables->thead['account']='ชื่อบัญชี';
	$tables->thead[]='รายการ';
	$tables->thead['amt']='น้ำหนัก';
	$tables->thead['money unitprice']='ราคา<sup title="ราคาต่อหน่วย">?</sup>';
	$tables->thead['money deposit']='ฝาก';
	$tables->thead['money withdraw']='ถอน';
	$tables->thead['money balance']='คงเหลือ';
	$tables->thead['poster']='ผู้บันทึก';
	if ($mid && user_access('administer saveups,create saveup content')) {
		$codes=mydb::select('SELECT * FROM %co_category% WHERE `cat_group`="'._WESTCODE.'" ORDER BY CONVERT (`cat_name` USING tis620) ASC');
		$option.='<optgroup label="รายการถอน"><option value="0">ถอนเงินสด</option></optgroup>';

		$option.='<optgroup label="รายการฝาก-ถอน">';
		foreach ($codes->items as $item) {
			$option.='<option value="'.$item->cat_id.'">'.$item->cat_name.'</option>';
		}
		$option.='</optgroup>';
		$input=array(
												'<input type="text" name="date" class="form-date" data-field="saveup-bank-trans-date" value="'.SG\getFirst(post('date'),date('d/m/Y')).'" />',
												'<select name="trcode" class="form-select"><option value="">==เลือกรายการ==</option>'.$option.'</select>',
												'<input type="text" name="amt" maxlength="7" class="form-text" />',
												'<input type="text" name="unitprice" maxlength="7" class="form-text" />',
												'<input type="text" name="deposit" maxlength="7" class="form-text" autocomplete="off" readonly="readonly" class="disabled" />',
												'<input type="text" name="withdraw" maxlength="7" class="form-text" autocomplete="off" />',
												'<td colspan="2"><input type="submit" class="float" value="บันทึกรายการ" style="width:100%;" /></td>',
												);
	}

	if ($mid) mydb::where('tr.`mid` = :mid',':mid',$mid);

	$stmt = 'SELECT
						tr.*
					, `cat_name`
					, CONCAT(`firstname`, " ", `lastname`) `accname`
					, u.`name` `poster`
					FROM %saveup_westbanktr% tr
						LEFT JOIN %co_category% c USING(`cat_id`)
						LEFT JOIN %users% u USING(`uid`)
						LEFT JOIN %saveup_member% USING(`mid`)
				%WHERE%
				ORDER BY `trdate` ASC, `trid` ASC';

	$dbs = mydb::select($stmt);

	$balance=0;
	$cdate=date('Y-m-d');
	foreach ($dbs->items as $rs) {
		unset($row);
		$balance+=$rs->total;
		$row[]=sg_date($rs->trdate,'ว ดด ปปปป').($rs->trdate>$cdate?'<span class="notify" title="วันที่รายการเกิดขึ้นหลังวันปัจจุบัน">?</span>':'');
		if (!$mid) $row[]='<a class-"sg-action" href="'.url('saveup/bank/trans/'.$rs->mid).'" data-rel="#saveup-main">'.$rs->accname.'</a>';

		if ($rs->cat_id==0) {
			// Withdraw transaction
			$row[]='ถอนเงินสด';
			$row[]='';
			$row[]='';
			$row[]='';
			$row[]=number_format(abs($rs->total),2);
			$totalWithdraw+=abs($rs->total);
		} else {
			// Deposit transaction
			$row[]=$rs->cat_name;
			$row[]=number_format($rs->amt,2);
			$row[]=number_format($rs->unitprice,2);
			$row[]=number_format($rs->total,2);
			$row[]='';
			$totalAmt+=$rs->amt;
			$totalDeposit+=$rs->total;
		}
		$row[]=number_format($balance,2);
		$row[]=$rs->poster;
		if (user_access('administer saveups,create saveup content')) {
			$row[]='<a class="sg-action" href="'.url('saveup/bank/trans/'.$mid,array('action'=>'delete', 'trid'=>$rs->trid)).'" data-rel="#saveup-main" title="ลยรายการ" confirm="ต้องการลบรายการฝากนี้ กรุณายืนยัน.">X</a>';
		}

		$tables->rows[]=$row;

	}

	$tfoot[]='<strong>รายการฝาก-ถอน</strong>';
	if (!$mid) $tfoot[]='';
	$tfoot[]='';
	$tfoot[]='<td align="center"><strong>'.number_format($totalAmt,2).'</strong></td>';
	$tfoot[]='';
	$tfoot[]='<td align="right"><strong>'.number_format($totalDeposit,2).'</strong></td>';
	$tfoot[]='<td align="right"><strong>'.($totalWithdraw!=0 ? number_format($totalWithdraw,2) : '-').'</strong></td>';
	$tfoot[]='<td align="right"><strong>'.number_format($balance,2).'</strong></td>';
	$tfoot[]='';

	$tables->rows[]=$tfoot; //array('<td colspan="9"><strong>รายการฝาก-ถอน</strong></td>');
	if ($input) $tables->rows[]=$input;

	krsort($tables->rows);

//		$tables->tfoot[]=$tfoot;
	$ret .= $tables->build();
	$ret.='</form>'._NL;

//		$ret.=print_o($tables,'$tables');

//$ret.=print_o($dbs,'dbs');
//$ret.=print_o($post,'post');
//$ret.=print_o($acc,'$acc');

	return $ret;
}
?>