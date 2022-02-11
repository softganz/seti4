<?php
function saveup_payment_trans($self, $uid = NULL) {
	$isEdit = user_access('create saveup content');

	if ($uid && ($isEdit || $uid==i()->uid)) {
		$ret.='<h3>บันทึกการโอนเงิน</h3>';

		$stmt='SELECT
						l.*
					, f.`file`
					FROM %saveup_log% l
						LEFT JOIN %topic_files% f ON f.`tagname` = "saveup_transfer" AND f.`refid` = l.`lid`
					WHERE l.`keyword` = "TRANSFER" AND l.`uid` = :uid
					ORDER BY l.`lid` DESC';
		$dbs=mydb::select($stmt,':uid',$uid);
		//$ret .= mydb()->_query;

		$tables = new Table();
		$tables->thead=array('date date'=>'วันที่แจ้ง','ผู้แจ้งโอน','amt'=>'จำนวนเงิน','center -from'=>'ทาง');
		foreach ($dbs->items as $rs) {
			$rs->detail=str_replace("\r",'<br />',$rs->detail);
			$tables->rows[]=array(
													'<strong>'.sg_date($rs->created,'ว ดด ปป H:i').'</strong>',
													'<strong>'.$rs->poster.'</strong>',
													'<strong>'.number_format($rs->amt,2).'</strong>',
													$rs->kid==1?'Web':'App',
													);
			$tables->rows[]=array(
												'<td colspan="4">'
												.sg_text2html($rs->detail)
												. ($rs->file ? '<a class="sg-action" href="{url:upload/pics/'.$rs->file.'}" data-rel="img"><img src="{url:upload/pics/'.$rs->file.'}" height="160" /></a>' : '')
												.'</td>'
											);
		}
		$ret .= $tables->build();
	}
	return $ret;
}
?>