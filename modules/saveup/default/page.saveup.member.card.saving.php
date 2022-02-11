<?php
/**
* Saveup View Member Saving Card
* Created 2019-05-28
* Modify  2019-05-28
*
* @param Object $self
* @param String $memberId
* @return String
*/

$debug = true;

function saveup_member_card_saving($self, $memberId) {
	$memberInfo = is_object($memberId) ? $memberId : R::Model('saveup.member.get',$memberId);
	$memberId = $memberInfo->mid;

	$getGlCode = SG\getFirst(post('gl'),'SAVING-DEP');


	if (!$memberInfo) return message('error',$self->theme->title='Member id '.$memberId.' not found.');

	$isEdit = user_access('administrator saveups,create saveup content');

	R::View('saveup.toolbar',$self,$memberId.' : '.$memberInfo->info->firstname.' '.$memberInfo->info->lastname,'member',$memberInfo);

	$ui = new Ui();
	$ui->add('<a class="sg-action" href="'.url('saveup/member/'.$memberId.'/balance', array('gl'=>$getGlCode)).'" data-rel="box" data-width="800" data-height="640" title="บันทึกยอดยกมา"><i class="icon -material">account_balance</i><span class="-hidden">ยอดยกมา</span></a>');

	$ret .= '<header class="header -box"><h3>สมุดคุมยอด</h3><nav class="nav">'.$ui->build().'</nav></header>';

	mydb::where('m.`mid` = :mid', ':mid', $memberId);
	if ($getGlCode) mydb::where('mc.`card` IN ( :card )', ':card', $getGlCode);
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

	//$ret .= print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->thead=array('date'=>'วันที่','อ้างอิง','card','รายการ','dr -money'=>'เดบิต','cr -money'=>'เครดิต');
	foreach ($dbs->items as $rs) {
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

	return $ret;
}
?>