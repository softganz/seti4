<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_social_report_visit_month($self, $orgInfo = NULL) {
	$orgId = $orgInfo->orgid;
	$getMonth = SG\getFirst(post('m'), date('Y-m'));

	R::View('imed.toolbar',$self,'รายงานการเยี่ยมบ้าน','none');

	$isMember = $orgInfo->is->socialtype;
	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isGroupAdmin = $isAdmin  || in_array($isMember,array('ADMIN','MODERATOR'));

	if (!$isGroupAdmin) return message('error', 'Access denied');

	if (!post('f')) {


		$ret .= '<div id="imed-app" class="imed-app">'._NL;


		$headerUi = new Ui();
		//$headerUi->add('<a href=""><i class="icon -material">view_list</i><span class="-hidden">คงเหลือ</span></a>');

		//$ret .= '<header class="header -imed-report"><h3>รายงานการเยี่ยมบ้าน</h3><nav class="nav">'.$headerUi->build().'</header>';


		$ret.='<form method="get" action="'.url('imed/social/'.$orgId.'/report.visit.month').'" class="report-form sg-form -no-print" data-rel="replace:#imed-report-output" style="padding: 0; background-color: transparent;"><input type="hidden" name="f" value="n" />';
		//$ret .= '<h3>รายงานการเยี่ยมบ้าน</h3>';
		$ret.='<div class="form-item">'._NL;

		$provdbs = mydb::select('SELECT DISTINCT `provid`, `provname` FROM %imed_need% n LEFT JOIN %db_person% p USING(`psnid`) LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat` HAVING `provname` IS NOT NULL ORDER BY CONVERT(`provname` USING tis620) ASC');

		$startDate = new DateTime('2019-01-01');
		$endDate = new DateTime();

		$interval = new DateInterval('P1M');

		$period = new DatePeriod($startDate, $interval, $endDate);

		// Start array with current date
		$monthList = [];

		// Add all remaining dates to array
		foreach ($period as $date) {
			array_push($monthList, $date->Format('Y-m'));
		}
		arsort($monthList);

		$ret.='<label for="prov">ประจำเดือน : </label>'._NL.'<select name="m" class="form-select sg-changwat" data-change="submit">'._NL;
		foreach ($monthList as $value) $ret.='<option value="'.$value.'"'.($value == $getMonth?' selected="selected"':'').'>'.sg_date($value.'-01','ดด ปปปป').'</option>'._NL;
		$ret.='</select>'._NL;
		$ret.='</div>';
		$ret.='</form>';
	}

	mydb::where('s.`uid` IN (SELECT `uid` FROM %imed_socialmember% WHERE `orgid` = :orgid OR `orgid` IN (SELECT `orgid` FROM %imed_socialparent% WHERE `parent` = :orgid))', ':orgid', $orgId);
	mydb::where('s.`timedata` BETWEEN :fromdate AND :todate', ':fromdate', sg_date($getMonth.'-01','U'), ':todate', sg_date(strtotime($getMonth.'-01 +1 month'),'U'));

	//$ret .= sg_date(strtotime('2019-06-01 +1 month'),'Y-m-d');
//strtotime("+1 month", $month);

	$stmt = 'SELECT
		s.*
		, u.`name`
		, CONCAT(p.`prename`, p.`name`, " ", p.`lname`) `patientName`
		, p.`house`, p.`village`, p.`tambon`, p.`ampur`, p.`changwat`
		, b.`score`, b.`qt01`, b.`qt01`, b.`qt02`, b.`qt03`, b.`qt04`, b.`qt05`, b.`qt06`, b.`qt07`, b.`qt08`, b.`qt09`, b.`qt10`
		, q.`q9_score`
		, q.`q9_1`, q.`q9_2`, q.`q9_3`, q.`q9_4`, q.`q9_5`, q.`q9_6`, q.`q9_7`, q.`q9_8`, q.`q9_9`
		, p.`house`, p.`village`, p.`tambon`, p.`ampur`, p.`changwat`
		, IFNULL(cosub.`subdistname`,p.`t_tambon`) subdistname
		, IFNULL(codist.`distname`,p.`t_ampur`) `distname`
		, IFNULL(copv.`provname`,p.`t_changwat`) `provname`
		FROM %imed_service% s
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %db_person% p ON p.`psnid` = s.`pid`
			LEFT JOIN %imed_barthel% b USING(`seq`)
			LEFT JOIN %imed_2q9q% q USING(`seq`)
			LEFT JOIN %co_province% copv ON p.`changwat` = copv.`provid`
			LEFT JOIN %co_district% codist ON codist.`distid` = CONCAT(p.`changwat`,p.`ampur`)
			LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid` = CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
		%WHERE%
		ORDER BY s.`timedata` ASC, s.`seq` ASC
		';
	
	$dbs = mydb::select($stmt);

	$ret .= '<div id="imed-report-output">';

	//$ret .= mydb()->_query;

	//$ret .= print_o($dbs,'$dbs');

	$ret .= '<h3 class="-sg-text-center">รายละเอียดรายงานแบบประเมินดูแลผู้สูงอายุปี '.sg_date($getMonth.'-01','ปปปป').'<br /><!-- อสม.เชี่ยวชาญ-->'.$orgInfo->name.' ตำบล ... อำเภอ ... จังหวัด ....<br />ประจำเดือน '.sg_date($getMonth.'-01','ดดด ปปปป').'</h3>';

	$tables = new Table();
	$tables->colgroup = array('no'=>'','name -nowrap'=>'','bp -center'=>'');

	$tables->thead = '<tr><th rowspan="2">ลำดับ</th><th rowspan="2">ชื่อ - สกุล</th><th rowspan="2">ความดัน</th><th colspan="11">1.การประเมินความสามารถในการทำกิจวัตรประจำวัน</th><th colspan="10">2.การประเมินภาวะซึมเศร้า</th></tr>';
	$tables->thead .= '<tr>';
	for ($i=1; $i<=10; $i++) {
		$tables->colgroup['bt'.$i.' -amt'] = '';
		$tables->thead .= '<th>1.'.$i.'</th>';
	}
	$tables->thead .= '<th>ADL</th>';
	$tables->colgroup['adl -amt'] = '';
	for ($i=1; $i<=9; $i++) {
		$tables->colgroup['dp'.$i.' -amt'] = '';
		$tables->thead .= '<th>2.'.$i.'</th>';
	}
	$tables->thead .= '<th>9Q</th>';
	$tables->colgroup['dp -amt'] = '';
	$tables->thead .= '</tr>';

	$no = 0;

	foreach ($dbs->items as $rs) {
		$row = array(
			++$no,
			$rs->patientName,
			$rs->sbp.'/'.$rs->dbp,
		);

		for ($i=1; $i<=10; $i++) $row[] = $rs->{'qt'.str_pad($i,2,'0',STR_PAD_LEFT)};

		$row[] = $rs->score;

		for ($i=1; $i<=9; $i++) $row[] = $rs->{'q9_'.$i};
		$row[] = $rs->q9_score;

		$tables->rows[] = $row;
	}

	$ret .= '<div class="scroll">'.$tables->build().'</div>';

	$ret .= '<h3 class="-sg-text-center">ดำเนินการสำรวจผู้สูงอายุ/ผู้พิการ/ผู้ป่วยติดเตียง/อื่น ๆ</h3>';


	$ret .= '<h3 class="-sg-text-center">แบบรายงานผลการปฏิบัติงาน <!-- อสม.เชี่ยวชาญ --><br />'.$orgInfo->name.' อำเภอ ...<br />ชื่อ อสม.เชี่ยวชาญ ...<br />ปฏิบัติงาน รพ.สต. ...</h3>';
	$tables = new Table();
	$tables->thead = array('no'=>'','date'=>'วันที่', 'name'=>'ผู้พิการ/ผู้สูงอายุที่ได้รับการดูแล/ที่อยู่','detail'=>'การดำเนินการ (ปฏิบัติงาน/แนะนำ/ให้ความรู้)','remark'=>'หมายเหตุ');

	$no = 0;

	foreach ($dbs->items as $rs) {
		$address = SG\implode_address($rs, 'short');
		$tables->rows[] = array(
			++$no,
			sg_date($rs->timedata,'ว ดด ปปปป'),
			'<b class="-nowrap">'.$rs->patientName.'</b><br />'
			.$address,
			'<b>@'.$rs->name.'</b> '.$rs->rx,
			' ',
		);
	}
	$ret .= '<div class="scroll">'.$tables->build().'</div>';

	$ret .= '<style type="text/css">
	h3.-sg-text-center {width: 100%; text-align: center;}
	.scroll {overflow: scroll;}
	</style>';

	if (!post('f')) {
		$ret .= '</div><!-- imed-report-output -->';
	}
	return $ret;
}
?>