<?php
/**
* Saveup Recieve Money Home
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function saveup_rcv_home($self) {
	R::View('saveup.toolbar',$self,'ใบรับเงิน','rcv');

	$isEdit = user_access('administrator saveups,create saveup content');

	//$loanType = json_decode(cfg('saveup.loan'));
	//$ret .= print_o($loanType,'$loanType');

	//$ret .= '<pre>'.sg_json_encode($loanType, JSON_PRETTY_PRINT).'</pre>';

	$stmt = 'SELECT
					r.*
					, GROUP_CONCAT(DISTINCT CONCAT(m.`firstname`," ",m.`lastname`) SEPARATOR " , ") `name`
					, GROUP_CONCAT(DISTINCT tr.`refno` SEPARATOR " , ") `refno`
					FROM %saveup_rcvmast% r
						LEFT JOIN %saveup_rcvtr% tr USING(`rcvno`)
						LEFT JOIN %saveup_member% m USING(`mid`)
					GROUP BY `rcvno`
					ORDER BY `rcvno` DESC';
	$dbs = mydb::select($stmt);

	$tables = new Table();
	$tables->thead = array('date -rcv'=>'วันที่รับเงิน','เลขที่','สมาชิก','money -total -nowrap'=>'จำนวนเงินรับ','อ้างอิง','ผู้โอนเงิน','amt -transamt -nowrap'=>'จำนวนเงินโอน');
	foreach ($dbs->items as $rs) {
		$ref = '';
		foreach (explode(',', $rs->refno) as $item) {
			$item=trim($item);
			if (substr($item,0,3) == 'LON') $ref .= '<a href="'.url('saveup/loan/view/'.$item).'">'.$item.'</a><br />';
		}

		$tables->rows[] = array(
											sg_date($rs->rcvdate,'ว ดด ปปปป'),
											'<a href="'.url('saveup/rcv/'.$rs->rcvid).'">'.$rs->rcvno.'</a>',
											$rs->name,
											number_format($rs->total,2),
											$ref,
											$rs->transby,
											$rs->transamt !=0 ? number_format($rs->transamt,2) : '',
											'config' => array('class' => $rs->status == 'Cancel' ? '-cancel' : '')
										);
	}
	$ret .= $tables->build();
	//$ret.=print_o($dbs,'$dbs');

	if ($isEdit) {
		$ret .= '<div class="btn-floating -right-bottom">'
			.'<a class="btn -floating -circle48" href="'.url('saveup/rcv/money').'" title="บันทึกการรับเงิน"><i class="icon -addbig -white"></i></a>'
			.'</div>';
	}

	return $ret;
}
?>