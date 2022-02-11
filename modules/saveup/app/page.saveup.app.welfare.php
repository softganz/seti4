<?php
/**
 * Project Application
 *
 * @param Object $topic
 */
function saveup_app_welfare($self) {
	saveup_model::init_app_mainpage($self);
	unset($self->theme->toolbar);

	$mid=post('mid');

	if (!i()->ok && post('act')=='signform') {
		return message('error','access denied');
	}

	if ($month=post('month')) {
		$stmt='SELECT
						  `date`
						, `payfor`
						, `amount`
						, CONCAT(LEFT(`firstname`,3),"..",LEFT(`lastname`,3)) `name`
						FROM %saveup_treat% t
							LEFT JOIN %saveup_member% m USING(`mid`)
						WHERE DATE_FORMAT(`date`,"%Y-%m")=:month
						ORDER BY `date` DESC';
		$dbs=mydb::select($stmt,':month',post('month'));

		$tables = new Table();
		$tables->thead=array('date'=>'เดือน-ปี','center -member'=>'สมาชิก','center -tran'=>'รายการ','money'=>'จำนวนเงิน(บาท)');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
												sg_date($rs->date,'ว ดด ปป'),
												$rs->name,
												$rs->payfor,
												number_format($rs->amount,2));
		}
		$ret.=$tables->build();
		//$ret.=print_o($dbs,'$dbs');

		return $ret;
	}

	$memberInfo=R::Model('saveup.member.getbyuserid',i()->uid);
	$lineTree=R::Model('saveup.line.tree',$memberInfo->mid);

	if ($lineTree) {
		$ret.='<form class="sg-form" action="'.url('saveup/app/welfare').'" data-rel="#primary" style="margin:8px;"><select name="mid" class="form-select -fill" onchange="$(this).parent().submit()"><option value="">==เลือกสมาชิกในสาย===</option>';
		foreach ($lineTree as $item) {
			$ret.='<option value="'.$item->mid.'">'.$item->name.'</option>';
		}
		$ret.='</select></form>';
	}

	if ($mid && !array_key_exists($mid, $lineTree)) $mid='';
	else if (!$mid) $mid=$memberInfo->mid;
	//$ret.=print_o($lineTree,'$meeTree');

	//$ret.=print_o($memberInfo,'$memberInfo');

	if ($mid) $ret.=__saveup_app_welfare_my($mid);
	if (post('mid')) return $ret;

	$ret.='<h3>รายงานเบิกเงินค่ารักษาพยาบาล</h3>';
	$stmt='SELECT
					  YEAR(`date`) `year`
					, SUM(`amount`) `total`
					, COUNT(*) `items`
					FROM %saveup_treat%
					GROUP BY `year`
					ORDER BY `year` ASC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	foreach ($dbs->items as $rs) {
		$tables->rows[] = [
			'string:เดือน'=>(string)($rs->year+543),
			'number:จำนวนเงิน'=>intval($rs->total),
		];
	}

	$ret.='<div id="year-project" class="sg-chart -project" data-chart-type="col" style="height:200px; overflow:auto;"><h3>ค่ารักษาพยาบาลรายปี</h3>'._NL.$tables->build().'</div>';

	head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');

	$stmt='SELECT
					  DATE_FORMAT(`date`,"%Y-%m") `month`
					, SUM(`amount`) `total`
					, COUNT(*) `items`
					FROM %saveup_treat%
					GROUP BY `month`
					ORDER BY `month` DESC';
	$dbs=mydb::select($stmt);

	$ret.='<div class="card">';
	foreach ($dbs->items as $rs) {
		$ret.='<div class="carditem">';
		$ret.='<h3>'.sg_date($rs->month.'-01','ดดด ปปปป').'</h3><p>จำนวน <b>'.$rs->items.'</b> รายการ รวมเงิน <b>'.number_format($rs->total,2).'</b> บาท</p>';
		$ret.='<p><a class="sg-action button -more -fill" href="'.url('saveup/app/welfare',array('month'=>$rs->month)).'" data-rel="#card-info-'.$rs->month.'">รายละเอียด</a></p>';
		$ret.='<div id="card-info-'.$rs->month.'"></div>';
		$ret.='</div>';
	}
	$ret.='</div>';

	//$ret.=print_o($dbs,'$dbs');

	return $ret;
}

function __saveup_app_welfare_my($mid) {
	$memberInfo=R::Model('saveup.member.get',$mid);
	$ret.='<p><b>รายการเบิกค่ารักษาพยาบาล ของ '.$memberInfo->name.'</b></p>';
	$stmt='SELECT
					  *
					FROM %saveup_treat%
					WHERE `mid`=:mid
					ORDER BY `tid` DESC;
					-- {sum:"amount"}';
	$dbs=mydb::select($stmt,':mid',$memberInfo->mid);

	$tables = new Table();
	$tables->addClass('-center');
	$tables->thead=array('วันที่','จำนวนเงิน','รายการ','โรค');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(sg_date($rs->date,'ว ดด ปปปป'),number_format($rs->amount,2),$rs->payfor,$rs->disease);
	}
	$tables->tfoot[]=array('รวมเงิน',number_format($dbs->sum->amount,2),'','');
	$ret.=$tables->build();
	//$ret.=print_o($memberInfo,'$memberInfo');
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>