<?php
/**
* Report name
* @param Object $self
* @return String
*/
function project_report_goodcenter($self) {
	$goodGroup=post('g');
	project_model::set_toolbar($self,'การประเมินศูนย์เรียนรู้');

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

	$where=array();
	// $where=sg::add_condition($where,'tr.`formid`="kamsaiindi" AND tr.`num1`>=0');
	// if ($goodGroup) $where=sg::add_condition($where,'tr.`part`=:part','part','inno.'.$goodGroup);

	$stmt='SELECT
						  tr.`trid`, tr.`tpid`
						, tr.`formid`, tr.`part`
						, SUM(tr.`num1`) `totalPoint`
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

	$stmt='SELECT
						  tr.`trid`, p.`tpid`
						, tr.`formid`, tr.`part`
						, SUM(tr.`num1`) `totalPoint`
						, t.`title`, p.`agrno`, p.`prid`, p.`pryear`
						, X(p.`location`) lat, Y(p.`location`) lng
						, p.`project_status`
						, p.`project_status`+0 project_statuscode
						, p.`changwat`, cop.`provname`
						, COUNT(*) `totalGood`
					FROM %project% p
						LEFT JOIN %topic% t USING(tpid)
						LEFT JOIN %project_tr% tr ON tr.`tpid`=p.`tpid` AND tr.`formid`="kamsaiindi"
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` '
					.($where?'WHERE '.implode(' AND ',$where['cond']):'')
					.' GROUP BY `tpid`'
					.' ORDER BY `totalPoint` ASC ';

	$dbs=mydb::select($stmt,$where['value']);

	$guideList['std1']=array(
											'title'=>'มาตรฐานที่ 1 : การบริหารจัดการสู่การเป็นโรงเรียนต้นแบบเด็กไทยแก้มใส'
											);
	$guideList['std2']=array(
											'title'=>'มาตรฐานที่ 2 : กระบวนการดำนเนินงานพัฒนาโรงเรียนต้นแบบเด็กไทยแก้มใส',
											'items'=>array(
																'std2o1'=>'เกษตรในโรงเรียน',
																'std2o2'=>'สหกรณ์นักเรียน',
																'std2o3'=>'การจัดบริการอาหารนักเรียน',
																'std2o4'=>'การเฝ้าระวังและติดตามภาวะโภชนาการและสมรรถภาพทางกายนักเรียน',
																'std2o5'=>'การพัฒนาสุขนิสัยและการส่งเสริมสุขภาพนักเรียน',
																'std2o6'=>'การพัฒนาอนามัยสิ่งแวดล้อมในโรงเรียน',
																'std2o7'=>'การจัดบริการสุขภาพนักเรียน',
																'std2o8'=>'การจัดการเรียนรู้เกษตร อาหาร โภชนาการและสุขภาพ',
															)
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
	$tables->id='project-list';
	$tables->thead=array('no'=>'','ข้อตกลงเลขที่','รหัสโครงการ','ปี','จังหวัด','ชื่อโครงการ','สถานะโครงการ');
	$icons['กำลังดำเนินโครงการ']='https://softganz.com/library/img/geo/circle-green.png';
	$icons['ดำเนินการเสร็จสิ้น']='https://softganz.com/library/img/geo/circle-gray.png';
	$icons['ยุติโครงการ']='https://softganz.com/library/img/geo/circle-red.png';
	$icons['ระงับโครงการ']='https://softganz.com/library/img/geo/circle-yellow.png';
	foreach ($dbs->items as $rs) {
		if ($rs->lat && $rs->lng) {
			if ($rs->totalPoint==0) {
				$icon='https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|EEEEEE|FFFFFF';
				$icon='https://softganz.com/library/img/geo/circle-gray.png';
			} else {
				$icon='https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld='.number_format($rs->totalPoint).'|b03cd3|FFFFFF';
			}


			$gis['markers'][]=array('latitude'=>$rs->lat,
														'longitude'=>$rs->lng,
														'title'=>$rs->title,
														'icon'=>$icon,
														'content'=>'<h4>'.$rs->title.'</h4><p>คุณค่าที่เกิดขึ้น : '.$parts[$rs->part].'<br />สถานภาพ : '.$rs->project_status.'<br /><a href="'.url('project/'.$rs->tpid).'">รายละเอียดโครงการ</a> | <a href="'.url('paper/'.$rs->tpid.'/situation/valuation').'">ประเมินศูนย์เรียนรู้</a></p>'
														);
		}
		$tables->rows[]=array(++$no,
												$rs->agrno,
												$rs->prid,
												$rs->pryear?sg_date($rs->pryear,'ปปปป'):'',
												$rs->provname,
												'<a href="'.url('paper/'.$rs->tpid.'/situation/valuation').'">'.$rs->title.'</a>',$rs->project_status,
												'config'=>array('class'=>'project-status-'.$rs->project_statuscode));
	}

	$ret.='<ul class="ui-menu">'._NL;
	foreach ($guideList as $k=>$v) {
		$ret.='<li><a href="'.url('project/report/goodcenter','g='.$k).'"><span class="" style="width:20px;height:20px;display:inline-block;background:#FD675B;border-radius:20px;border:2px #fff solid; text-align:center; line-height:20px;">'.substr($k,-1).'</span> '.$v['title'].'</a>';
		if ($v['items']) {
			$ret.='<ul>';
			foreach ($v['items'] as $k2 => $v2) {
				$ret.='<li><a href="'.url('project/report/goodcenter',array('g'=>$k,'s'=>$k2)).'">'.$v2.'</a></li>';
			}
			$ret.='</ul>';
		}
		$ret.='</li>'._NL;
	}
	$ret.='</ul>'._NL;

	$ret.='<div class="app-output">กำลังโหลดแผนที่!!!!</div>'._NL;

	$ret.='</div><!-- mapping -->';

	//$ret.=print_o($dbs,'$dbs');
	$ret.=$tables->build();

	$ret.='<style type="text/css">
	.mapping {position:relative;}
	.mapping .ui-menu {width: 200px; padding:8px;position: absolute; z-index:1; top:8px; right:8px; border-radius:2px; background-color:#fff; opacity:0.9;}
	</style>';

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