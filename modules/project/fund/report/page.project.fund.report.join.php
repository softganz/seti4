<?php
/**
* Project :: Fund Report Join Program
* Created 2017-06-13
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/report/join
*/

$debug = true;

function project_fund_report_join($self) {
	$area=post('area');
	$prov=post('prov');
	$ampur=post('ampur');

	$repTitle='รายงานสรุปจำนวนองค์กรปกครองส่วนท้องถิ่นที่เข้าใช้งานโปรแกรมฯ';

	R::view('project.toolbar',$self,'รายงาน - '.$repTitle,'fund');

	$ui=new Ui();
	$ui->add('<a class="btn" href="'.url('project/report').'">รายงาน</a>');
	$ui->add('<a class="btn" href="'.url('project/fund/report/join').'">'.$repTitle.'</a>');
	$ret.='<nav class="nav -page">'.$ui->build().'</nav>';

	$form='<form id="condition" action="'.url('project/fund/report/join').'" method="get">';
	$form.='<span>ตัวเลือก </span>';

	// Select area
	$form.='<select id="area" class="form-select" name="area">';
	$form.='<option value="">ทุกเขต</option>';
	$areaList=mydb::select('SELECT `areaid`,`areaname` FROM %project_area% WHERE `areatype`="nhso" ORDER BY `areaid`+0 ASC');
	foreach ($areaList->items as $rs) {
		$form.='<option value="'.$rs->areaid.'" '.($rs->areaid==$area?'selected="selected"':'').'>เขต '.$rs->areaid.' '.$rs->areaname.'</option>';
	}
	$form.='</select>';

	// Select province
	if ($area) {
		$stmt='SELECT DISTINCT f.`changwat`, cop.`provname` FROM %project_fund% f LEFT JOIN %co_province% cop ON cop.`provid`=f.`changwat` WHERE f.`areaid`=:areaid';
		$provList=mydb::select($stmt,':areaid',$area);
		$form.='<select id="province" class="form-select" name="prov">';
		$form.='<option value="">ทุกจังหวัด</option>';
		foreach ($provList->items as $rs) {
			$form.='<option value="'.$rs->changwat.'" '.($rs->changwat==$prov?'selected="selected"':'').'>'.$rs->provname.'</option>';
		}
		$form.='</select>';
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
		$form.='</select>';
	}

	$form.='</form>';

	$ret.=$form;

	$label='CONCAT("เขต ",LPAD(a.areaid,2," ")," ",a.`areaname`)';
	if ($area) {
	 mydb()->where('f.`areaid`=:areaid:',':areaid:',$area);
	 $label='f.`namechangwat`';
	}
	if ($ampur) {
		mydb()->where('f.`changwat`=:prov AND f.`ampur`=:ampur',':prov',$prov,':ampur',$ampur);
		$label='f.`fundname`';
	} else if ($prov) {
		mydb()->where('f.`changwat`=:prov',':prov',$prov);
		$label='f.`nameampur`';
	}

	$stmt="SELECT
					a.`areaid`
				, $label `label`
				, COUNT(f.`areaid`) `totalFund`
				, COUNT(IF(f.`openbalance`>0,1,NULL)) `totalJoin`
				FROM %project_area% a
					LEFT JOIN %project_fund% f USING(`areaid`)
				%WHERE%
				GROUP BY `label`
				ORDER BY CONVERT(`label` USING tis620) ASC
				;
				";

	$dbs=mydb::select($stmt);
	//$ret.=mydb()->_query.'<br />';


	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับ','เขต','amt -all'=>'อปท.ทั้งหมด','amt -join'=>'อปท.ที่เข้าร่วมกองทุน','amt -remain'=>'	คงเหลือยังไม่เข้าร่วม');

	$chartTable = new Table();
	foreach ($dbs->items as $rs) {
		$notJoin=$rs->totalFund-$rs->totalJoin;

		$tables->rows[]=array(
			++$i,
			$rs->label,
			number_format($rs->totalFund),
			number_format($rs->totalJoin),
			number_format($rs->totalFund-$rs->totalJoin),
		);

		$chartTable->rows[]=array(
			'string:label'=>$rs->label,
			'number:อปท.เข้าร่วม'=>$rs->totalJoin,
			'string:อปท.เข้าร่วม:role'=>$rs->totalJoin,
			'number:อปท.ไม่เข้าร่วม'=>$notJoin,
			'string:อปท.ไม่เข้าร่วม:role'=>$notJoin?$notJoin:'',
		);
	}

	$ret.='<div id="fund-join" class="sg-chart -join" data-chart-type="col" data-options=\'{"isStacked":true,"hAxis":{"textStyle":{"fontSize":12}}}\'><h3>แผนภูมิแสดงจำนวน อปท. ที่เข้าใช้งานโปรแกรม</h3>'.$chartTable->build().'</div>';

	//$ret.=$chartTable->build();

	$ret.=$tables->build();
	//$ret.='<p>หมายเหตุ: คลิกที่ชื่อเขตเพื่อดูรายละเอียด</p>';

	//$ret.=print_o($dbs,'$dbs');

	head('googlegraph','<script type="text/javascript" src="https
		://www.gstatic.com/charts/loader.js"></script>');
	$ret.='<style type="text/css">
	.sg-chart {height:400px; overflow:hidden;}
	</style>';

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

	return $ret;
}
?>