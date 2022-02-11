<?php
/**
* Project :: Report of Follow Valuation
* Created 2019-03-13
* Modify  2021-04-10
*
* @param Object $self
* @return String
*
* @usage project/report/valuation
*/

$debug = true;

function project_report_valuation_v2($self) {
	// Data Model
	$getArea = post('area');
	$getYear = post('year');
	$getChangwat = post('prov');
	$getInno = post('inno');

	$parts = [
		'inno'=>'เกิดความรู้ด้านการสร้างเสริมสุขภาพและนวัตกรรมเชิงระบบสุขภาพ',
		'behavior'=>'เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
		'environment'=>'การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ (กายภาพ สังคม และเศรษฐกิจ)',
		'publicpolicy'=>'การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
		'social'=>'กระบวนการเคลื่อนไหวทางสังคมและกระบวนการในพื้นที่',
		'spirite'=>'มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ'
	];


	mydb::where('tr.`formid` IN ("ประเมิน", "valuation") AND tr.`rate1` = 1');
	mydb::where('p.`project_status` IN ("กำลังดำเนินโครงการ","ดำเนินการเสร็จสิ้น")');
	if ($getYear) mydb::where('p.`pryear` = :year',':year', $getYear);
	if ($getChangwat) mydb::where('LEFT(t.`areacode`,2) = :changwat',':changwat',$getChangwat);
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

	/*
	if ($getChangwat) mydb::where('p.changwat = :changwat', ':changwat', $getChangwat);
	$yearDbs = mydb::select('SELECT pryear, COUNT(*) total FROM %project% p %WHERE% GROUP BY pryear');

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

	$totalList = [];
	$join = [];
	mydb::where('p.`project_status` IN ("กำลังดำเนินโครงการ","ดำเนินการเสร็จสิ้น")');
	//if ($getChangwat) mydb::where('LEFT(t.`areacode`,2) = :changwat',':changwat',$getChangwat);
	//if ($getInno) mydb::where('v.`part` LIKE :part',':part',$getInno.'%');
	if ($getArea) {
		$totalList[] = 'COUNT(DISTINCT IF(f.`areaid` = :areaId AND v.`rate1` = 1, p.`tpid`, NULL)) `totalArea`';
		mydb::where(NULL,':areaId', $getArea);
		$join[] = 'LEFT JOIN %project_fund% f ON f.`orgid` = o.`orgid`';
	}
	if ($getChangwat) {
		$totalList[] = 'COUNT(DISTINCT IF(t.`areacode` LIKE :changwat AND v.`rate1` = 1, p.`tpid`, NULL)) `totalProvince`';
		mydb::where(NULL,':changwat', $getChangwat.'%');
	}
	if ($getInno) {
		mydb::where(NULL, ':part', $getInno.'%');
		$totalList[] = 'COUNT(DISTINCT IF(v.`part` LIKE :part AND v.`rate1` = 1, p.`tpid`, NULL)) `totalRate`';
	} else {
		$totalList[] = 'COUNT(DISTINCT IF(v.`rate1` = 1, p.`tpid`, NULL)) `totalRate`';
	}

	mydb::value('$FIELD$', implode(_NL.'		, ', $totalList), false);
	mydb::value('$JOIN$', implode(_NL, $join));

	$stmt = 'SELECT
		p.`pryear`
		, COUNT(DISTINCT p.`tpid`) `totalProject`
		, $FIELD$
		-- , p.`tpid`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
			$JOIN$
			LEFT JOIN %project_tr% v ON v.`tpid` = p.`tpid` AND v.`formid` = "valuation"
		%WHERE%
		GROUP BY p.`pryear`
		ORDER BY p.`pryear` ASC';

	$yearEstimateDbs = mydb::select($stmt);
	//debugMsg($yearEstimateDbs, '$yearEstimateDbs');

	$areaOptions = mydb::table_exists('%project_area%') ? mydb::select('SELECT `areaid`, CONCAT("เขต ", `areaid`, " ",`areaname`) `areaname` FROM %project_area% ORDER BY CAST(`areaid` AS UNSIGNED); -- {key: "areaid", value: "areaname"}')->items : [];



	// View Model
	R::View('project.toolbar', $self, 'การประเมินคุณค่า (แผนภูมิ)', 'report');
	$form = new Form(
		[
			'action' => url(q()),
			'id' => 'project-report',
			'class' => 'sg-form -report -inlineitem',
			'rel' => '#main',
			'children' => [
				'inno' => [
					'type' => 'select',
					'options' => ['' => '--- ทุกนวัตกรรม ---'] + $parts,
					'value' => $getInno,
				],
				'year' => [
					'type' => 'select',
					'options' => ['' => '--- ทุกปี ---']
						+ mydb::select(
							'SELECT DISTINCT p.`pryear`, CONCAT("พ.ศ.", p.`pryear` + 543) `bcyear`
							FROM %project_tr% v
								LEFT JOIN %project% p USING(`tpid`)
							WHERE v.`formid` = "valuation"
							ORDER BY p.`pryear` ASC;
							-- {key: "pryear", value: "bcyear"}'
						)->items,
					'value' => $getYear,
				],
				'plan' => [
					'type' => 'select',
					'options' => ['' => '--- ทุกแผนงาน ---'] + R::Model('category.get','project:planning','catid'),
					'value' => $getPlan,
				],
				'area' => [
					'type' => $areaOptions ? 'select' : 'hidden',
					'options' => ['' => '--- ทุกเขต ---'] + $areaOptions,
					'value' => $getArea,
				],
				'prov' => [
					'type' => 'select',
					'options' => ['' => '--- ทุกจังหวัด ---']
						+ mydb::select(
							'SELECT DISTINCT LEFT(`areacode`, 2) `changwat`, `provname`
							FROM %project_tr% v
								LEFT JOIN %topic% t USING(`tpid`)
								LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`, 2)
							WHERE v.`formid` = "valuation" AND cop.`provname`!=""
							ORDER BY CONVERT(`provname` USING tis620) ASC;
							-- {key: "changwat", value: "provname"}'
						)->items,
					'value' => $getChangwat,
				],
				'ampur' => [
					'type' => 'select',
					'options' => ['' => '--- ทุกอำเภอ ---'],
					'value' => $getAmpur,
				],
				'fund' => [
					'type' => 'select',
					'options' => ['' => '--- ทุกกองทุน ---'],
					'value' => $getFund,
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

	$gis['center'] = '13.5,101.5';
	$gis['zoom'] = 6;

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

	$graphType = 'line';
	$data[] = array('ปี',
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

	foreach ($yearEstimateDbs->items as $rs) {
		$max = $max < $rs->totalProject ? $rs->totalProject : $max;
		$year='พ.ศ. '.($rs->pryear+543);
		$percent = round($rs->totalRate / $rs->totalProject*100);
		$data[] = [
			$year,
			$rs->totalProject,
			'ทั้งหมด '.$rs->totalProject.' โครงการ',

			$rs->totalProject,
			intval($rs->totalRate),
			'เกิดสิ่งดี ๆ <strong>'.$rs->totalRate.' โครงการ</strong> ('.$percent.'%)<br />จากทั้งหมด '.$rs->totalProject.' โครงการ',
			$rs->totalRate.'('.$percent.'%)',
			//$percent,
			//'คิดเป็น <strong>'.$percent.'%</strong>',
			//$percent.'%',
		];
	}

	//$max=(floor($max/100)+1)*100;


	$ret.='<div id="project-report-graph" class="-sg-flex" style="width: 100%;" >';
	$ret.='<div id="chart" style="flex: 1; height:600px;"></div>';


	//		$ret.=print_o($data,'$data');
	//		if (i()->username=='softganz') $ret.=print_o($yearEstimateDbs,'$yearEstimateDbs');

	$ret.='<div id="project-report-map" style="flex: 0 0 400px;">กำลังโหลดแผนที่!!!!</div>';
	$ret.='</div>'._NL;

	//$ret.='<div id="project-report-detail" style="width: 100%;">'. $tables->build() .'</div>';

	$ret.='<br clear="all" />';

	//$ret .= '<pre>'.$yearEstimateDbs->_query.'</pre>'.print_o($yearEstimateDbs, '$yearEstimateDbs');

	head('<style type="text/css">
		.form.-report .form-select {width: 100px;}
	</style>');

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
						//count: maxProject/20,
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