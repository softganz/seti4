<?php

/**
 * OrgDg report รายชื่อสมาชิกในพื้นที่
 *
 */
function org_report_memberbyarea($self) {
	$prov=post('p');
	$ampur=post('a');
	$tambon=post('t');
	$village=post('v');
	$graphType=strtolower(SG\getFirst($_REQUEST['g'],'pie'));

	$isAdmin=user_access('administrator orgs');
	$isAdd=user_access('create org content');

	$chartTypes=array('bar'=>'BarChart','pie'=>'PieChart','col'=>'ColumnChart','line'=>'LineChart');

	$title='รายชื่อสมาชิกในพื้นที่';

	if ($tambon) $tambon_rs=mydb::select('SELECT * FROM %co_subdistrict% WHERE subdistid=:subdistid LIMIT 1',':subdistid',$prov.$ampur.$tambon);
	if ($ampur) $ampur_rs=mydb::select('SELECT * FROM %co_district% WHERE distid=:distid LIMIT 1',':distid',$prov.$ampur);
	if ($prov) $prov_rs=mydb::select('SELECT * FROM %co_province% WHERE provid=:provid LIMIT 1',':provid',$prov);

	if ($village) $title.=' ม.'.$village;
	if ($tambon_rs->subdistname) $title.=' ต.'.$tambon_rs->subdistname;
	if ($ampur_rs->distname) $title.=' อ.'.$ampur_rs->distname;
	if ($prov_rs->provname) $title.=' จ.'.$prov_rs->provname;

	$self->theme->title=$title;

	$ret.='<form class="report-form" id="report-disease"><input type="hidden" name="g" value="'.$graphType.'" />';
	$ret.='<div class="form-item" style="width:80%;float:left;">';
	$provdbs=mydb::select('SELECT `provid`, `provname` FROM %org_mjoin% m LEFT JOIN %db_person% p USING(`psnid`) LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` GROUP BY `changwat` HAVING `provname` IS NOT NULL ORDER BY `provname`');
	$ret.='<label>จังหวัด : </label><select name="p" id="prov" class="form-select"><option value="">---เลือกจังหวัด---</option>';
	foreach ($provdbs->items as $rs) $ret.='<option value="'.$rs->provid.'"'.($rs->provid==$prov?' selected="selected"':'').'>'.$rs->provname.'</option>';
	$ret.='</select>';
	if ($prov) {
		$stmt='SELECT distid, distname FROM %co_district% cod WHERE SUBSTR(distid,1,2)=:prov GROUP BY distname ASC';
		$ret.='<label> อำเภอ : </label><select name="a" id="ampur" class="form-select"><option value="">---เลือก---</option>';
		foreach (mydb::select($stmt,':prov',$prov)->items as $rs) $ret.='<option value="'.substr($rs->distid,2,2).'"'.(substr($rs->distid,2,2)==$ampur?' selected="selected"':'').'>'.$rs->distname.'</option>';
		$ret.='</select>';
	}
	if ($ampur) {
		$stmt='SELECT subdistid, subdistname FROM %co_subdistrict% p WHERE SUBSTR(subdistid,1,4)=:ampur ORDER BY subdistname';
		$ret.='<label> ตำบล : </label><select name="t" id="tambon" class="form-select"><option value="">---เลือก---</option>';
		foreach (mydb::select($stmt,':ampur',$prov.$ampur)->items as $rs) $ret.='<option value="'.substr($rs->subdistid,4,2).'"'.(substr($rs->subdistid,4,2)==$tambon?' selected="selected"':'').'>'.$rs->subdistname.'</option>';
		$ret.='</select>';
	}
	if ($tambon) {
		$stmt='SELECT villno,CONCAT("หมู่ ",villno," - ",villname) villname FROM %co_village% p WHERE SUBSTR(villid,1,6)=:tambon ORDER BY villno';
		$ret.='<label> หมู่บ้าน : </label><select name="v" id="village" class="form-select"><option value="">---เลือก---</option>';
		foreach (mydb::select($stmt,':tambon',$prov.$ampur.$tambon)->items as $rs) $ret.='<option value="'.$rs->villno.'"'.($rs->villno==$village?' selected="selected"':'').'>'.$rs->villname.'</option>';
		$ret.='</select>';
	}
	$ret.='</div><div class="form-item" style="width:20%;float:right;"><input type="submit" class="button" value="ดูรายงาน" style="display:block;width:100%;font-size:1.3em;" /></div><br clear="all" />'._NL;

	$where=array();
	if ($prov) $where=sg::add_condition($where,'p.`changwat`=:prov','prov',$prov);
	if ($ampur) $where=sg::add_condition($where,'p.`ampur`=:ampur','ampur',$ampur);
	if ($tambon) $where=sg::add_condition($where,'p.`tambon`=:tambon','tambon',$tambon);
	if ($village) $where=sg::add_condition($where,'p.`village`=:village','village',$village);

	$cfg['from']='%org_mjoin% m';
	$cfg['joins'][]='LEFT JOIN %db_person% p USING(`psnid`)';

	if ($tambon) {
		$cfg['label']='p.`ampur`, p.`tambon`, p.`village`, `village`';
	} else if ($ampur) {
		$cfg['label']='p.`ampur`, p.`tambon`, `subdistname`';
		$cfg['joins'][]='LEFT JOIN %co_subdistrict% cot ON cot.`subdistid`=CONCAT(p.`changwat`,p.`ampur`, p.`tambon`)';
	} else if ($prov) {
		$cfg['label']='p.`ampur`, distname';
		$cfg['joins'][]='LEFT JOIN %co_district% cod ON cod.`distid`=CONCAT(p.`changwat`,p.`ampur`)';
	} else {
		$cfg['label']='p.`changwat`, `provname`';
		$cfg['joins'][]='LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`';
	}

	unset($stmt);
	if (!$stmt) {
		$stmt='SELECT p.`changwat`, '.($sql_fields?implode(', ',$sql_fields).', ':'').$cfg['label'].' `label`, COUNT(*) `value`
						FROM '.$cfg['from'].'
							'.implode(_NL,$cfg['joins']).'
						'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
						GROUP BY `label`
						ORDER BY `label` IS NULL, `label` ASC';
	}
	$dbs=mydb::select($stmt,$where['value']);

	$tables = new Table();
	$tables->thead=array('no'=>'', 'title'=>'พื้นที่','amt'=>'จำนวน(คน)');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											++$no,
											'<a href="'.url('org/report/memberbyarea',array('p'=>$rs->changwat,'a'=>$rs->ampur,'t'=>$rs->tambon,'v'=>$rs->village)).'">'.SG\getFirst($rs->label,'ไม่ระบุ').'</a>',
											number_format($rs->value),
												);
		$total+=$rs->value;
	}
	$tables->tfoot[]=array('','','<td align="center">'.number_format($total).'</td>');
	$ret .= $tables->build();

	if ($ampur) {
		$stmt='SELECT
						  m.`psnid`
						, m.`joindate`
						, p.`prename`
						, p.`name`
						, p.`lname`
						, CONCAT(`name`," ",`lname`) fullname
						, p.`phone`
						, p.`email`
						, p.`house`
						, p.`village`
						, cosd.`subdistname`
						, copv.`provname`
						, codist.`distname`
					FROM '.$cfg['from'].'
						'.implode(_NL,$cfg['joins']).'
						LEFT JOIN %co_province% copv ON copv.provid=p.changwat
						LEFT JOIN %co_district% codist ON codist.distid=CONCAT(p.changwat,p.ampur)
						LEFT JOIN %co_subdistrict% cosd ON cosd.subdistid=CONCAT(p.changwat,p.ampur,p.tambon)
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					ORDER BY CONVERT(`name` USING tis620) ASC';
		$nameDbs=mydb::select($stmt,$where['value']);

		$tablesName = new Table();
		$tablesName->thead=array('no'=>'ลำดับ','ชื่อ - สกุล','ที่อยู่-หน่วยงาน','โทรศัพท์','อีเมล์');
		$no=0;
		foreach ($nameDbs->items as $rs) {
			if (!i()->ok || (i()->ok && $rs->uid!=i()->uid)) unset($rs->house,$rs->phone,$rs->email);
			if (!$isAdd) $name=$rs->prename.' '.substr($rs->name,0,9).'... '.substr($rs->lname,0,9).'...';
			else $name=$rs->prename.' '.$rs->fullname;

			$tablesName->rows[]=array(
																++$no,
																trim($name),
																SG\implode_address($rs,'short'),
																$rs->phone,
																$rs->email,
																);
		}
		$ret .= $tablesName->build();
	}

	head('<script type="text/javascript" src="https://www.google.com/jsapi"></script>');

			$ret.='
<style type="text/css">
table.report-summary {width:100%;}
#chart_div {width:70%;height:400px;float:left;}
table.report-summary {width:28%;float:right;}
table.report-summary>tbody>tr>td, table.report-summary>tfoot>tr>td {text-align:center;}
table.report-summary>tbody>tr>td:first-child, table.report-summary>tfoot>tr>td:first-child {text-align:left;}
</style>
<script type="text/javascript">
google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawChart);
function drawChart() {
	var data = google.visualization.arrayToDataTable('.json_encode($pie->items).');
//		var options = {title: "'.$data->title.'",};
	var options = {title: "'.$data->title.'",
	          hAxis: {title: "'.$cfg['thead'][0].'", titleTextStyle: {color: "black"}},
	          vAxis: {title: "'.$cfg['thead'][1].'", minValue: 0},
	          isStacked: '.($_REQUEST['stack']?'true':'false').'
			};
	var chart = new google.visualization.'.$chartTypes[$graphType].'(document.getElementById("chart_div"));
	chart.draw(data, options);
}

$(document).ready(function() {
$(".report-form .toolbar>ul>li>a").click(function() {
	var $this=$(this);
	$(".toolbar>ul>li").removeClass("active");
	$this.parent().addClass("active");
	$("#reporttype").val($this.attr("href").slice(1));
//		notify("Click "+$this.attr("href").slice(1));
	$("#report-disease").submit();
	return false;
});
$("a.right").click(function() {
	$(".report-form .toolbar>ul>li").animate({"left":"-834px"}, "slow");
});
$("a.left").click(function() {
	$(".report-form .toolbar>ul>li").animate({"left":"0px"}, "slow");
});

$("#icd").change(function() {
	notify("Loading...");
	$("#report-disease").submit();
});
$("#prov").change(function() {
	notify("Loading...");
	$("#ampur").val("");
	$("#tambon").val("");
	$("#village").val("");
	$("#report-disease").submit();
});
$("#ampur").change(function() {
	notify("Loading...");
	$("#tambon").val("");
	$("#village").val("");
	$("#report-disease").submit();
});
$("#tambon").change(function() {
	notify("Loading...");
	$("#village").val("");
	$("#report-disease").submit();
});
$("#village").change(function() {
	notify("Loading...");
	$("#report-disease").submit();
});

$("#sidePanel").hover(function() {
	var $this=$(this);
	$this.height("auto").width("auto");
	var height=$this.height()+40;
	$this.height(height);
	$("#panelContent").stop(true, false).animate({"left": "0px"}, 100);
}, function() {
	var $this=$(this);
	$this.height("34px").width("34px");
	$("#panelContent").stop(true, false).animate({"left": "-222px"}, 100);
});
});
</script>';

	return $ret;
}
?>