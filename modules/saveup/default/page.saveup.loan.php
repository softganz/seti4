<?php
/**
 * saveup_loan class for loan management
 *
 * @package saveup
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2012-02-06
 * @modify 2012-06-21
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
 */

function saveup_loan($self) {
	$getPaided = post('paided');

	R::View('saveup.toolbar',$self,'รายการเงินกู้','loan');

	$isEdit = user_access('administrator saveups,create saveup content');

	if ($getPaided == 'yes') mydb::where('l.`balance` = 0');
	else if ($getPaided == 'all') ;
	else mydb::where('l.`balance` != 0');

	$stmt='SELECT l.*, CONCAT(m.`firstname`," ",m.`lastname`) name, c.`desc`
		FROM %saveup_loan% l
			LEFT JOIN %saveup_member% m USING(`mid`)
			LEFT JOIN %saveup_glcode% c USING(`glcode`)
		%WHERE%
		ORDER BY `loanno` DESC;
		-- {sum: "total,balance"}';

	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('date'=>'วันที่','เลขที่','','สมาชิก','ประเภทเงินกู้','money total'=>'จำนวนเงิน','money balance'=>'คงเหลือ','tool'=>'');

	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			sg_date($rs->loandate, cfg('date.format')).'<br /><em><small>(@'.sg_date($rs->created, cfg('date.format')).')</small></em>',
			'<a href="'.url('saveup/loan/view/'.$rs->loanno).'" title="ดูรายละเอียดใบกู้เงิน">'.$rs->loanno.'</a>',
			'<a href="'.url('saveup/member/view/'.$rs->mid).'" title="ดูรายละเอียดสมาชิก"><img src="'.saveup_model::member_photo($rs->mid).'" class="profile-photo saveup-list-photo" width="46" height="46" /></a>',
			'<a href="'.url('saveup/member/view/'.$rs->mid).'" title="ดูรายละเอียดสมาชิก"><strong>'.$rs->name.'</strong></a>',
			$rs->desc,
			number_format($rs->total,2),
			number_format($rs->balance,2),
			'<a href="'.url('saveup/loan/view/'.$rs->loanno).'" target="_blank" title="ดูรายละเอียดใบกู้เงิน"><i class="icon -viewdoc"></i></a>',
			//.($rs->balance>0?'<a href="'.url('saveup/loan/rcv','id='.$rs->loanno).'">รับชำระเงินกู้</a>':''),
			'config'=>array('class'=>$rs->status=='Cancel'?'cancel':'')
		);
		if ($rs->memo) $tables->rows[]=array('','','','<td colspan="5">'.$rs->memo.'</td>');
	}

	$tables->tfoot[] = array('', '', '', '', '', number_format($dbs->sum->total,2), number_format($dbs->sum->balance, 2), '');

	$ret .= $tables->build();

	//$ret.=print_o($dbs,'$dbs');

	if ($isEdit) {
		$ret .= '<div class="btn-floating -right-bottom">'
			.'<a class="sg-action btn -floating -circle48" href="'.url('saveup/loan/new').'" data-rel="box" title="บันทึกการกู้เงินรายใหม่" data-width="480"><i class="icon -addbig -white"></i></a>'
			.'</div>';
	}

	//$ret .= 'date.format = '.cfg('date.format');
	return $ret;
}
?>