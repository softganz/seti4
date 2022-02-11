<?php
/**
* Report name
* @param Object $self
* @return String
*/
function project_report_goodproject($self) {
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
	foreach ($dbs->items as $rs) {
		if ($rs->lat && $rs->lng) {
			$icon='https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld='.($goodGroup?'*':$rs->totalGood).'|'.($pinColor[$goodGroup?$goodGroup:0]).'|000000';

			$gis['markers'][]=array('latitude'=>$rs->lat,
														'longitude'=>$rs->lng,
														'title'=>$rs->title,
														'icon'=>$icon,
														'content'=>'<h4>'.$rs->title.'</h4><p>คุณค่าที่เกิดขึ้น : '.$parts[$rs->part].'<br />สถานภาพ : '.$rs->project_status.'<br /><a href="'.url('project/'.$rs->tpid).'" target="_blank">รายละเอียดโครงการ</a> | <a href="'.url('paper/'.$rs->tpid.'/situation/valuation').'" target="_blank">คุณค่าของโครงการ</a></p>'
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
		$ret.='<li><a href="'.url('project/report/goodproject','g='.$k).'"><img src="https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|'.$pinColor[$k].'|000000" />'.$v.'</a></li>'._NL;
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