<?php
/**
* Module : Project :: Map Org
* Created 2020-08-24
* Modify  2020-08-24
*
* @param Object $self
* @return String
*
* @usage project/map/org
*/

$debug = true;

function project_map_org($self) {
	$goodGroup=post('g');

	R::View('project.toolbar', $self, 'แผนที่องค์กร', 'map', $orgInfo);

	if (!mydb::table_exists('%co_subdistloc%')) return message('error', 'ไม่มีข้อมูลพิกัดอำเภอ');
	
	$ret='<div class="mapping -project-good">';

	/*
	$stmt='SELECT qt.`qtid`, qt.`qtgroup`, qt.`qtno`, c.`qtchoice` ,c.`choicename`
				FROM %qt% qt
					LEFT JOIN %qtchoice% c ON c.`qtid`=qt.`qtid`
				WHERE LEFT(`qtgroup`,4) IN ("std1")
				ORDER BY `qtgroup` ASC, `qtno` ASC';

	foreach (mydb::select($stmt)->items as $rs) {
		$choices[$rs->qtgroup][$rs->qtno][$rs->qtchoice]=$rs->choicename;
	}

	$ret.=print_o($choices,'$choices');
	*/

	if (post('s')) mydb::where('o.`sector` = :sector', ':sector', post('s'));

	$stmt = 'SELECT
		o.`orgid`
		, o.`name`
		--	, X(p.location) lat, Y(p.location) lng
		, cos.`lat`
		, cos.`lng`
		, cop.`provname` `changwatName`
		, o.`changwat`, o.`ampur`, o.`tambon`
		, (SELECT COUNT(*) FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE p.`prtype`="แผนงาน" AND t.`orgid` = o.`orgid`) `planningCount`
		FROM %db_org% o
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(o.`areacode`,2)
			LEFT JOIN %co_subdistloc% cos ON cos.`subdistid` = LEFT(o.`areacode`,6)
			-- LEFT JOIN %co_subdistloc% cos ON cos.`subdistid` = CONCAT(o.`changwat`, o.`ampur`, o.`tambon`)
		%WHERE%
		GROUP BY `orgid`
		HAVING `planningCount` > 0
		ORDER BY `orgid` ASC
	';

	$stmt = 'SELECT
		a.*
		, COUNT(p.`tpid`) `planningCount`
		FROM
		(SELECT
			o.`orgid`
			, o.`name`
			, f.`fundid`
			, cos.`lat`
			, cos.`lng`
			, cop.`provname` `changwatName`
			, o.`changwat`, o.`ampur`, o.`tambon`
			FROM %db_org% o
				LEFT JOIN %project_fund% f USING(`orgid`)
				LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(o.`areacode`,2)
				LEFT JOIN %co_subdistloc% cos ON cos.`subdistid` = LEFT(o.`areacode`,6)
			%WHERE%
			GROUP BY `orgid` ASC
		) a
			LEFT JOIN %topic% t USING(`orgid`)
			LEFT JOIN %project% p ON p.`tpid` = t.`tpid` AND p.`prtype` = "แผนงาน"
		GROUP BY `orgid`
		HAVING `planningCount` > 0
	';

	$dbs = mydb::select($stmt);

	//debugMsg(mydb()->_query);

	//$ret .= print_o($dbs,'$dbs');

	$guideList['1'] = array(
		'title'=>'',
		'items'=>array(),
	);

	$icons=array('green','purple','yellow','blue','red','orange','brown');
	if ($goodGroup) $self->theme->title=$guideList[$goodGroup]['title'];

	$gis['center']='13.604486109074745,103.1000';
	$gis['zoom']=6;

	/*
	$ui=new ui();
	$ui->add('<a href="'.url('project/report').'">วิเคราะห์ภาพรวม</a>');
	$ui->add('<a href="'.url('project/report/goodproject').'">การประเมินคุณค่า (แผนที่)</a>');
	if ($_REQUEST['g']) $ui->add('<a href="'.url('project/report/goodproject',array('g'=>$_REQUEST['g'])).'">'.$parts[$_REQUEST['g']].'</a>');
	$ret.=$ui->build().'<br clear="all" />';
	*/

	$tables = new Table();
	$tables->thead=array('no'=>'','หน่วยงาน','จังหวัด', 'planning -amt' => 'แผนงาน', 'dev -amt' => 'พัฒนาโครงการ', 'follow -amt' => 'ติดตามโครงการ');

	foreach ($dbs->items as $rs) {
		list($lat, $lng) = explode(',', $rs->location);
		$lat = $rs->lat;
		$lng = $rs->lng;
		if ($lat && $lng) {
		/*
			if ($rs->projectCount==0) {
				$icon='https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|EEEEEE|FFFFFF';
				//$icon='https://softganz.com/library/img/geo/circle-gray.png';
			} else {
				$icon='https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld='.number_format($rs->projectCount).'|b03cd3|FFFFFF';
			}
		*/
			$icon='https://softganz.com/library/img/geo/circle-green.png';

			$url = $rs->fundid ? 'project/fund/'.$rs->orgid : 'project/org/'.$rs->orgid;
			$gis['markers'][]=array('latitude'=> (float) $lat,
				'longitude'=> (float) $lng,
				'title'=>$rs->name,
				'icon'=>$icon,
				'content'=>'<h4><a href="'.url($url).'" target="_blank">'.$rs->name.'</a></h4><p>จังหวัด'.$rs->changwatName.'<br />แผนงาน : '.$rs->planningCount.' แผนงาน<br /><a href="'.url($url).'" target="_blank">รายละเอียดองค์กร</a></p>'
			);
		}

		/*
		$tables->rows[]=array(
			++$no,
			'<a href="'.url('project/org/'.$rs->orgid).'" target="_blank">'.$rs->name.'</a>',
			$rs->changwatName,
			$rs->planningCount ? $rs->planningCount : '-',
			$rs->developCount ? $rs->developCount : '-',
			$rs->projectCount ? $rs->projectCount : '-',
		);
		*/
	}

	$ret.='<ul class="ui-menu">'._NL;
	foreach (project_base::$orgTypeList as $k=>$v) {
		$ret.='<li><a href="'.url('project/map/org',array('s'=>$k)).'"><span class="" style="width:20px;height:20px;display:inline-block;background:#FD675B;border-radius:20px;border:2px #fff solid; text-align:center; line-height:20px;">'.($k).'</span> '.$v.'</a>';
		$ret.='</li>'._NL;
	}
	$ret.='</ul>'._NL;

	$ret.='<div class="app-output">กำลังโหลดแผนที่!!!!</div>'._NL;

	$ret.='</div><!-- mapping -->';

	//$ret.=print_o($dbs,'$dbs');
	//$ret.=$tables->build();

	head('<style type="text/css">
	.mapping {position:relative;}
	.mapping .ui-menu {padding:8px;position: absolute; z-index:1; top:8px; right:8px; border-radius:2px; background-color:#fff; opacity:0.9;}
	</style>');

	head('jquery.ui.map','<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');

	$ret.='<script type="text/javascript"><!--
$(document).ready(function() {
var imgSize = new google.maps.Size(12, 20);
var gis='.json_encode($gis).';
var is_point=false;
$map=$(".app-output");
$map.gmap({
		center: gis.center,
		zoom: gis.zoom,
		scrollwheel: false
	})
	.bind("init", function(event, map) {
		if (gis.markers) {
			$.each( gis.markers, function(i, marker) {
				$map.gmap("addMarker", {
					position: new google.maps.LatLng(marker.latitude, marker.longitude),
					icon : marker.icon,
					draggable: false,
				}).mouseover(function() {
					$map.gmap("openInfoWindow", { "content": marker.content }, this);
				});
			});
		}
	})
});
--></script>';

//		$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>