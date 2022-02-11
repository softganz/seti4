<?php
/**
* Report name
* @param Object $self
* @return String
*/
function org_map($self) {

	$year = post('y');
	$group = post('i');
	$changwat = post('p');


	R::View('org.toolbar',$self,'แผนที่องค์กร', 'none');

	/*
	$parts=array(	'5.1'=>'เกิดความรู้ หรือ นวัตกรรมชุมชน',
								'5.2'=>'เกิดการปรับเปลี่ยนพฤติกรรมที่เอื้อต่อสุขภาพ',
								'5.3'=>'การสร้างสภาพแวดล้อมที่เอื้อต่อสุขภาพ',
								'5.4'=>'การพัฒนานโยบายสาธารณะที่เอื้อต่อสุขภาวะ',
								'5.5'=>'เกิดกระบวนการชุมชน',
								'5.6'=>'มิติสุขภาวะปัญญา/สุขภาวะทางจิตวิญญาณ'
							);

	$ui=new ui();
	$ui->add('<a href="'.url('project/report').'">วิเคราะห์ภาพรวม</a>');
	$ui->add('<a href="'.url('project/report/goodproject').'">การประเมินคุณค่า (แผนที่)</a>');
	if ($group) $ui->add('<a href="'.url('project/report/goodproject',array('g'=>$group)).'">'.$parts[$group].'</a>');
	$ret.='<nav class="nav -page">'.$ui->build().'</nav>';


	$form=new Form('report',url(q()),'project-report','form -inlineitem');
	$form->addConfig('method', 'GET');

	$form->year->type='select';
	$form->year->name='y';
	$form->year->options[NULL]='--- ทุกปี ---';
	foreach (mydb::select('SELECT DISTINCT `pryear` FROM %project% ORDER BY `pryear` ASC')->items as $item) {
		$form->year->options[$item->pryear]='พ.ศ. '.($item->pryear+543);
	}
	$form->year->value=$year;

	$form->province->type='select';
	$form->province->name='p';
	$form->province->options[NULL]='--- ทุกจังหวัด ---';
	$dbs=mydb::select('SELECT DISTINCT `changwat`, `provname` FROM %project% p LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` WHERE `provname`!="" ORDER BY CONVERT(`provname` USING tis620) ASC');
	foreach ($dbs->items as $rs) {
		$form->province->options[$rs->changwat]=$rs->provname;
	}
	$form->province->value=$changwat;

	$form->inno->type='select';
	$form->inno->name='i';
	$form->inno->options[NULL]='--- ทุกนวัตกรรม ---';
	foreach ($parts as $key=>$item) {
		$form->inno->options[$key]=$item;
	}
	$form->inno->value=$group;

	$form->addField('go','<button class="btn -primary"><span>ดูรายงาน</span></button>');
	//$form->addField('go',array('type'=>'button','name'=>NULL,'value'=>'<span>ดูรายงาน</span>'));

	$ret.='<nav class="nav -page">'.$form->build().'</nav>';

	*/


	$stmt = 'SELECT
						o.`orgid`, o.`name`
					, o.`location`
					FROM %db_org% o
					%WHERE%
					';
	$dbs = mydb::select($stmt,$where['value']);
	//$ret .= print_o($dbs,'$dbs');





	$iconPart='https://maps.gstatic.com/mapfiles/ridefinder-images/mm_20_';
	$icons=array('green','purple','yellow','blue','red','orange','brown');
	if ($_REQUEST['g']) $self->theme->title.=' : '.$parts[$_REQUEST['g']];

	$gis['center']='13.604486109074745,100.1000';
	$gis['center']='7.604486109074745,100.1000';
	$gis['zoom']=8;


	$tables = new Table();
	$tables->thead=array('no'=>'','องค์กร');
	$icons['กำลังดำเนินโครงการ']='https://softganz.com/library/img/geo/circle-green.png';
	$icons['ดำเนินการเสร็จสิ้น']='https://softganz.com/library/img/geo/circle-gray.png';
	$icons['ยุติโครงการ']='https://softganz.com/library/img/geo/circle-red.png';
	$icons['ระงับโครงการ']='https://softganz.com/library/img/geo/circle-yellow.png';
	foreach ($dbs->items as $rs) {
		if ($rs->location) {
			list($lat,$lng) = explode(',',$rs->location);
			$lat = floatval($lat);
			$lng = floatval($lng);
			$icon='https://maps.google.com/mapfiles/kml/paddle/'.substr($rs->part,-1).'.png';
			$icon=$iconPart.($icons[substr($_REQUEST['g'],2,1)+0]).'.png';
			$gis['markers'][]=array(
				'latitude'=>$lat,
				'longitude'=>$lng,
				'title'=>$rs->title,
				//'icon'=>$icon,
				'content'=>'<h4>'.$rs->name.'</h4><p></p>'
				);
		}
		$tables->rows[]=array(
			++$no,
			'<a class="sg-action" href="'.url('org/'.$rs->orgid).'" data-rel="box" data-width="640">'.$rs->name.'</a>',
		);
	}

	$ret.='<div class="app-output" style="width:100%; height: 800px;">';
	$ret.='กำลังโหลดแผนที่!!!!';
	$ret.='</div>'._NL;

	$ret.='<ul class="project-list">'._NL;
	foreach ($parts as $k=>$v) {
		$ret.='<li><p><a href="'.url('project/report/goodproject','g='.$k).'"><span style="width:20px;height:20px;display:inline-block;background:#FD675B;border-radius:20px;border:2px #fff solid;">'.substr($k,-1).'</span><br />'.$v.'</a></p></li>'._NL;
	}
	$ret.='</ul>'._NL;
	$ret.='<br clear="all" />';
	$ret .= $tables->build();

	//$ret .= print_o($gis,'$gis');

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

	$ret .= '<style>
	.btn-floating.-right-bottom {display: none;}
	</style>';

//		$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>