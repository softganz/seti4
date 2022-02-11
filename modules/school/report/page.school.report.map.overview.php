<?php
function school_report_map_overview($self) {
	$situation=SG\getFirst(post('situation'),1);
	$zone=post('zone');
	$province=post('province');
	$school=post('school');
	$year=SG\getFirst(post('year'),2015);
	list($term,$period)=explode(':',SG\getFirst(post('term'),'1:1'));

	$situationList=array(1=>'ภาวะเตี้ย',2=>'ภาวะผอม',3=>'ภาวะอ้วน',4=>'ภาวะอ้วน+เริ่มอ้วน');


	R::View('school.toolbar',$self,'Analysis : '.$schoolInfo->name,NULL,$schoolInfo);


	$formText.='<form id="condition" method="get" action="'.url('school/report/map/overview').'">';
	$formText.='<select class="form-select" name="situation">';
	foreach ($situationList as $k => $v) {
		$formText.='<option value="'.$k.'"'.($k==$situation?' selected="selected"':'').'>'.$v.'</option>';
	}
	$formText.='</select> ';

	// Select year
	$dbs=mydb::select('SELECT DISTINCT `detail1` `pryear` FROM %project_tr% WHERE `formid`="weight" AND `part`="title" HAVING `pryear` ORDER BY `pryear` ASC');
	$formText.='<select name="year" class="form-select">';
	foreach ($dbs->items as $rs) {
		$formText.='<option value="'.$rs->pryear.'"'.($rs->pryear==$year?' selected="selected"':'').'>ปีการศึกษา '.($rs->pryear+543).'</option>';
	}
	$formText.='</select>&nbsp;';

	// Select term
	$formText.='<select name="term" class="form-select">';
	for ($i=1;$i<=2;$i++) {
		for ($j=1;$j<=2;$j++) {
			$termperiod=$i.':'.$j;
			$formText.='<option value="'.$termperiod.'"'.($termperiod==$term.':'.$period?' selected="selected"':'').'>ภาคการศึกษา '.$i.' ครั้งที่ '.$j.'</option>';
		}
	}
	$formText.='</select>&nbsp;';

	$formText.='<button class="btn -primary"><i class="icon -viewdoc -white"></i><span>ดูรายงาน</span></button>';
	$formText.='</form>';

	$ret.='<h2>สถานการณ์ภาวะโภชนาการนักเรียน '.implode(' ', $text).'</h2>';

	$ret.='<div class="reportbar">'.$formText.'</div>';




	// Pie Chart by Regian

	$text=array();
	$where=array();
	$where=sg::add_condition($where,'p.`prtype`="โครงการ" AND ((tr.`formid`="weight" AND tr.`part`="weight") OR (tr.`formid`="height" AND tr.`part`="height"))');
	if ($year) {
		$where=sg::add_condition($where,'ti.`detail1`=:year ','year',$year);
		$text[]=' ปีการศึกษา '.($year+543);
	}
	if ($term) {
		$where=sg::add_condition($where,'ti.`detail2`=:term ','term',$term);
		$text[]=' ภาคการศึกษา '.($term);
	}
	if ($period) {
		$where=sg::add_condition($where,'ti.`period`=:period ','period',$period);
		$text[]='ครั้งที่ '.$period;
	}

	$stmt='SELECT
					  tr.`trid`, tr.`tpid`, tr.`sorder`
					, tr.`formid`, tr.`part`
					, ti.`detail1` `year`
					, ti.`detail2` `term`
					, ti.`period` `times`
					, p.`changwat`
					, cop.`provname`
					, SUM(IF(tr.`formid`="height",tr.`num5`,NULL)) `short`
					, SUM(IF(tr.`formid`="weight",tr.`num5`,NULL)) `thin`
					, SUM(IF(tr.`formid`="weight",tr.`num10`,NULL)) `fat`
					, SUM(IF(tr.`formid`="weight",tr.`num9`+tr.`num10`,NULL)) `fatplus`
					, SUM(IF(tr.`formid`="height",tr.`num2`,NULL)) `getheight`
					, SUM(IF(tr.`formid`="weight",tr.`num2`,NULL)) `getweight`
					FROM %project_tr% tr
						LEFT JOIN %project% p USING(`tpid`)
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %project_tr% ti ON ti.`trid`=tr.`parent`
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
				'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
				GROUP BY `changwat`';
	$dbs=mydb::select($stmt,$where['value']);

	$gis['center']='13.604486109074745,103.1000';
	$gis['zoom']=6;
	$gis['markers'][]=array('จังหวัด','%');


	foreach ($dbs->items as $rs) {
		$divby=$rs->getweight;
		if ($situation==1) {
			$situationValue=$rs->short;
			$divby=$rs->getheight;
		} else if ($situation==2) $situationValue=$rs->thin;
		else if ($situation==3) $situationValue=$rs->fat;
		else if ($situation==4) $situationValue=$rs->fatplus;
		$gis['markers'][]=array(
												$rs->provname,
												round($situationValue*100/$divby,2),
												);
	}

	$ret.='<p><b>แผนที่สถานการณ์ภาวะโภชนาการนักเรียน - '.$situationList[$situation].' '.implode(' ',$text).'</b></p>';
	$ret.='<div id="regions_div" style="height:500px;">กำลังโหลดแผนที่!!!!</div>'._NL;

	head('loader.js','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');


	$ret.='<script type="text/javascript"><!--
$(document).ready(function() {
var gis='.json_encode($gis).';
google.charts.load("current", {
        "packages":["geochart"],
        // Note: you will need to get a mapsApiKey for your project.
        // See: https://developers.google.com/chart/interactive/docs/basic_load_libs#load-settings
        "mapsApiKey": "AIzaSyD-9tSrke72PouQMnMX-a7eZSW0jkFMBWY"
      });
      google.charts.setOnLoadCallback(drawRegionsMap);

      function drawRegionsMap() {
        var data = google.visualization.arrayToDataTable([
          ["จังหวัด", "ประชากร"],
          ["สงขลา", 200],
          ["พัทลุง", 300],
          ["ตรัง", 400],
          ["สตูล", 500],
          ["ปัตตานี", 600],
          ["ยะลา", 700]
        ]);

        var data = google.visualization.arrayToDataTable(gis.markers);

	var options = {
								region: "TH",
								displayMode: "area",
								resolution: "provinces",
								colorAxis: { colors: ["yellow", "red"]},
								sizeAxis: { minValue: 0, maxValue: 30 },
								};
	var chart = new google.visualization.GeoChart(document.getElementById("regions_div"));

	chart.draw(data, options);
	}
});
--></script>';
	return $ret;
}
?>