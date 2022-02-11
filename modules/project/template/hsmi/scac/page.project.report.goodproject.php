<?php
/**
* Report name
* @param Object $self
* @return String
*/
function project_report_goodproject($self) {
	$group=post('g');

	$pinColor=array(
							'all'=>'FFFFFF',
							'inno'=>'00FF00',
							'behavior'=>'FF6600',
							'environment'=>'FFED3D',
							'publicpolicy'=>'1E74FF',
							'social'=>'FFA5A5',
							'spirite'=>'8CFFE9',
							'FFC79A',
							'BA49FF',
							'BFCFFF'
							);

	$parts=array(
						'inno'=>'เกิดความรู้ด้านการสร้างเสริมสุขภาพและนวัตกรรมเชิงระบบสุขภาพ',
						'behavior'=>'เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
						'environment'=>'การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ (กายภาพ สังคม และเศรษฐกิจ)',
						'publicpolicy'=>'การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
						'social'=>'กระบวนการเคลื่อนไหวทางสังคมและกระบวนการในพื้นที่',
						'spirite'=>'มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ'
					);

	R::View('project.toolbar',$self,'การประเมินคุณค่า'.($group?' : '.$parts[$group]:''));

	mydb::where('tr.`formid`="valuation" AND tr.`rate1`=1');
	if ($group) mydb::where('tr.`part` LIKE :part',':part',$group.'%');

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
					ORDER BY p.`pryear` DESC, `provname` ASC, t.`title` ASC;
					';
	$dbs=mydb::select($stmt,$where['value']);

	$iconPart='https://maps.gstatic.com/mapfiles/ridefinder-images/mm_20_';

	$gis['center']='9.004486109074745,100.1000';
	$gis['zoom']=7;


	$tables = new Table();
	$tables->addClass('project-list');
	$tables->thead=array('no'=>'','ข้อตกลงเลขที่','รหัสโครงการ','ปี','จังหวัด','ชื่อโครงการ','สถานะโครงการ');
	foreach ($dbs->items as $rs) {
		if ($rs->lat && $rs->lng) {
			//$icon='https://maps.google.com/mapfiles/kml/paddle/'.substr($rs->part,-1).'.png';
			$icon='https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld='.($goodGroup?'*':$rs->totalGood).'|'.($pinColor[$group?$group:'all']).'|000000';
			//$icon=$iconPart.($icons[substr($_REQUEST['g'],2,1)+0]).'.png';
			$gis['markers'][]=array('latitude'=>$rs->lat,
														'longitude'=>$rs->lng,
														'title'=>$rs->title,
														'icon'=>$icon,
														'content'=>'<h4>'.$rs->title.'</h4><p>คุณค่าที่เกิดขึ้น : '.$parts[$rs->part].'<br />สถานภาพ : '.$rs->project_status.'<br /><a href="'.url('paper/'.$rs->tpid).'">รายละเอียดโครงการ</a> | <a href="'.url('paper/'.$rs->tpid.'/member/trainer/post/valuation').'">คุณค่าของโครงการ</a></p>'
														);
		}
		$tables->rows[]=array(++$no,
												$rs->agrno,
												$rs->prid,
												$rs->pryear?sg_date($rs->pryear,'ปปปป'):'',
												$rs->provname,
												'<a href="'.url('paper/'.$rs->tpid.'/member/trainer/post/valuation').'">'.$rs->title.'</a>',$rs->project_status,
												'config'=>array('class'=>'project-status-'.$rs->project_statuscode));
	}

	$ret.='<div class="app-output" style="height:560px;">';
	$ret.='กำลังโหลดแผนที่!!!!';
	$ret.='</div>'._NL;

	$ret.='<ul class="project-list">'._NL;
	$ret.='<li><p><a href="'.url('project/report/goodproject').'"><a href="'.url('project/report/goodproject','g='.$k).'"><img src="https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|'.$pinColor['all'].'|000000" height="24" /> ภาพรวม</a></p></li>'._NL;
	foreach ($parts as $k=>$v) {
		$ret.='<li><p><a href="'.url('project/report/goodproject','g='.$k).'"><img src="https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|'.$pinColor[$k].'|000000" height="24" /> '.$v.'</a></p></li>'._NL;
	}
	$ret.='</ul>'._NL;
	$ret.='<br clear="all" />';
	$ret.=$tables->build();

	head('jquery.ui.map','<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');

	$ret.='<style type="text/css">
	.__main {position: relative;}
	ul.project-list>li>p {padding:8px;}
	ul.project-list>li>p>a>img {display:block;float:left;margin-right:8px;}
	</style>';

	$ret.='<script type="text/javascript"><!--
$(document).ready(function() {
var imgSize = new google.maps.Size(20, 32);
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
					icon : new google.maps.MarkerImage(marker.icon, imgSize, null, null, imgSize),
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