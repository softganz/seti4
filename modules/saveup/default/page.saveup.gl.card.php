<?php
/**
* Saveup Member Card
* Created 2017-04-08
* Modify  2019-05-20
*
* @param Object $self
* @param Int $memberId
* @param String $card
* @return String
*/

$debug = true;

function saveup_gl_card($self, $memberId = NULL, $card = NULL) {
	$memberInfo = saveup_model::get_user_detail($memberId);

	$self->theme->title = $memberId.' : '.$dbs->items[0]->name.' - สมุดคุมสมาชิก';

	R::View('saveup.toolbar',$self,$memberInfo->mid.' : '.$memberInfo->firstname.' '.$memberInfo->lastname,'member',$memberInfo);

	$stmt = 'SELECT DISTINCT c.`card`, c.`desc`
					FROM %saveup_memcard% m
						LEFT JOIN %saveup_glcode% c USING(`card`)
					WHERE m.`mid` = :mid';
	$cardlist = mydb::select($stmt, ':mid', $memberId);


	$ui = new Ui();
	foreach ($cardlist->items as $citem) {
		$ui->add( '<a class="btn" href="'.url('saveup/gl/card/'.$memberId.'/'.$citem->card).'">'.$citem->desc.'</a>');
	}
	$ui->add('<a class="btn" href="'.url('saveup/gl/card/'.$memberId.'/expense').'">เบิกสวัสดิการ</a>');
	$ret .= '<nav class="nav -page">'.$ui->build().'</nav>';

	if ($card == 'expense') {
		$_REQUEST['mid'] = $memberId;
		$_REQUEST['i'] = '*';
		return $ret.R::Page('saveup.treat.list', NULL,$memberId);
	}

	mydb::where('m.`mid` = :mid', ':mid', $memberId);
	if ($card) mydb::where('mc.`card` = :card', ':card', $card);
	$stmt='SELECT
						m.`mid`
					, CONCAT(m.`firstname`," ",m.`lastname`) `name`
					, mc.`date`, mc.`refno`, mc.`trno`
					, mc.`card`, glc.`desc`, mc.`amt`
					, r.`rcvid`
					FROM %saveup_member% m
						LEFT JOIN %saveup_memcard% mc USING(`mid`)
						LEFT JOIN %saveup_glcode% glc ON glc.`card` = mc.`card`
						LEFT JOIN %saveup_rcvmast% r ON r.`rcvno` = mc.`refno`
					%WHERE%
					ORDER BY mc.`date` ASC';
	$dbs=mydb::select($stmt);


	$tables = new Table();
	$tables->thead=array('date'=>'วันที่','อ้างอิง','card','รายการ','dr -money'=>'เดบิต','cr -money'=>'เครดิต');
	foreach ($dbs->items as $rs) {
		if ($card && $card != $rs->card) continue;
		$tables->rows[]=array(
											$rs->date ? sg_date($rs->date,'ว ดด ปปปป') : '',
											$rs->rcvid ? '<a href="'.url('saveup/rcv/'.$rs->rcvid).'" title="รายการที่ '.$rs->trno.'">'.$rs->refno.'</a>' : $rs->refno,
											$rs->card,
											$rs->desc.(empty($rs->trno) ? ' - ยอดยกมา' : ''),
											$rs->amt >= 0 ? number_format($rs->amt,2) : '',
											$rs->amt < 0 ? number_format(abs($rs->amt),2) : ''
										);
		if ($rs->amt >= 0) $totaldr += $rs->amt;
		if ($rs->amt < 0) $totalcr += abs($rs->amt);
	}
	$tables->rows[]=array('','','','','<strong>'.number_format($totaldr,2).'</strong>','<strong>'.number_format($totalcr,2).'</strong>');

	$ret .= $tables->build();

	//$ret .= print_o($dbs, '$dbs');

	return $ret;
}
?>