<?php

/**
 * Send Document Report
 *
 */
function project_report_inno($self) {
	R::View('project.toolbar', $self, 'การเกิดขึ้นของนวัตกรรม', 'report');

	$year=SG\getFirst(post('y'));
	$province=post('p');
	$inno=post('i');

	$parts=array(
		'5.1'=>'เกิดความรู้ หรือ นวัตกรรมชุมชน',
		'5.2'=>'เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
		'5.3'=>'การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ',
		'5.4'=>'การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
		'5.5'=>'เกิดกระบวนการชุมชน',
		'5.6'=>'มิติสุขภาวะปัญญา/สุขภาวะทางจิตวิญญาณ'
	);

	$form = new Form([
		'action' => url(q()),
		'id' => 'project-report',
		'class' => 'form -inlineitem',
		'children' => [
			'year' => [
				'type' => 'select',
				'name' => 'y',
				'options' => (function() {
					$options = [NULL => '--- ทุกปี ---'];
					foreach (mydb::select('SELECT DISTINCT `pryear` FROM %project% ORDER BY `pryear` ASC')->items as $item) {
						$options[$item->pryear]='พ.ศ. '.($item->pryear+543);
					}
					return $options;
				})(),
				'value' => $year,
			],
			'province' => [
				'type' => 'select',
				'name' => 'p',
				'options' => (function() {
					$options = [NULL => '--- ทุกจังหวัด ---'];
					$dbs=mydb::select('SELECT DISTINCT `changwat`, `provname` FROM %project% p LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` WHERE `provname`!="" ORDER BY CONVERT(`provname` USING tis620) ASC');
					foreach ($dbs->items as $rs) {
						$options[$rs->changwat]=$rs->provname;
					}
					return $options;
				})(),
				'value' => $province,
			],
			'go' => [
				'type' => 'button',
				'value' => '<i class="icon -material">search</i><span>ดูรายงาน</span>',
			],
		], // children
	]);

	$ret.='<div class="toolbar -sub">'. $form->build() .'</div>';

	$where=array();
	$where=sg::add_condition($where,'tr.`formid`="follow" AND tr.`part`="2.3.1" AND tr.`detail1` NOT IN("","-","ไม่มี") ');
	if ($year) $where=sg::add_condition($where,'p.`pryear`=:year','year',$year);
	if ($province) $where=sg::add_condition($where,'p.`changwat`=:changwat','changwat',$province);

	$stmt='SELECT tr.`trid`, tr.`tpid`, tr.`formid`, tr.`part`,
			tr.`detail1` innovation,
			tr.`text1`, tr.`text2`,
			t.`title`, p.`agrno`, p.`prid`, p.`pryear`, tr.`period`,
			X(p.`location`) lat, Y(p.`location`) lng,
			p.`project_status`, p.`project_status`+0 project_statuscode,
			p.`changwat`, cop.`provname`
		FROM %project_tr% tr
			LEFT JOIN %topic% t USING(tpid)
			LEFT JOIN %project% p USING(tpid)
			LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` '
		.($where?'WHERE '.implode(' AND ',$where['cond']):'')
		.' GROUP BY tr.tpid'
		.' ORDER BY CONVERT(t.`title` USING tis620) ASC';
	$dbs=mydb::select($stmt,$where['value']);

	$iconPart='https://maps.gstatic.com/mapfiles/ridefinder-images/mm_20_';
	$icons=array('green','purple','yellow','blue','red','orange','brown');
	if ($_REQUEST['g']) $this->theme->title.=' : '.$parts[$_REQUEST['g']];

	$gis['center']='8.604486109074745,100.1000';
	$gis['zoom']=7;

	$tables = new Table();
	$tables->colspan=array('no'=>'align="center"', 'align="center"','align="left"');
	$tables->thead=array('','ปี','จังหวัด','ชื่อโครงการ');
	$icons['กำลังดำเนินโครงการ']='https://softganz.com/library/img/geo/circle-green.png';
	$icons['ดำเนินการเสร็จสิ้น']='https://softganz.com/library/img/geo/circle-gray.png';
	$icons['ยุติโครงการ']='https://softganz.com/library/img/geo/circle-red.png';
	$icons['ระงับโครงการ']='https://softganz.com/library/img/geo/circle-yellow.png';
	foreach ($dbs->items as $rs) {
		if ($rs->lat && $rs->lng) {
			$icon='https://maps.google.com/mapfiles/kml/paddle/'.substr($rs->part,-1).'.png';
			$icon=$iconPart.($icons[substr($inno,2,1)+0]).'.png';
			$gis['markers'][]=array('latitude'=>$rs->lat,
				'longitude'=>$rs->lng,
				'title'=>$rs->title,
				'icon'=>$icon,
				'content'=>'<h4>'.$rs->title.'</h4><p>คุณค่าที่เกิดขึ้น : '.$parts[$rs->part].'<br />สถานภาพ : '.$rs->project_status.'<br /><a href="'.url('project/'.$rs->tpid).'">รายละเอียดโครงการ</a> | <a href="'.url('project/'.$rs->tpid.'/eval.valuation').'">คุณค่าของโครงการ</a></p>'
			);
		}
		$tables->rows[]=array(
			++$no,
			$rs->pryear?sg_date($rs->pryear,'ปปปป'):'',
			$rs->provname,
			'<div style="text-align:left;"><strong><a href="'.url('paper/'.$rs->tpid.'/member/trainer/post/follow/period/'.$rs->period).'">'.$rs->title.'</a></strong>('.$rs->project_status.')<br />'
			.'<br /><strong>ชื่อนวัตกรรม : </strong><br />'.$rs->innovation
			.'<br /><strong>คุณลักษณะ/วิธีการทำให้เกิดนวัตกรรม : </strong><br />'.$rs->text1
			.'<br /><strong>ผลของนวัตกรรม/การนำไปใช้ประโยชน์ : </strong><br />'.$rs->text2.'</div>',
			'config'=>array('class'=>'project-status-'.$rs->project_statuscode)
		);
	}

	$stmt='SELECT pryear, COUNT(*) total FROM %project% p '.($province?'WHERE p.changwat=:changwat ' :'').' GROUP BY pryear';
	$yearDbs=mydb::select($stmt,$where['value']);

	$max=0;
	foreach ($yearDbs->items as $rs) {
		$max=$max<$rs->total?$rs->total:$max;
		$yearTotalProject[$rs->pryear]=$rs->total;
	}
//		$ret.=print_o($yearTotalProject);
//		$ret.=print_o($yearDbs);
	$stmt='SELECT  p.`pryear`, COUNT(*) total, p.`tpid`
		FROM %project% p
		LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
		WHERE '.($province?'p.changwat=:changwat AND ' :'').'p.tpid IN (SELECT tpid FROM %project_tr% tr WHERE tr.tpid=p.tpid AND tr.`formid`="follow" AND tr.`part`="2.3.1" AND tr.`detail1` NOT IN("","-","ไม่มี") )
		GROUP BY p.pryear
		ORDER BY p.pryear ASC';
	$sdbs=mydb::select($stmt,$where['value']);

	$graphType='col';
	$data[]=array(
		'ปี',
		'โครงการทั้งปี',
		array(
			'role'=>'tooltip',
			'p'=>array(
							'html'=>true
							)
			),
		array('role'=>'annotation'),
		'เกิดนวัตกรรม',
		array(
			'role'=>'tooltip',
			'p'=>array(
							'html'=>true
							)
			),
		array(
			'role'=>'annotation'
			),
		//'%',
		//array('role'=>'tooltip','p'=>array('html'=>true)),
		//array('role'=>'annotation')
	);
	$chartTypes=array('bar'=>'BarChart','pie'=>'PieChart','col'=>'ColumnChart','line'=>'LineChart');
	foreach ($sdbs->items as $rs) {
		$year='พ.ศ. '.($rs->pryear+543);
		$percent=round($rs->total/$yearTotalProject[$rs->pryear]*100);
		$data[]=array(
			$year,
			$yearTotalProject[$rs->pryear],
			'ทั้งหมด '.$yearTotalProject[$rs->pryear].' โครงการ',
			$yearTotalProject[$rs->pryear],
			intval($rs->total),
			'เกิดนวัตกรรม <strong>'.$rs->total.' โครงการ</strong> '.$percent.'% ของ '.$yearTotalProject[$rs->pryear].' โครงการทั้งปี',
			$rs->total.'('.$percent.'%)',
			//$percent,
			//'คิดเป็น <strong>'.$percent.'%</strong>',
			//$percent.'%',
		);
	}
	//$ret.='Max='.$max;
	//$max=(floor($max/100)+1)*100;
	//$ret.=' adjust ='.$max;


	$ret.='<div id="project-report-detail">'. $tables->build() .'</div>';

	$ret.='<div id="project-report-graph">';
	$ret.='<div id="chart" style="width:100%; height:500px;"></div>';

	//$ret.=print_o($data,'$data');
	//$ret.=print_o($sdbs,'$sdbs');

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
	title: "การเกิดขึ้นของนวัตกรรม'.($inno?' '.$parts[$inno]:'').($province?' จังหวัด'.$form->children['province']['options'][$province]:'').'",
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