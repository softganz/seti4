<?php
/**
* Project :: Report of Follow Estimation
* Created 2019-03-13
* Modify  2021-04-10
*
* @param Object $self
* @return String
*
* @usage project/report/estimation
*/

$debug = true;

function project_report_estimation($self) {
	// Data Model
	$getYear = post('year');
	$getChangwat = post('prov');
	$getInno = post('inno');

	$parts = array(
		'5.1' => 'เกิดความรู้ หรือ นวัตกรรมชุมชน',
		'5.2' => 'เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
		'5.3' => 'การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ',
		'5.4' => 'การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
		'5.5' => 'เกิดกระบวนการชุมชน',
		'5.6' => 'มิติสุขภาวะปัญญา/สุขภาวะทางจิตวิญญาณ'
	);


	mydb::where('tr.`formid` IN ("ประเมิน", "valuation") AND tr.`rate1` = 1');
	mydb::where('p.`project_status` IN ("กำลังดำเนินโครงการ","ดำเนินการเสร็จสิ้น")');
	if ($getYear) mydb::where('p.`pryear` = :year',':year', $getYear);
	if ($getChangwat) mydb::where('p.`changwat` = :changwat',':changwat',$getChangwat);
	if ($getInno) mydb::where('tr.`part` LIKE :part',':part',$getInno.'%');

	$stmt = 'SELECT
		tr.`trid`, tr.`tpid`, tr.`formid`, tr.`part`, tr.`rate1`
		, t.`title`, p.`agrno`, p.`prid`, p.`pryear`
		, X(p.`location`) lat, Y(p.`location`) lng
		, p.`project_status`, p.`project_status`+0 project_statuscode
		, p.`changwat`, cop.`provname`
		FROM %project_tr% tr
			LEFT JOIN %topic% t USING(tpid)
			LEFT JOIN %project% p USING(tpid)
			LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat`
		%WHERE%
		GROUP BY tr.`tpid`
		ORDER BY p.`pryear` DESC, `provname` ASC, t.`title` ASC';

	$estimateDbs = mydb::select($stmt);

	//debugMsg('<pre>'.mydb()->_query.'</pre>');

	if ($getChangwat) mydb::where('p.changwat = :changwat', ':changwat', $getChangwat);
	$yearDbs = mydb::select('SELECT pryear, COUNT(*) total FROM %project% p %WHERE% GROUP BY pryear');

	/*
	mydb::where('p.`tpid` IN (SELECT `tpid` FROM %project_tr% tr WHERE tr.`tpid` = p.`tpid` AND tr.`formid` IN ("ประเมิน", "valuation") AND tr.`rate1`=1)');
	if ($getChangwat) {
		mydb::where('p.`changwat` = :changwat', ':changwat', $getChangwat);
	}
	if ($getInno) mydb::where('tr.`part` LIKE :part', ':part', $getInno);

	$stmt = 'SELECT
		p.`pryear`, COUNT(*) `total`, p.`tpid`
		FROM %project% p
			LEFT JOIN %co_province% cop ON cop.`provid` = p.`changwat`
		%WHERE%
		GROUP BY p.`pryear`
		ORDER BY p.`pryear` ASC';
	*/

	mydb::where('e.`formid` IN ("ประเมิน", "valuation") AND e.`rate1` = 1');
	mydb::where('p.`project_status` IN ("กำลังดำเนินโครงการ","ดำเนินการเสร็จสิ้น")');
	if ($getChangwat) mydb::where('p.`changwat` = :changwat',':changwat',$getChangwat);
	if ($getInno) mydb::where('e.`part` LIKE :part',':part',$getInno.'%');

	$stmt = 'SELECT
		p.`pryear`, COUNT(*) `total`, p.`tpid`
		FROM %project_tr% e
			LEFT JOIN %project% p USING(`tpid`)
		%WHERE%
		GROUP BY p.`pryear`
		ORDER BY p.`pryear` ASC';

	$yearEstimateDbs = mydb::select($stmt);
	//debugMsg($yearEstimateDbs, '$yearEstimateDbs');

	// View Model
	R::View('project.toolbar', $self, 'การประเมินคุณค่า (แผนภูมิ)', 'report');
	$form = new Form(
		[
			'action' => url(q()),
			'id' => 'project-report',
			'class' => 'sg-form -inlineitem',
			'rel' => '#main',
			'children' => [
				'year' => [
					'type' => 'select',
					'options' => ['' => '--- ทุกปี ---']
						+ mydb::select(
							'SELECT DISTINCT `pryear`, CONCAT("พ.ศ.",`pryear` + 543) `bcyear`
							FROM %project%
							ORDER BY `pryear` ASC;
							-- {key: "pryear", value: "bcyear"}'
						)->items,
					'value' => $getYear,
				],
				'prov' => [
					'type' => 'select',
					'options' => ['' => '--- ทุกจังหวัด ---']
						+ mydb::select(
							'SELECT DISTINCT `changwat`, `provname`
							FROM %project% p
								LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
							WHERE `provname`!=""
							ORDER BY CONVERT(`provname` USING tis620) ASC;
							-- {key: "changwat", value: "provname"}'
						)->items,
					'value' => $getChangwat,
				],
				'inno' => [
					'type' => 'select',
					'options' => ['' => '--- ทุกนวัตกรรม ---'] + $parts,
					'value' => $getInno,
				],
				'submit' => [
					'type' => 'button',
					'value' => 'ดูรายงาน',
				],
			],
		]
	);

	$ret .= '<div class="toolbar -sub">'.$form->build().'</div>';

	$iconPart='https://maps.gstatic.com/mapfiles/ridefinder-images/mm_20_';
	$icons=array('green','purple','yellow','blue','red','orange','brown');
	if ($_REQUEST['g']) $this->theme->title.=' : '.$parts[$_REQUEST['g']];

	$gis['center']='8.604486109074745,100.1000';
	$gis['zoom']=7;

	$tables = new Table();
	$tables->colspan=array('no'=>'align="center"', 'align="center"','align="left"');
	$tables->thead=array('no'=>'',/*'ข้อตกลงเลขที่','รหัสโครงการ',*/'ปี','จังหวัด','ชื่อโครงการ'/*,'สถานะโครงการ'*/);
	$icons['กำลังดำเนินโครงการ']='https://softganz.com/library/img/geo/circle-green.png';
	$icons['ดำเนินการเสร็จสิ้น']='https://softganz.com/library/img/geo/circle-gray.png';
	$icons['ยุติโครงการ']='https://softganz.com/library/img/geo/circle-red.png';
	$icons['ระงับโครงการ']='https://softganz.com/library/img/geo/circle-yellow.png';

	foreach ($estimateDbs->items as $rs) {
		if ($rs->lat && $rs->lng) {
			$icon='https://maps.google.com/mapfiles/kml/paddle/'.substr($rs->part,-1).'.png';
			$icon=$iconPart.($icons[substr($getInno,2,1)+0]).'.png';
			$gis['markers'][]=array('latitude'=>$rs->lat,
				'longitude'=>$rs->lng,
				'title'=>$rs->title,
				'icon'=>$icon,
				'content'=>'<h4>'.$rs->title.'</h4><p>คุณค่าที่เกิดขึ้น : '.$parts[$rs->part].'<br />สถานภาพ : '.$rs->project_status.'<br /><a href="'.url('project/'.$rs->tpid).'" target="_blank">รายละเอียดโครงการ</a> | <a href="'.url('project/'.$rs->tpid.'/eval.valuation').'" target="_blank">คุณค่าของโครงการ</a></p>'
			);
		}
		$tables->rows[]=array(++$no,
			$rs->pryear?sg_date($rs->pryear,'ปปปป'):'',
			$rs->provname,
			'<a href="'.url('project/'.$rs->tpid.'/eval.valuation').'" target="_blank">'.$rs->title.'</a><br />('.$rs->project_status.')',
			'config'=>array('class'=>'project-status-'.$rs->project_statuscode));
		}

	$graphType = 'col';
	$data[] = array(
		'ปี',
		'โครงการทั้งปี',
		array('role'=>'tooltip','p'=>array('html'=>true)),
		array('role'=>'annotation'),
		'เกิดสิ่งดี ๆ',
		array('role'=>'tooltip','p'=>array('html'=>true)),
		array('role'=>'annotation'),
		//'%',
		//array('role'=>'tooltip','p'=>array('html'=>true)),
		//array('role'=>'annotation')
	);

	$chartTypes=array('bar'=>'BarChart','pie'=>'PieChart','col'=>'ColumnChart','line'=>'LineChart');

	$max = 0;
	foreach ($yearDbs->items as $rs) {
		$max = $max<$rs->total?$rs->total:$max;
		$yearTotalProject[$rs->pryear] = $rs->total;
	}
//		$ret.=print_o($yearTotalProject);
//		$ret.=print_o($yearDbs);

	foreach ($yearEstimateDbs->items as $rs) {
		$year='พ.ศ. '.($rs->pryear+543);
		$percent=round($rs->total/$yearTotalProject[$rs->pryear]*100);
		$data[]=array(
			$year,
			$yearTotalProject[$rs->pryear],
			'ทั้งหมด '.$yearTotalProject[$rs->pryear].' โครงการ',
			$yearTotalProject[$rs->pryear],
			intval($rs->total),
			'เกิดสิ่งดี ๆ <strong>'.$rs->total.' โครงการ</strong> ('.$percent.'%)<br />จากทั้งหมด '.$yearTotalProject[$rs->pryear].' โครงการ',
			$rs->total.'('.$percent.'%)',
			//$percent,
			//'คิดเป็น <strong>'.$percent.'%</strong>',
			//$percent.'%',
		);
	}

	//$max=(floor($max/100)+1)*100;

	$ret.='<div id="project-report-detail">'. $tables->build() .'</div>';

	$ret.='<div id="project-report-graph">';
	$ret.='<div id="chart" style="width:100%; height:400px;"></div>';

//		$ret.=print_o($data,'$data');
//		if (i()->username=='softganz') $ret.=print_o($yearEstimateDbs,'$yearEstimateDbs');

	$ret.='<div id="project-report-map">กำลังโหลดแผนที่!!!!</div>';
	$ret.='</div>'._NL;
	$ret.='<br clear="all" />';

	head('<script type="text/javascript" src="https://www.google.com/jsapi"></script>');
	head('<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');

	$ret.='<script type="text/javascript"><!--

google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(drawChart);
function drawChart() {
var data = google.visualization.arrayToDataTable('.json_encode($data).');
var maxProject='.$max.'

var options = {
	title: "การประเมินคุณค่า'.($getInno?' '.$parts[$getInno]:'').($getChangwat?' จังหวัด'.$form->children['prov']['options'][$getChangwat]:'').'",
	colors: ["#058DC7", "#f60", "#C605BA"],
	tooltip: {isHtml: true},
	vAxes: {
		0: {
			logScale: false,
			viewWindow: {
				min: 0,
				max: maxProject
			},
			gridlines: {
				count: maxProject/20,
			},
		},
		/*
		1: {
			logScale: false,
			viewWindow: {
				min: 0,
				max: 100
			},
			gridlines: {
				count: 100/20,
			},
		}
		*/
		},
	series: {
		0:{targetAxisIndex:0},
		1:{targetAxisIndex:0},
		//2:{targetAxisIndex:1}
	}
};

var chart = new google.visualization.'.$chartTypes[$graphType].'(document.getElementById("chart"));
chart.draw(data, options);

//	var sdata = google.visualization.arrayToDataTable('.json_encode($sdata).');
//	var soptions = {title: "จำนวนคนพิการแยกตามประเภท"};
//	var schart = new google.visualization.PieChart(document.getElementById("schart_div"));
//	schart.draw(sdata, soptions);
}

$(document).ready(function() {
var imgSize = new google.maps.Size(12, 20);
var gis='.json_encode($gis).';
var is_point=false;
$map=$("#project-report-map");
$map.gmap({
		center: gis.center,
		zoom: gis.zoom,
		scrollwheel: true
	})
	.bind("init", function(event, map) {
		if (gis.markers) {
			$.each( gis.markers, function(i, marker) {
				$map.gmap("addMarker", {
					position: new google.maps.LatLng(marker.latitude, marker.longitude),
					icon : new google.maps.MarkerImage(marker.icon, imgSize, null, null, imgSize),
					draggable: false,
				}).mouseover(function() {
					$map.gmap("openInfoWindow", { "content": marker.content }, this);
				});
			});
		}
	})

$("#project-report select").change(function() {
	notify("Loading...");
	$("#project-report").submit();
});
});
--></script>';
	return $ret;
}
?>