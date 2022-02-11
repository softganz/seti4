<?php
/**
* Project Follow Mapping
* @param Object $self
* @return String
*/
function project_present_planning($self) {
	$goodGroup=post('g');
	$getSector = post('s');
	$getPlan = post('p');
	$getYear = post('yy');

	//R::View('project.toolbar', $self, 'แผนที่ติดตามโครงการ', 'map');
	cfg('web.fullpage', true);

	$ui = new Ui();
	$ui->add('<a class="btn -link" href="'.url('project/present/situation').'"><i class="icon -material">trending_up</i><span>สถานการณ์</span></a>');
	$ui->add('<a class="btn -primary" href="'.url('project/present/planning').'"><i class="icon -material">dashboard</i><span>แผนงาน</span></a>');
	$ui->add('<a class="btn -link" href="'.url('project/present/follow').'"><i class="icon -material">directions_run</i><span>ติดตามประเมินผล</span></a>');
	$ui->add('<a class="btn -link" href="'.url('project/present/valuation').'"><i class="icon -material">how_to_reg</i><span>ประเมินคุณค่า</span></a>');

	$ret .= '<nav class="nav -page">'.$ui->build().'</nav>';

	$form = new Form(NULL, url('project/map/follow'), 'map-form', '-inlineitem');
	$form->addData('query',url('project/api/marker/follow'));
	$form->addData('query','https://happynetwork.org/project/api/marker/follow');
	$form->addConfig('method', 'GET');

	$sourceList = array(
		'https://dekthaikamsai.com/project/situation/weight' => 'เด็กไทยแก้มใส',
		'https://localfund.happynetwork.org/project/report/planning/summary' => 'กองทุนตำบล',
	);

	$form->addField(
		'host',
		array(
			'type' => 'select',
			'class' => '-fill',
			'options' => $sourceList,
		)
	);



	$ret .= '<div class="mapping -project-present">';

	$gis['center']='13.6044,80.0000';
	$gis['zoom']=6;



	$ret.='<div id="map" class="app-output"><iframe src="https://localfund.happynetwork.org/project/planning" style="width: 100%; height: 100%;"></iframe></div>'._NL;

	$ret.='</div><!-- mapping -->';


	$ret.='<style type="text/css">
	html, body, .page.-main {height: 100%;}
	.mapping.-project-present {height: calc( 100% - 40px);}
	.module-project .app-output {height: 100%;}
	.package-footer {display: none;}
	.nav.-page {margin: 0; padding: 2px; background-color: #65ccff;}
	.mapping {position:relative;}
	.mapping .map-nav {width: 200px; padding:8px;position: absolute; z-index:1; top:80px; left: 10px; border-radius:2px; background-color:#fff; opacity:0.9;}
	.infowindow {width: 240px;}
	.infowindow h3 {font-family: sans-serif; font-weight: bold; font-size: 1em;}
	.btn.-active {}
	.notify-main {top: 40px;}
	</style>';



	$ret .= '<script type="text/javascript">
	var host = "https://dekthaikamsai.com/"
	var queryUrl = "project/api/marker/follow"
	var projectSet
	var goodType
	var markerUrl = host+queryUrl
	var infoWindow = null
	var activeInfoWindow = null
	var mapType = "Pin Map"
	var map
	var markerCluster
	var markers
	var pinMarkers = {}

	$("#edit-host").change(function() {
		notify("กำลังโหลด...", 10000)
		$("#map>iframe").attr("src",$("#edit-host").val())
		return false
	})

	</script>';
	return $ret;
}
?>