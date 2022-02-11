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

function project_report_valuation_v1($self) {
	// Data Model
	$getYear = post('y');
	$getChangwat = post('p');
	$getInno = post('i');

	$parts = array(
		'inno'=>'เกิดความรู้ด้านการสร้างเสริมสุขภาพและนวัตกรรมเชิงระบบสุขภาพ',
		'behavior'=>'เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
		'environment'=>'การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ (กายภาพ สังคม และเศรษฐกิจ)',
		'publicpolicy'=>'การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
		'social'=>'กระบวนการเคลื่อนไหวทางสังคมและกระบวนการในพื้นที่',
		'spirite'=>'มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ'
	);


	$form=new Form('report',url(q()),'project-report','form -inlineitem');
	$form->addConfig('method', 'GET');

	$form->year->type='select';
	$form->year->name='y';
	$form->year->options[NULL]='--- ทุกปี ---';
	foreach (mydb::select('SELECT DISTINCT `pryear` FROM %project% ORDER BY `pryear` ASC')->items as $item) {
		$form->year->options[$item->pryear]='พ.ศ. '.($item->pryear+543);
	}
	$form->year->value=$getYear;

	$form->province->type='select';
	$form->province->name='p';
	$form->province->options[NULL]='--- ทุกจังหวัด ---';
	$dbs=mydb::select('SELECT DISTINCT `changwat`, `provname` FROM %project% p LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` WHERE `provname`!="" ORDER BY CONVERT(`provname` USING tis620) ASC');
	foreach ($dbs->items as $rs) {
		$form->province->options[$rs->changwat]=$rs->provname;
	}
	$form->province->value=$getChangwat;

	$form->inno->type='select';
	$form->inno->name='i';
	$form->inno->options[NULL]='--- ทุกนวัตกรรม ---';
	foreach ($parts as $key=>$item) {
		$form->inno->options[$key]=$item;
	}
	$form->inno->value=$getInno;

	$form->addField('go','<button class="btn -primary"><span>ดูรายงาน</span></button>');
	//$form->addField('go',array('type'=>'button','name'=>NULL,'value'=>'<span>ดูรายงาน</span>'));

	$ret.='<div class="toolbar -sub">'.$form->build().'</div>';




	mydb::where('tr.`formid`="valuation" AND tr.`rate1`=1 AND p.`project_status` IN ("กำลังดำเนินโครงการ","ดำเนินการเสร็จสิ้น")');
	if ($getYear) mydb::where('p.`pryear`=:year',':year',$getYear);
	if ($getChangwat) mydb::where('p.`changwat`=:changwat',':changwat',$getChangwat);
	if ($getInno) mydb::where('tr.`part` LIKE :part',':part',$inno.'%');

	$mydbWhere=mydb()->_where;
	$mydbValue=mydb()->_values;


	// Query for project show in table and map
	$stmt='SELECT
						tr.`trid`, tr.`tpid`, tr.`formid`, tr.`part`, tr.`rate1`
					, t.`title`, p.`agrno`, p.`prid`, p.`pryear`
					, X(p.`location`) `lat`, Y(p.`location`) `lng`
					, p.`project_status`, p.`project_status`+0 `project_statuscode`
					, p.`changwat`, cop.`provname`
					FROM %project_tr% tr
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %project% p USING(`tpid`)
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
					%WHERE%
					GROUP BY tr.`tpid`
					ORDER BY p.`pryear` DESC, `provname` ASC, t.`title` ASC';
	$dbs=mydb::select($stmt,$where['value']);
	//$ret.=print_o($dbs,'$dbs');



	// View Model
	R::View('project.toolbar', $self, 'การประเมินคุณค่า (แผนภูมิ)', 'report');

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
	foreach ($dbs->items as $rs) {
		if ($rs->lat && $rs->lng) {
			$icon='https://maps.google.com/mapfiles/kml/paddle/'.substr($rs->part,-1).'.png';
			$icon=$iconPart.($icons[substr($getInno,2,1)+0]).'.png';
			$gis['markers'][]=array(
				'latitude'=>$rs->lat,
				'longitude'=>$rs->lng,
				'title'=>$rs->title,
				'icon'=>$icon,
				'content'=>'<h4>'.$rs->title.'</h4><p>คุณค่าที่เกิดขึ้น : '.$parts[$rs->part].'<br />สถานภาพ : '.$rs->project_status.'<br /><a href="'.url('project/'.$rs->tpid).'">รายละเอียดโครงการ</a> | <a href="'.url('project/'.$rs->tpid.'/eval.valuation').'">คุณค่าของโครงการ</a></p>'
			);
		}
		$tables->rows[]=array(++$no,
			$rs->pryear?sg_date($rs->pryear,'ปปปป'):'',
			$rs->provname,
			'<a href="'.url('project/'.$rs->tpid.'/eval.valuation').'">'.$rs->title.'</a><br />('.$rs->project_status.')',
			'config'=>array('class'=>'project-status-'.$rs->project_statuscode)
		);
	}
	//$ret.=print_o($gis,'$gis');

	$graphType='col';
	$data[]=array('ปี',
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



	mydb()->_values=$mydbValue;
	$stmt='SELECT pryear, COUNT(*) total FROM %project% p '.($getChangwat?'WHERE p.changwat=:changwat ' :'').' GROUP BY pryear';
	$yearDbs=mydb::select($stmt);
	$max=0;
	foreach ($yearDbs->items as $rs) {
		$max=$max<$rs->total?$rs->total:$max;
		$yearTotalProject[$rs->pryear]=$rs->total;
	}
	//$ret.=print_o($yearTotalProject,'$yearTotalProject');
	//$ret.=print_o($yearDbs);



	mydb()->_where=$mydbWhere;
	mydb()->_values=$mydbValue;
	if ($getInno) mydb()->where(NULL,':part',$getInno.'%');

	// Query for show in chart
	$stmt='SELECT
					  p.`pryear`
					, COUNT(*) `total`
					, p.`tpid`
					FROM `sgz_project` p
						LEFT JOIN `sgz_co_province` cop ON cop.`provid`=p.`changwat`
					WHERE '.($getChangwat?'p.changwat=:changwat AND ' :'').'p.tpid IN (SELECT tpid FROM %project_tr% tr WHERE tr.tpid=p.tpid AND tr.`formid`="valuation" AND tr.`rate1`=1 '.($getInno?'AND tr.`part` LIKE :part':'').')
					GROUP BY p.pryear
					ORDER BY p.pryear ASC;
					-- {key:"pryear"}';
	$sdbs=mydb::select($stmt);
	//$ret.=print_o($sdbs,'$sdbs');


	foreach ($yearTotalProject as $pryear=>$totalProject) {
		$rs=$sdbs->items[$pryear];
		$year='พ.ศ. '.($pryear+543);
		$percent=round($rs->total*100/$totalProject);
		$data[]=array(
							$year,
							$totalProject,
							'ทั้งหมด '.$totalProject.' โครงการ',
							$totalProject,
							intval($rs->total),
							'เกิดสิ่งดี ๆ <strong>'.$rs->total.' โครงการ</strong> ('.$percent.'%)<br />จากทั้งหมด '.$yearTotalProject[$rs->pryear].' โครงการ',
							$rs->total.'('.$percent.'%)',
							//$percent,
							//'คิดเป็น <strong>'.$percent.'%</strong>',
							//$percent.'%',
							);
	}

	//$max=(floor($max/100)+1)*100;


	$ret.='<div id="project-report-graph" class="project-report-graph">';
	$ret.='<div id="chart" style="width:100%; height:400px;"></div>';

	//$ret.=print_o($data,'$data');
	//		if (i()->username=='softganz') $ret.=print_o($sdbs,'$sdbs');

	$ret.='<div id="project-report-map" style="width:100%; height:400px;">กำลังโหลดแผนที่!!!!</div>';
	$ret.='</div>'._NL;

	$ret.='<div id="project-report-detail" class="project-report-detail">'. $tables->build() .'</div>';
	$ret.='<br clear="all" />';

	head('<script type="text/javascript" src="https://www.google.com/jsapi"></script>');
	head('<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');

	$ret.='<style type="text/css">
	.project-report-graph {width:50%; float:left;}
	.project-report-detail {width:45%; margin-left:5%; float:right;}
	</style>';

	$ret.='<script type="text/javascript"><!--
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);
	function drawChart() {
	var data = google.visualization.arrayToDataTable('.json_encode($data).');
	var maxProject='.$max.'

	var options = {
									title: "การประเมินคุณค่า'.($getInno?' '.$parts[$getInno]:'').($getChangwat?' จังหวัด'.$form->province->options[$getChangwat]:'').'",
									colors: ["#058DC7", "#f60", "#C605BA"],
									tooltip: {isHtml: true},
									vAxes: {	0: {
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