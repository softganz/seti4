<?php
/**
 * View Payment Log
 *
 */
function saveup_payment_list($self, $uid = NULL) {
	if (!(user_access('create saveup content'))) return message('error','access denied');

	$getYear = post('y');

	$isAdmin = is_admin('saveup');

	mydb::where('l.`keyword` = "TRANSFER"');
	if ($uid && ($isAdmin || (i()->ok && i()->uid == $uid))) mydb::where('`uid` = :uid', ':uid', $uid);

	$ui = new Ui();
	$ui->add('<a class="btn" href="'.url('saveup/payment/list', array('y'=>'all')).'">All Year</a>');
	$stmt = 'SELECT DISTINCT FROM_UNIXTIME(l.`created`, "%Y") `year` FROM %saveup_log% l %WHERE% ORDER BY `year` DESC -- {reset: false, max: "year"}';
	$dbs = mydb::select($stmt);
	foreach ($dbs->items as $rs) {
		$ui->add('<a class="btn" href="'.url('saveup/payment/list', array('y'=>$rs->year)).'">พ.ศ.'.($rs->year+543).'</a>');
	}


	R::View('saveup.toolbar',$self,'ระบบงานกลุ่มออมทรัพย์ '.cfg('saveup.version'));
	$ret .= '<nav class="nav -page">'.$ui->build().'</nav>';

	if ($getYear == 'all') $getYear = '';
	else if (empty($getYear)) $getYear = reset($dbs->items)->year;

	if ($getYear) mydb::where('FROM_UNIXTIME(l.`created`, "%Y") = :year', ':year', $getYear);

	//$ret .= 'Year = '.$getYear.print_o($dbs,'$dbs');

	$stmt = 'SELECT
			l.*
		, f.`file`
		FROM %saveup_log% l
			LEFT JOIN %topic_files% f ON f.`tagname` = "saveup_transfer" AND f.`refid` = l.`lid`
		%WHERE%
		ORDER BY l.`lid` DESC';

	$dbs = mydb::select($stmt);
	//$ret.=mydb()->_query;

	$self->theme->title = 'บันทึกการโอนเงิน ('.$dbs->count().' รายการ)';

	$tables = new Table();
	$tables->addClass('saveup -payment-list');
	$tables->thead = [
		'date -date' => 'วันที่แจ้ง',
		'ผู้แจ้งโอน',
		'amt -money' => 'จำนวนเงิน',
		'by -center -nowrap -hover-parent' => 'แจ้งทาง',
	];

	foreach ($dbs->items as $rs) {
		$rs->detail = str_replace("\r",'<br />',$rs->detail);
		$menu = '';
		$ui = new Ui();
		if ($rs->process > 1) {
			$ui->add('<a href="'.url('saveup/rcv/'.$rs->process).'"><i class="icon -material">attach_money</i></a>');
		} else {
			$ui->add('<a href="'.url('saveup/rcv/money', array('payment'=>$rs->lid)).'"><i class="icon -material">money</i></a>');
			$ui->add('<a class="sg-action" href="'.url('saveup/payment/delete/'.$rs->lid).'" data-confirm="ต้องการลบรายการ กรุณายืนยัน?" data-rel="none" data-removeparent="tr"><i class="icon -delete"></i></a>');
		}
		$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
		$tables->rows[] = [
			'<strong>'.sg_date($rs->created,'ว ดด ปปปป H:i').'</strong>',
			'<strong>'.$rs->poster.'</strong>'.($rs->uid?' ('.$rs->uid.')':'')
			.sg_text2html($rs->detail)
			. ($rs->file ? '<a class="sg-action" href="{url:upload/pics/'.$rs->file.'}" data-rel="img"><img src="{url:upload/pics/'.$rs->file.'}" height="160" /></a>' : ''),
			'<strong>'.number_format($rs->amt,2).'</strong>',
			($rs->kid == 1 ? 'Web' : 'App')
			. $menu,
			'config' => array('class' => $rs->process > 0 ? '-recieved' : ''),
		];
		/*
		$tables->rows[]=array(
			'',
			'<td colspan="3">'
			.sg_text2html($rs->detail)
			. ($rs->file ? '<a class="sg-action" href="{url:upload/pics/'.$rs->file.'}" data-rel="img"><img src="{url:upload/pics/'.$rs->file.'}" height="160" /></a>' : '')
			.'</td>'
		);
		*/
	}
	$ret .= $tables->build();
	return $ret;
}
?>