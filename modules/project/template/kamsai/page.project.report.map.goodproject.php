<?php
/**
* Report name
* @param Object $self
* @return String
*/
function project_report_map_goodproject($self) {
	$goodGroup=post('g');
	project_model::set_toolbar($self,'การประเมินคุณค่า (แผนที่)');

	$ret='<div class="mapping -project-good">';

	$where=array();
	$where=sg::add_condition($where,'tr.`formid`="valuation" AND `part` LIKE "inno.%" AND tr.`rate1`=1');
	if ($goodGroup) $where=sg::add_condition($where,'tr.`part`=:part','part','inno.'.$goodGroup);

	$stmt='SELECT
						  tr.`trid`, tr.`tpid`
						, tr.`formid`, tr.`part`
						, tr.`rate1`
						, t.`title`, p.`agrno`, p.`prid`, p.`pryear`
						, X(p.`location`) lat, Y(p.`location`) lng
						, p.`project_status`
						, p.`project_status`+0 project_statuscode
						, p.`changwat`, cop.`provname`
						, COUNT(*) `totalGood`
					FROM %project_tr% tr
						LEFT JOIN %topic% t USING(tpid)
						LEFT JOIN %project% p USING(tpid)
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` '
					.($where?'WHERE '.implode(' AND ',$where['cond']):'')
					.' GROUP BY tr.tpid'
					.' ORDER BY p.pryear DESC, provname ASC, t.title ASC';
	$dbs=mydb::select($stmt,$where['value']);

	$guideList=model::get_category('project:activitygroup','catid');

	$pinColor=array('FFFFFF','00FF00','FF6600','FFED3D','1E74FF','FFA5A5','8CFFE9','FFC79A','BA49FF','BFCFFF');
	if ($goodGroup) $self->theme->title=$guideList[$goodGroup];

	$gis['center']='13.604486109074745,103.1000';
	$gis['zoom']=6;
	$gis['markers'][]=array('จังหวัด','จำนวน');


	$tables = new Table();
	$tables->id='project-list';
	$tables->thead=array('no'=>'','ข้อตกลงเลขที่','รหัสโครงการ','ปี','จังหวัด','ชื่อโครงการ','สถานะโครงการ');
	$max=0;
	foreach ($dbs->items as $rs) {
		$max=$rs->totalGood>$max?$rs->totalGood:$max;
		$gis['markers'][]=array(
												$rs->provname,
												intval($rs->totalGood),
												);
		$tables->rows[]=array(++$no,
												$rs->agrno,
												$rs->prid,
												$rs->pryear?sg_date($rs->pryear,'ปปปป'):'',
												$rs->provname,
												'<a href="'.url('paper/'.$rs->tpid.'/member/trainer/post/valuation').'">'.$rs->title.'</a>',$rs->project_status,
												'config'=>array('class'=>'project-status-'.$rs->project_statuscode));
	}

	$ret.='<ul class="ui-menu -issue">'._NL;
	$ret.='<li><a href="'.url('project/report/map/goodproject').'">ทั้งหมด</a></li>'._NL;
	foreach ($guideList as $k=>$v) {
		$ret.='<li><a href="'.url('project/report/map/goodproject','g='.$k).'">'.$v.'</a></li>'._NL;
	}
	$ret.='</ul>'._NL;

	$ret.='<div id="regions_div" class="app-output">กำลังโหลดแผนที่!!!!</div>'._NL;

	$ret.='</div><!-- mapping -->';

	//$ret.=print_o($dbs,'$dbs');
	$ret.=$tables->build();

	$ret.='<style type="text/css">
	.mapping {position:relative;}
	.mapping .ui-menu {padding:8px;position: absolute; z-index:1; top:8px; right:8px; border-radius:2px; background-color:#fff; opacity:0.9;}
	.ui-menu.-issue a {padding:4px 0; display:block;}
	</style>';

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
                colorAxis: { colors: ["yellow", "green"],
                sizeAxis: { minValue: 0, maxValue: '.$max.' },
              }
            };
        var chart = new google.visualization.GeoChart(document.getElementById("regions_div"));

        chart.draw(data, options);
      }
});
--></script>';

//		$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>