<?php
function saveup_bank_member($self) {
	R::View('saveup.toolbar',$self,'ธนาคารขยะ','bank');
	$ret.='<h3>รายชื่อบัญชี</h3>';
	if (user_access('administer saveups,create saveup content')) {
			$ret.='<div class="btn-floating -right-bottom">';
		$ret.='<a class="btn -floating -circle48" href="'.url('saveup/bank/addmember').'"><i class="icon -addbig -white"></i></a>';
		$ret.='</div>';
	}

	$where=array();
	if (post('q')) {
		list($firstname,$lastname)=sg::explode_name(' ',post('q'));
		if ($firstname && $lastname) {
			mydb::where('m.`firstname` LIKE :firstname AND m.`lastname` LIKE :lastname ', ':firstname','%'.$firstname.'%', ':lastname','%'.$lastname.'%');
		} else {
			mydb::where('m.`mid` LIKE :q OR m.`firstname` LIKE :q ',':q','%'.post('q').'%');
		}
	}

	$stmt='SELECT m.*,
						SUM(IF(`total`>0,`total`,0)) deposit,
						SUM(IF(`total`<0,`total`,0)) withdraw
					FROM %saveup_member% m
						LEFT JOIN %saveup_westbanktr% tr USING(`mid`)
					%WHERE%
					GROUP BY m.`mid`
					ORDER BY `mid` DESC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('หมายเลขบัญชี','ชื่อบัญชี','money deposit'=>'ฝาก','money withdraw'=>'ถอน','money balance'=>'คงเหลือ','date'=>'วันที่สมัคร');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			$rs->mid,
			'<a class="sg-action" href="'.url('saveup/bank/trans/'.$rs->mid).'" data-rel="saveup-main">'.$rs->prename.' '.$rs->firstname.' '.$rs->lastname.'</a>',
			$rs->deposit!=0 ? number_format($rs->deposit,2) : '-',
			$rs->withdraw!=0 ? number_format(abs($rs->withdraw),2) : '-',
			$rs->deposit==0 && $rs->withdraw==0 ? '-' : '<strong>'.number_format($rs->deposit+$rs->withdraw,2).'</strong>',
			sg_date($rs->date_regist,'ว ดด ปปปป')
		);
		$deposit+=$rs->deposit;
		$withdraw+=$rs->withdraw;
	}
	$tables->tfoot[]=array(
		'',
		'',
		'<td align="right">'.number_format($deposit,2).'</td>',
		'<td align="right">'.number_format(abs($withdraw),2).'</td>',
		'<td align="right">'.number_format($deposit+$withdraw,2).'</td>',
		''
	);
	$ret .= $tables->build();

	return $ret;
}
?>