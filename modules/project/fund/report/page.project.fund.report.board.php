<?php
/**
* Project :: Fund Report Board
* Created 2019-01-29
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/report/board
*/

$debug = true;

function project_fund_report_board($self) {
	$year=post('yr');
	$area=post('area');
	$prov=post('prov');
	$ampur=post('ampur');
	$position=post('pos');
	$export=post('export');

	$repTitle='รายงานรายชื่อกรรมการกองทุน';

	R::view('project.toolbar',$self,'รายงาน - '.$repTitle,'fund');

	$ui=new Ui();
	$ui->add('<a class="btn" href="'.url('project/report').'">รายงาน</a>');
	$ui->add('<a class="btn" href="'.url('project/fund/report/board').'">'.$repTitle.'</a>');
	$ret.='<nav class="nav -page">'.$ui->build().'</nav>';

	$form='<form id="condition" class="report-form" action="'.url('project/fund/report/board').'" method="get">';
	$form.='<span>ตัวเลือก </span>';

	// OptGroup
	$stmt='SELECT t.`catid` `board`, t.`name` `boardName`, p.`catid` `position`, p.`name` `positionName`
					FROM %tag% p
						LEFT JOIN %tag% t ON t.`catid`=p.`catparent` AND t.`taggroup`="project:board"
					WHERE p.`taggroup`="project:boardpos" AND p.`process` IS NOT NULL';
	$sdbs=mydb::select($stmt);
	foreach ($sdbs->items as $rs) $boardList[$rs->boardName][]=$rs;
	//$ret.=print_o($boardList);

	$form.='<select class="form-select" name="pos" style="width: 120px;">';
	$form.='<option value="">==ทุกองค์ประกอบ==</option>';
	foreach ($boardList as $optGrp => $optList) {
		$form.='<optgroup label="'.$optGrp.'">';
		foreach ($optList as $optItem) {
			$form.='<option value="'.$optItem->position.'" '.($optItem->position==$position?' selected="selected"':'').'>&nbsp;&nbsp;'.$optItem->positionName.'</option>';
		}
		$form.='</optgroup>';
	}
	$form.='</select> ';

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

	$form.='<button class="btn -primary -main" value="ดูรายงาน" type="submit"><i class="icon -search -white"></i><span>ดูรายงาน</span></button> '._NL;
	$form.='<button class="btn" name="export" value="Export" type="submit"><i class="icon -download"></i><span>Export</span></button>'._NL;

	$form.='</form>'._NL;

	$ret.='<nav class="nav -page">'.$form.'</nav>';





	if (empty($prov)) {
		$ret.='<p class="notify">กรุณาเลือกเขต/จังหวัด</p>';
	} else {
		$where=array();
		mydb::where('b.`status`=1');
		if ($position) mydb::where('b.`position`=:position',':position',$position);
		if ($area) mydb::where('f.`areaid`=:area',':area',$area);
		if ($prov) mydb::where('f.`changwat`=:prov',':prov',$prov);
		if ($ampur) mydb::where('f.`ampur`=:ampur',':ampur',$ampur);

		$stmt='SELECT b.*, bp.`name` `boardName`, p.`name` `positionName`, o.`orgid`, o.`name` `orgName`, o.`shortname`
						FROM %org_board% b
							LEFT JOIN %db_org% o USING(`orgid`)
							LEFT JOIN %project_fund% f USING(`orgid`)
							LEFT JOIN %tag% bp ON bp.`catid`=b.`boardposition` AND bp.`taggroup`="project:board"
							LEFT JOIN %tag% p ON p.`taggroup`="project:boardpos" AND p.`catid`=b.`position`
						%WHERE%
						ORDER BY CONVERT(`orgName` USING tis620) ASC, `boardposition` ASC';
		$dbs=mydb::select($stmt,':orgid',$fundInfo->orgid);
		//$ret.=mydb()->_query;

		$tables=new table('item -board');
		$tables->thead=array('กองทุน','ชื่อ-นามสกุล','ตำแหน่ง','องค์ประกอบของคณะกรรมการ','date'=>'เมื่อวันที่');

		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				$export?$rs->orgName:'<a href="'.url('project/fund/'.$rs->orgid.'/board').'" target="_blank">'.$rs->orgName.'</a>',
				$rs->prename.$rs->name,
				$rs->boardName,
				$rs->positionName
				.($rs->posno>=1 ? ' คนที่  '.$rs->posno : '')
				.($rs->fromorg ? '<br />'.$rs->fromorg : ''),
				$rs->datein?sg_date($rs->datein,'ว ดด ปปปป'):'',
			);
		}

		if ($export) {
			die(R::Model('excel.export',$tables,'fundboard-'.$prov.'-'.date('YmdHis').'.xls','{debug:false}'));
			return $ret;
		}

		$ret.=$tables->build();
	}

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