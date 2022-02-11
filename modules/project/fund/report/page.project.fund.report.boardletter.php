<?php
/**
* Project :: Fund Report Board Letter
* Created 2019-01-29
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/report/boardletter
*/

$debug = true;

function project_fund_report_boardletter($self) {
	$year = SG\getFirst(post('yr'),'2018');
	$area=post('area');
	$prov=post('prov');
	$ampur=post('ampur');
	$export=post('export');
	$getHaveBoard = post('haveboard');

	$repTitle='รายงานการแต่งตั้งกรรมการกองทุน';

	R::view('project.toolbar',$self,'รายงาน - '.$repTitle,'fund');

	$ui=new Ui();
	$ui->add('<a class="btn" href="'.url('project/report').'">รายงาน</a>');
	$ui->add('<a class="btn" href="'.url('project/fund/report/boardletter').'">'.$repTitle.'</a>');
	$ret.='<nav class="nav -page">'.$ui->build().'</nav>';

	$form='<form id="condition" class="report-form" action="'.url('project/fund/report/boardletter').'" method="get">';
	$form.='<span>ตัวเลือก </span>';

	// Select area
	$form.='<select id="area" class="form-select" name="area">';
	$form.='<option value="">ทุกเขต</option>';
	$areaList=mydb::select('SELECT `areaid`,`areaname` FROM %project_area% WHERE `areatype`="nhso" ORDER BY `areaid`+0 ASC');
	foreach ($areaList->items as $rs) {
		$form.='<option value="'.$rs->areaid.'" '.($rs->areaid==$area?'selected="selected"':'').'>เขต '.$rs->areaid.' '.$rs->areaname.'</option>';
	}
	$form.='</select> ';

	// Select province
	if ($area) {
		$stmt='SELECT DISTINCT f.`changwat`, cop.`provname` FROM %project_fund% f LEFT JOIN %co_province% cop ON cop.`provid`=f.`changwat` WHERE f.`areaid`=:areaid';
		$provList=mydb::select($stmt,':areaid',$area);
		$form.='<select id="province" class="form-select" name="prov">';
		$form.='<option value="">ทุกจังหวัด</option>';
		foreach ($provList->items as $rs) {
			$form.='<option value="'.$rs->changwat.'" '.($rs->changwat==$prov?'selected="selected"':'').'>'.$rs->provname.'</option>';
		}
		$form.='</select> ';
	}

	// Select province
	if ($prov) {
		$stmt='SELECT DISTINCT `distid`, `distname` FROM  %co_district% WHERE LEFT(`distid`,2)=:prov';
		$ampurList=mydb::select($stmt,':prov',$prov);
		$form.='<select id="ampur" class="form-select" name="ampur">';
		$form.='<option value="">ทุกอำเภอ</option>';
		foreach ($ampurList->items as $rs) {
			$form.='<option value="'.substr($rs->distid,2).'" '.(substr($rs->distid,2)==$ampur?'selected="selected"':'').'>'.$rs->distname.'</option>';
		}
		$form.='</select> ';
	}

	$form .= '<input type="checkbox" name="haveboard" value="1"'.($getHaveBoard ? 'checked="checked"' : '').'> แต่งตั้ง';
	$form.='<button class="btn -primary -main" value="ดูรายงาน" type="submit"><i class="icon -search -white"></i><span>ดูรายงาน</span></button> '._NL;
	//$form.='<button class="btn" name="export" value="Export" type="submit"><i class="icon -download"></i><span>Export</span></button>'._NL;

	$form.='</form>'._NL;

	$ret.='<nav class="nav -page">'.$form.'</nav>';





	if ($getHaveBoard) {
		mydb::where('b.`series` IS NOT NULL');
	} else {
		mydb::where('b.`series` IS NULL');
	}
	//mydb::where('b.`series` = :year', ':year',$year);

	if ($area) mydb::where('f.`areaid` = :area',':area',$area);
	if ($prov) mydb::where('f.`changwat` = :prov',':prov',$prov);
	if ($ampur) mydb::where('f.`ampur` = :ampur',':ampur',$ampur);

	mydb::value('$YEAR$',$year);
	$stmt = 'SELECT f.`orgid`,l.`trid`,l.`refid`,l.`refcode`,b.`series`,o.`name`
					FROM %project_fund% f
					LEFT JOIN %db_org% o USING(`orgid`)
					LEFT JOIN %project_tr% l ON l.`refid` = f.`orgid` AND l.`part` = "boardletter" AND l.`refcode` = "new"
					LEFT JOIN %org_board% b ON b.`orgid` = f.`orgid` AND b.`series` >= $YEAR$
					%WHERE%
					GROUP BY `orgid`
					ORDER BY `orgid`';

/*
	$stmt = 'SELECT f.`orgid`,l.`trid`,l.`refid`,l.`refcode`,b.`series`,o.`name`
					FROM %project_fund% f
					LEFT JOIN %db_org% o USING(`orgid`)
					LEFT JOIN %project_tr% l ON l.`refid` = f.`orgid` AND l.`part` = "boardletter" AND l.`refcode` = "new"
					LEFT JOIN %org_board% b ON b.`refid` = l.`trid`
					%WHERE%
					GROUP BY `orgid`
					ORDER BY `orgid`';
*/
	/*
	$stmt='SELECT b.*, bp.`name` `boardName`, p.`name` `positionName`, o.`name` `orgName`, o.`shortname`
					FROM %org_board% b
						LEFT JOIN %db_org% o USING(`orgid`)
						LEFT JOIN %project_fund% f USING(`orgid`)
						LEFT JOIN %tag% bp ON bp.`catid`=b.`boardposition` AND bp.`taggroup`="project:board"
						LEFT JOIN %tag% p ON p.`taggroup`="project:boardpos" AND p.`catid`=b.`position`
					%WHERE%
					ORDER BY CONVERT(`orgName` USING tis620) ASC, `boardposition` ASC';
					*/
	$dbs=mydb::select($stmt);
	//$ret.=mydb()->_query;

	$tables=new Table('item -board');
	$tables->thead=array('no'=>'','กองทุน', 'year -amt'=>'ปี');

	$no = 0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			++$no,
			'<a class="" href="'.url('project/fund/'.$rs->orgid.'/board').'" target="_blank">'.$rs->name.'</a>',
			$rs->series ? $rs->series+543 : 'ยังไม่แต่งตั้ง',
		);
	}

	if ($export) {
		die(R::Model('excel.export',$tables,'fundboard-'.$prov.'-'.date('YmdHis').'.xls','{debug:false}'));
		return $ret;
	}

	$ret.=$tables->build();

	//$ret.=print_o($dbs,'$dbs');
	$ret.='<script type="text/javascript">
	$("body").on("change","#condition select", function() {
		var $this=$(this);
		if ($this.attr("name")=="area") {
			$("#province").val("");
			$("#ampur").val("");
		}
		if ($this.attr("name")=="prov") {
			$("#ampur").val("");
		}
		notify("กำลังโหลด");
		console.log($(this).attr("name"))
		$(this).closest("form").submit();
	});
	</script>';
	$ret.='<style type="text/css">
	.nav .sg-upload {display: block; float: left; height:21px; margin:0; }
	.nav .sg-upload .btn {margin:0; }
	.photocard {margin:0; padding:0; list-style-type:none;}
	.photocard>li {height:300px; margin:0 10px 10px 0; float:left; position;relative;}
	.photocard img {height:100%;}
	.photocard .iconset {right:10px; top:10px; z-index:1;}
	</style>';
	return $ret;
}
?>