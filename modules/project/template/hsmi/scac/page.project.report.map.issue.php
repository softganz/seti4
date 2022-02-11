<?php
/**
* Report name
* @param Object $self
* @return String
*/
function project_report_map_issue($self) {
	$group=post('g');

	$pinColor=array(
							'all'=>'FFFFFF',
							1=>'00FF00',
							'FF6600',
							'FFED3D',
							'1E74FF',
							'FFA5A5',
							'8CFFE9',
							'FFC79A',
							'BA49FF',
							'BFCFFF',
							'00FF00',
							'FF6600',
							'FFED3D',
							'1E74FF',
							'FFA5A5',
							'8CFFE9',
							'FFC79A',
							'BA49FF',
							'BFCFFF',
							'00FF00',
							'FF6600',
							'FFED3D',
							'1E74FF',
							'FFA5A5',
							'8CFFE9',
							'FFC79A',
							'BA49FF',
							'BFCFFF',
							);

	$parts=array(
						'inno'=>'เกิดความรู้ด้านการสร้างเสริมสุขภาพและนวัตกรรมเชิงระบบสุขภาพ',
						'behavior'=>'เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
						'environment'=>'การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ (กายภาพ สังคม และเศรษฐกิจ)',
						'publicpolicy'=>'การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
						'social'=>'กระบวนการเคลื่อนไหวทางสังคมและกระบวนการในพื้นที่',
						'spirite'=>'มิติสุขภาวะปัญญา / สุขภาวะทางจิตวิญญาณ'
					);

	R::View('project.toolbar',$self,'แผนที่ประเด็นโครงการ'.($group?' : '.$parts[$group]:''));

	//mydb::where('tr.`formid`="valuation" AND tr.`rate1`=1');
	if ($group) mydb::where('b.`flddata`=:issue',':issue',$group);

	$stmt='SELECT
						p.`tpid`
					, t.`title`, p.`agrno`, p.`prid`, p.`pryear`
					, X(p.`location`) `lat`, Y(p.`location`) `lng`
					, p.`project_status`, p.`project_status`+0 `project_statuscode`
					, p.`changwat`, cop.`provname`
					, COUNT(b.`flddata`) `totalCategory`
					, GROUP_CONCAT(b.`flddata`) `issueGroup`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %bigdata% b ON b.`keyid`=p.`tpid` AND b.`keyname`="project.category"
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
					%WHERE%
					GROUP BY `tpid`
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
			$icon='https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld='.($goodGroup?'*':$rs->totalCategory?$rs->totalCategory:'').'|'.($pinColor[$group?$group:'all']).'|000000';
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

	$projectCategory=model::get_category('project:category','catid',ture);
	foreach ($projectCategory as $key => $item) {
		if (!$item->process) continue;
		$options.='<option value="'.$item->catid.'" '.($item->catid==$group?'selected="selected"':'').'>'.$item->name.'</option>';
	}

	$ret.='<div class="map-report">'._NL;
	$ret.='<div class="app-output" style="height:560px;">';
	$ret.='กำลังโหลดแผนที่!!!!';
	$ret.='</div>'._NL;

	$ret.='<div class="map-bar">'._NL;
	$ret.='<form>';
	$ret.='<div class="form-item"><select class="form-select -fill" name="y"><option value="">ทุกปี</option></select></div>'._NL;
	$ret.='<div class="form-item"><select class="form-select -fill" name="g" style="width:200px;" onChange="this.form.submit();"><option value="">ทุกประเด็น</option>'.$options.'</select></div>'._NL;
	$ret.='</form>';
	$ret.='<ul class="project-list">'._NL;
	$ret.='<li><p><a href="'.url('project/report/map/issue').'"><img src="https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|'.$pinColor['all'].'|000000" height="24" /> ภาพรวม</a></p></li>'._NL;
	foreach ($projectCategory as $key => $item) {
		$ret.='<li><p><a href="'.url('project/report/map/issue','g='.$item->catid).'"><img src="https://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=|'.$pinColor[$key].'|000000" height="24" /> '.$item->name.'</a></p></li>'._NL;
	}
	$ret.='</ul>'._NL;
	$ret.='</div><!-- map-bar -->'._NL;
	$ret.='</div><!-- map-report -->'._NL;

	//$ret.=print_o($dbs,'$dbs');

	$ret.=$tables->build();

	head('jquery.ui.map','<script type="text/javascript" src="/js/jquery.ui.map.js"></script>');
	head('googlemap','<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?language=th&key='.cfg('gmapkey').'"></script>');

	$ret.='<style type="text/css">
	.__main {position: relative;}
	.map-report {position: relative;}
	.map-report .map-bar {position:absolute;top:48px;left:8px; background:#fff; opacity:0.8;}
	.map-report .map-bar form {padding:4px;}
	ul.project-list {height:300px;overflow:scroll;margin:0;padding:0;position:relative;top:auto;left:auto;right:auto;}
	ul.project-list>li>p {height:24px;padding:4px 8px;overflow:hidden;}
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